<?php
require_once(realpath(dirname(__FILE__) . '/UserHandler.php'));
require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));

class UserManager {
	private static $cacheTime = 600;
	private static $credentialsCacheTime = 60;

	public static function getFromBLID($blid) {
		$userObject = apc_fetch('userObject_' . $blid);

		if($userObject === false) {
			$database = new DatabaseManager();
			UserManager::verifyTable($database);
			$resource = $database->query("SELECT username, blid, banned, admin, verified, email, groups FROM `users` WHERE `blid` = '" . $database->sanitize($blid) . "' AND `verified` = 1");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}

			if($resource->num_rows == 0) {
				$userObject = false;
			} else {
				$userObject = new UserObject($resource->fetch_object());
			}
			$resource->close();
			apc_store('userObject_' . $blid, $userObject, UserManager::$cacheTime);
		}
		return $userObject;
	}

	public static function getCurrent() {
		if(!isset($_SESSION)) {
			throw new Exception("No Session!");
		}

		if(isset($_SESSION['blid'])) {
			return getFromBLID($_SESSION['blid']);
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
						"message" => "This BL_ID has not been verified yet, please use your E-mail instead"
					];
				}
			} else {
				return [
					"message" => "Invalid BL_ID"
				];
			}
		} elseif(filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
			$email = $identifier;
			$loginDetails = UserManager::getLoginDetailsFromEmail($email);
		} else {
			return [
				"message" => "Invalid E-mail/BL_ID"
			];
		}

		if(!$loginDetails) {
			//username not found
			return [
				"message" => "Incorrect login credentials"
			];
		}
		$hash = $loginDetails['hash'];
		$salt = $loginDetails['salt'];

		if($hash === hash("sha256", $password . $salt)) {
			$_SESSION['loggedin'] = 1;
			$_SESSION['blid'] = $loginDetails['blid'];
			$_SESSION['username'] = $loginDetails['username'];

			return [
				"redirect" => $redirect
			];
		}
		return [
			"message" => "Incorrect login credentials"
		];
	}

	public static function register($email, $password1, $password2, $blid) {
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return [
				"message" => "Invalid e-mail address"
			];
		}

		if($password1 !== $password2) {
			return [
				"message" => "Your passwords do not match."
			];
		}

		if(strlen($password1) < 4) {
			return [
				"message" => "Your password must be at least 4 characters"
			];
		}
		$blid = trim($blid);

		if(!is_numeric($blid)) {
			return [
				"message" => "INVALID BL_ID"
			];
		}
		$loginDetails1 = UserManager::getLoginDetailsFromBLID($blid);
		$loginDetails2 = UserManager::getLoginDetailsFromEmail($email);

		if($loginDetails1) {
			return [
				"message" => "That BL_ID is already in use!"
			];
		} else if($loginDetails2) {
			return [
				"message" => "That E-mail address is already in use."
			];
		}
		$database = new DatabaseManager();
		$intermediateSalt = md5(uniqid(rand(), true));
		$salt = substr($intermediateSalt, 0, 6);
		$hash = hash("sha256", $password1 . $salt);

		//long if statement because oh well
		//I am assuming 'groups' is a json array, so by default it is "[]"
		if($database->query("INSERT INTO users (password, salt, blid, email, groups, username) VALUES ('" .
			$database->sanitize($hash) . "', '" .
			$database->sanitize($salt) . "', '" .
			$database->sanitize($blid) . "', '" .
			$database->sanitize($email) . "', '" .
			$database->sanitize("[]") . "', '" .
			$database->sanitize("Blockhead" . $blid) . "')")) {

			return [
				"redirect" => "/login.php"
			];
		} else {
			throw new Exception("Error adding new user into databse: " . $database->error());
		}
	}

	private static function getLoginDetailsFromEmail($email) {
		$loginDetails = apc_fetch('loginDetailsFromEmail_' . $email);

		if($loginDetails === false) {
			$database = new DatabaseManager();
			$query = "SELECT password, salt, blid, username FROM users WHERE `email` = '" . $database->sanitize($email) . "'";
			$loginDetails = UserManager::buildLoginDetailsFromQuery($database, $query);
			apc_store('loginDetailsFromEmail_' . $email, $loginDetails, UserManager::$credentialsCacheTime);
		}
		return $loginDetails;
	}

	private static function getLoginDetailsFromBLID($blid) {
		$loginDetails = apc_fetch('loginDetailsFromBLID_' . $blid);

		if($loginDetails === false) {
			$database = new DatabaseManager();
			$query = "SELECT password, salt, blid, username FROM users WHERE `blid` = '" . $database->sanitize($blid) . "' AND  `verified` = 1";
			$loginDetails = UserManager::buildLoginDetailsFromQuery($database, $query);
			apc_store('loginDetailsFromBLID_' . $blid, $loginDetails, UserManager::$credentialsCacheTime);
		}
		return $loginDetails;
	}

	private static function buildLoginDetailsFromQuery($database, $query) {
		UserManager::verifyTable($database);
		$resource = $database->query($query);

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		}

		if($resource->num_rows === 0) {
			$loginDetails = false;
		} else {
			$resultObj = $resource->fetch_object();
			$loginDetails = [
				"hash" => $resultObj->password,
				"salt" => $resultObj->salt,
				"blid" => $resultObj->blid, //no need to come up with two numerical identifiers
				"username" => $resultObj->username //we might need to change this to pull from the user-log (from in-game auth); alternatively, have the user-log update the username var
			];
		}
		$resource->close();
		return $loginDetails;
	}

	private static function validUsername($username) {
		//usernames need to be between 1 and 20 characters (inclusive) and cannot contain newlines
		return preg_match("/.{1,20}/", $username);
	}

	private static function verifyTable($database) {
		if(!$database->query("CREATE TABLE IF NOT EXISTS `users` (
			username VARCHAR(20) NOT NULL,
			blid INT NOT NULL DEFAULT '-1',
			password VARCHAR(64) NOT NULL,
			email VARCHAR(64) NOT NULL,
			salt VARCHAR(10) NOT NULL,
			session_last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			groups MEDIUMTEXT,
			verified TINYINT NOT NULL DEFAULT 0,
			banned TINYINT NOT NULL DEFAULT 0,
			admin TINYINT NOT NULL DEFAULT 0,
			KEY (blid))")) {
			throw new Exception("Error creating users table: " . $database->error());
		}
	}
}
?>
