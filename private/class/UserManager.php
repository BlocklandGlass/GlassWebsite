<?php
namespace Glass;

//require_once(realpath(dirname(__FILE__) . '/UserHandler.php'));
require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));
require_once(realpath(dirname(__FILE__) . '/UserObject.php'));
require_once(realpath(dirname(__FILE__) . '/DigestAccessAuthentication.php'));

class UserManager {
	private static $cacheTime = 600;
	private static $credentialsCacheTime = 60;

	private static $verified_db = false;

	public static function getFromID($id) {
		return UserManager::getFromBLID($id);
	}

	public static function getFromBLID($blid) {

		$database = new DatabaseManager();
		UserManager::verifyTable($database);
		$resource = $database->query("SELECT username, blid, banned, admin, verified, email, reset, daaHash FROM `users` WHERE `blid` = '" . $database->sanitize($blid) . "' AND `verified` = 1");

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}

		if($resource->num_rows == 0) {
			$userObject = false;
		} else {
			$userObject = new UserObject($resource->fetch_object());
		}
		$resource->close();

		return $userObject;
	}

	public static function getFromUsername($username) {

		$database = new DatabaseManager();
		UserManager::verifyTable($database);
		$resource = $database->query("SELECT username, blid, banned, admin, verified, email, reset, daaHash FROM `users` WHERE `username` = '" . $database->sanitize($username) . "' AND `verified` = 1");

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}

		if($resource->num_rows == 0) {
			$userObject = false;
		} else {
			$userObject = new UserObject($resource->fetch_object());
		}
		$resource->close();

		return $userObject;
	}

	//includes accounts that have not been activated
	public static function getAllAccountsFromBLID($blid) {

		$database = new DatabaseManager();
		UserManager::verifyTable($database);
		$resource = $database->query("SELECT username, blid, banned, admin, verified, email, daaHash FROM `users` WHERE `blid` = '" . $database->sanitize($blid) . "'");

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}
		$userObject = [];

		while($row = $resource->fetch_object()) {
			$userObject[] = new UserObject($row);
		}
		$resource->close();

		return $userObject;
	}

	public static function getCurrent() {
		if(!isset($_SESSION)) {
			session_start();
		}

		if(isset($_SESSION['blid'])) {
			return UserManager::getFromBLID($_SESSION['blid']);
		} else {
			return false;
		}
	}

	public static function login($identifier, $password, $redirect = "/index.php") {
		if(is_numeric($identifier)) {
			$blid = intval($identifier);

			if(is_int($blid)) {
				$loginDetails = UserManager::getLoginDetailsFromBLID($blid);

				if(!$loginDetails) {
					return [
						"message" => "This BL_ID has not been verified yet, please use your E-mail instead."
					];
				}
			} else {
				return [
					"message" => "Invalid BL_ID."
				];
			}
		} elseif(filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
			$email = $identifier;
			$loginDetails = UserManager::getLoginDetailsFromEmail($email);
		} else {
			return [
				"message" => "Invalid E-mail/BL_ID."
			];
		}

		if(!$loginDetails) {
			//username not found
			return [
				"message" => "Incorrect login credentials."
			];
		}
		$hash = $loginDetails['hash'];
		$salt = $loginDetails['salt'];

		if($hash === hash("sha256", $password . $salt)) {
			$_SESSION['loggedin'] = 1;

			$_SESSION['blid']			= $loginDetails['blid'];
			$_SESSION['email']		= $loginDetails['email'];
			$_SESSION['username'] = $loginDetails['username'];

			UserManager::generateHashDAA($_SESSION['blid'], $password);

			$userObject = UserManager::getFromBLID($_SESSION['blid']);
			if($userObject == false || $userObject->isMigrated()) {
				return [
					"redirect" => $redirect
				];
			} else {
				return [
					"redirect" => "/user/migrate.php"
				];
			}
		}
		return [
			"message" => "Incorrect login credentials."
		];
	}

	private static function setSessionLoggedIn($loginDetails) {
		if(!isset($_SESSION)) {
			session_start();
		}

		$_SESSION['loggedin'] = 1;

		$_SESSION['blid']			= $loginDetails['blid'];
		$_SESSION['email']		= $loginDetails['email'];
		$_SESSION['username'] = $loginDetails['username'];
	}

	public static function setSessionLoggedInBlid($blid) {
		$loginDetails = UserManager::getLoginDetailsFromBLID($blid);
		UserManager::setSessionLoggedIn($loginDetails);
	}

	public static function updatePassword($blid, $password) {
		$database = new DatabaseManager();
		UserManager::verifyTable($database);
		$intermediateSalt = md5(uniqid(rand(), true));
		$salt = substr($intermediateSalt, 0, 6);
		$hash = hash("sha256", $password . $salt);

		$database->query("UPDATE `users` SET `reset`='', `password`='" . $database->sanitize($hash) . "', `salt`='" . $database->sanitize($salt) . "' WHERE `blid`='" . $database->sanitize($blid) . "'");
		UserManager::generateHashDAA($blid, $password);
	}

	public static function generateHashDAA($blid, $password) {
		$database = new DatabaseManager();
		UserManager::verifyTable($database);

		$hash = DigestAccessAuthentication::hashPassword('sha1', 'api.blocklandglass.com', $blid, $password);

		$blid = $database->sanitize($blid);
		$hash = $database->sanitize($hash);

		$database->query("UPDATE `users` SET `daaHash`='$hash' WHERE `blid`='$blid'");
	}

	public static function register($email, $password1, $password2, $blid) {
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return [
				"message" => "Invalid e-mail address."
			];
		}

		if($password1 !== $password2) {
			return [
				"message" => "Your passwords do not match."
			];
		}

		if(strlen($password1) < 4) {
			return [
				"message" => "Your password must be at least 4 characters."
			];
		}
		$blid = trim($blid);

		if(!is_numeric($blid)) {
			return [
				"message" => "Invalid BL_ID."
			];
		}
		$loginDetails1 = UserManager::getLoginDetailsFromBLID($blid);
		$loginDetails2 = UserManager::getLoginDetailsFromEmail($email);

		if($loginDetails1) {
			return [
				"message" => "That BL_ID is already in use! Contact administration if you believe this is a mistake."
			];
		} else if($loginDetails2) {
			return [
				"message" => "E-mail address already in use."
			];
		}
		$database = new DatabaseManager();
		UserManager::verifyTable($database);
		$intermediateSalt = md5(uniqid(rand(), true));
		$salt = substr($intermediateSalt, 0, 6);
		$hash = hash("sha256", $password1 . $salt);

		//long if statement because oh well
		//I am assuming 'groups' is a json array, so by default it is "[]"
		if($database->query("INSERT INTO users (password, salt, blid, email, username) VALUES ('" .
			$database->sanitize($hash) . "', '" .
			$database->sanitize($salt) . "', '" .
			$database->sanitize($blid) . "', '" .
			$database->sanitize($email) . "', '" .
			$database->sanitize("Blockhead" . $blid) . "')")) {

			return [
				"redirect" => "/login.php"
			];
		} else {
			throw new \Exception("Error adding new user into database: " . $database->error());
		}
	}

	private static function getLoginDetailsFromEmail($email) {

		$database = new DatabaseManager();
		UserManager::verifyTable($database);
		$query = "SELECT password, salt, blid, username, email, verified FROM users WHERE `email` = '" . $database->sanitize($email) . "'";
		$loginDetails = UserManager::buildLoginDetailsFromQuery($database, $query);

		return $loginDetails;
	}

	private static function getLoginDetailsFromBLID($blid) {

		$database = new DatabaseManager();
		UserManager::verifyTable($database);
		$query = "SELECT password, salt, blid, username, email, verified FROM users WHERE `blid` = '" . $database->sanitize($blid) . "' AND  `verified` = 1";
		$loginDetails = UserManager::buildLoginDetailsFromQuery($database, $query);

		return $loginDetails;
	}

	public static function getCookieIdentifier($blid) {
		$user_data = UserManager::getLoginDetailsFromBLID($blid);
		if($user_data) {
			return CookieManager::generateIdentifier($blid, $user_data['salt']);
		} else {
			return false;
		}
	}

	private static function buildLoginDetailsFromQuery($database, $query) {
		UserManager::verifyTable($database);
		$resource = $database->query($query);

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}

		if($resource->num_rows === 0) {
			$loginDetails = false;
		} else {
			$resultObj = $resource->fetch_object();
			$loginDetails = [
				"hash" => $resultObj->password,
				"salt" => $resultObj->salt,
				"blid" => $resultObj->blid, //no need to come up with two numerical identifiers
				"username" => $resultObj->username, //we might need to change this to pull from the user-log (from in-game auth); alternatively, have the user-log update the username var
				"email" => $resultObj->email,
				"verified" => $resultObj->verified
			];
		}
		$resource->close();
		return $loginDetails;
	}

	public static function validUsername($username) {
		//usernames need to be between 1 and 20 characters (inclusive) and cannot contain newlines
		return preg_match("/.{1,20}/", $username);
	}

	public static function invalidateResetKey($blid) {
		$db = new DatabaseManager();
		UserManager::verifyTable($db);
		$db->query("UPDATE `users` SET `reset`='' WHERE `blid`='" . $db->sanitize($blid) . "'");
	}

	public static function sendPasswordResetEmail($user) {
		$resetToken = substr(base64_encode(md5(mt_rand())), 0, 20);
		$db = new DatabaseManager();
		UserManager::verifyTable($db);
		$db->query("UPDATE `users` SET `reset`='" . $db->sanitize($resetToken . " " . time()) . "' WHERE `blid`='" . $db->sanitize($user->getBlid()) . "'");

		$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html>
			  <head>
			    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			    <title>Blockland Glass Password Reset</title>
			    <meta name="description" content="" />
			    <meta name="keywords" content="" />
			    <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1" />
			    <style type="text/css">
				    h1 {
				      margin-top: 0;
				    }

				    td {
				      width: 600px;
				      padding: 20px;
				      font-family: Verdana;
				      border-radius: 15px;
				      border: 1px solid #aaa;
				      background-color: #ccc;
				    }

				    td[class=footer] {
				      background-color: #333;
				      color: #fff;
				      font-weight: bold;
				      padding:10px;
				    }

				    table {
				      width: 600px;
				      margin: 15px auto;
				    }

						.tile {
						  margin: 5px;
						  padding: 15px;
						  font-size: 0.8em;
						  background-color: #ebebeb;
						  border-radius: 2px;
						  box-shadow: 0 2px 2px 0 rgba(0,0,0,.14),0 3px 1px -2px rgba(0,0,0,.2),0 1px 5px 0 rgba(0,0,0,.1);
						}
			    </style>
			  </head>
			  <body>
			    <table class="content">
			      <tr>
			        <td class="tile">
			          <h1>Blockland Glass</h1>
								<p>
				          You seem to have forgotten your password! Please click <a href="https://blocklandglass.com/user/resetPassword.php?token=' . urlencode($resetToken) . '&id=' . $user->getBLID() . '">here to reset your password</a>.<br /><br />
									If you were not the one who requested a password reset, you may disregard this message.
								</p>
			        </td>
			      </tr>
			      <tr>
			        <td class="tile" style="background-color:#333;color:#fff;font-weight: bold;padding:10px;font-size:0.6em; text-align:center;border: 1px solid black;">Email sent ' . date('H:i:s M-d-y') . '</td>
			      </tr>
			    </table>
			  </body>
			</html>';
		UserManager::email($user, "Password Reset", $body);
	}

	public static function email($user, $subject, $message, $reply = "noreply") {
		if($user->getEmail() != null) {
			$headers = 'From: Blockland Glass <' . $reply . '@blocklandglass.com>' . "\r\n" .
    	'Reply-To: ' . $reply . '@blocklandglass.com' . "\r\n" .
    	'X-Mailer: PHP/' . phpversion() . "\r\n" .
			"MIME-Version: 1.0" . "\r\n" .
			"Content-Type: text/html; charset=UTF-8" . "\r\n";

			mail($user->getEmail(), $subject, $message, $headers, '-fnoreply@blocklandglass.com');
		} else {
			throw new \Exception("No E-Mail Address");
		}
	}

	//session last active should be moved to a new user_stats table
	//I want to move 'volatile' data out of the *Manager classes and into the StatManager class
	public static function verifyTable($database) {
		if(UserManager::$verified_db)
			return;

		UserManager::$verified_db = true;

		if(!$database->query("CREATE TABLE IF NOT EXISTS `users` (
			`username` VARCHAR(20) NOT NULL,
			`blid` INT NOT NULL DEFAULT '-1',
			`password` VARCHAR(64) NOT NULL,
			`email` VARCHAR(64),
			`salt` VARCHAR(10) NOT NULL,
			`registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`session_last_active` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`verified` TINYINT NOT NULL DEFAULT 0,
			`banned` TINYINT NOT NULL DEFAULT 0,
			`admin` TINYINT NOT NULL DEFAULT 0,
			`reset` TEXT,
			`profile` TEXT,
			`daaHash` TEXT,
			KEY (`blid`),
			UNIQUE KEY (`email`))")) {
			throw new \Exception("Error creating users table: " . $database->error());
		}
	}
}
?>
