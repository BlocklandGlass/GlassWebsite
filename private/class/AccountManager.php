<?php
require_once(realpath(dirname(__FILE__) . "/DatabaseManager.php"));

class AccountManager {
	//only cache for 60 seconds
	//in this case the only cache hits will be for people who mistype their password
	private static $cacheTime = 60;

	public static function login($identifier, $password, $redirect = "") {
		if(is_numeric($identifier)) {
			$blid = intval($identifier);
			if(is_int($blid)) {
				$loginDetails = AccountManager::getLoginDetailsFromBLID($blid);
			} else {
				return "Invalid e-mail/bl_id";
			}
		} else if(filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
			$email = $identifier;
			$loginDetails = AccountManager::getLoginDetailsFromEmail($email);
		} else {
			return "Invalid e-mail/bl_id";
		}
		//$safe_username = filterUsername($username);
        //
		//if($username !== $safe_username) {
		//	return "Invalid username/password provided.  You may only use letters, numbers, spaces, periods, underscores, forward slashes, and dashes.";
		//}
		$hash = $loginDetails['hash'];
		$salt = $loginDetails['salt'];

		if(!$loginDetails) {
			//username not found
			return "Incorrect username and/or password";
		}

		if($hash == hash("sha256", $password . $salt)) {
			$_SESSION['loggedin'] = 1;
			$_SESSION['blid'] = $loginDetails['blid'];
			$_SESSION['username'] = $loginDetails['username'];

			if($redirect !== "") {
				header("Location: " . $redirect);
			} else {
				header("Location: /index.php");
			}
			$resource->close();
			//no need to cache on a successful login
			die();
		}

		//if(isset($flag)) {
		//	apc_store('loginAttempt_' . $username, $loginDetails, AccountManager::$cacheTime);
		//}
		//password does not match username
		return "Incorrect username and/or password";
	}

	public static function register($email, $password1, $password2, $blid) {
		/*if(!validUsername($username)) {
			return "Invalid username provided.  You must use 3-20 characters and may only use letters, numbers, spaces, periods, underscores, forward slashes, and dashes.";
		}*/

		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return "Invalid e-mail address";
		}

		if($password1 !== $password2) {
			return "Your passwords do not match.";
		}

		if(strlen($password1) < 4) {
			return "Your password must be at least 4 characters";
		}

		if(!is_numeric($blid)) {
			return "INVALID BL_ID";
		}
		$loginDetails1 = AccountManager::getLoginDetailsFromBLID($blid);
		$loginDetails2 = AccountManager::getLoginDetailsFromEmail($email);

		if($loginDetails1) {
			return "That BL_ID is already in use!";
		} else if($loginDetails2) {
			return "That e-mail address is already in use.";
		}

		$database = new DatabaseManager();
		//AccountManager::verifyTable($database);
		$intermediateSalt = md5(uniqid(rand(), true));
		$salt = substr($intermediateSalt, 0, 6);
		$hash = hash("sha256", $password1 . $salt);

		if($database->query("INSERT INTO users (password, salt, blid, email) VALUES ('" . $database->sanitize($hash) . "', '" . $database->sanitize($salt) . "', '" . $database->sanitize($blid) . "', '" . $database->sanitize($email) . "')"))
		{
			$_SESSION['justregistered'] = 1;

			if(isset($redirect)) {
				header("Location: " . $redirect);
			} else {
				header("Location: /index.php");
			}
			die();
		} else {
			throw new Exception("Error adding new user into databse: " . $database->error());
		}
	}



	//	$safe_username = filterUsername($username);
    //
	//	if($username !== $safe_username || strlen($safe_username) < 3 || strlen($safe_username) > 20) {
	//		return "Invalid username provided.  You must use 3-20 characters and may only use letters, numbers, spaces, periods, underscores, forward slashes, and dashes.";
	//	}	else if($password !== $password_check) {
	//		$status_message = "Your passwords do not match.";
	//	} else if(strlen($password) < 4) {
	//		$status_message = "Your password must be at least 4 characters";
	//	} else if(!is_numeric($blid)) {
	//		$status_message = "Invalid BL_ID";
	//	} else {
	//		require_once(realpath(dirname(__FILE__) . "/private/class/DatabaseManager.php"));
	//		$database = new DatabaseManager();
    //
	//		//if($database
	//		//	$status_message = "That username is already taken.  Please try another.";
	//		//can never be too safe
	//		$resource = $database->query("SELECT * FROM `users` WHERE `username` = '" . $database->sanitize($safe_username) . "' OR `blid`='" . $database->sanitize($blid) . "'");
    //
	//		if(!$resource) {
	//			$status_message = "An internal database error occurred: " . $database->error();
	//		} else if($resource->num_rows === 0) {
	//			$intermediateSalt = md5(uniqid(rand(), true));
	//			$salt = substr($intermediateSalt, 0, 6);
	//			$hash = hash("sha256", $password . $salt);
    //
	//			if($database->query("INSERT INTO users (username, password, salt, blid) VALUES ('" . $database->sanitize($safe_username) . "', '" . $database->sanitize($hash) . "', '" . $database->sanitize($salt) . "', '" . $database->sanitize($blid) . "')"))
	//			{
	//				$_SESSION['justregistered'] = 1;
	//				header("Location: /login.php");
	//				$resource->close();
	//				die();
	//			} else {
	//				$status_message = "Error adding new user into databse: " . $database->error();
	//			}
	//		} else {
	//			$status_message = "That username is already taken";
	//		}
	//		//improves performance with simultaneous connections
	//		$resource->close();
	//	}
	//}

	private static function getLoginDetailsFromEmail($email) {
		$loginDetails = apc_fetch('loginDetails_' . $email);

		if($loginDetails === false) {
			$database = new DatabaseManager();
			AccountManager::verifyTable($database);
			$resource = $database->query("SELECT password, salt, blid, username, email FROM users WHERE `email` = '" . $database->sanitize($email) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}

			if($resource->num_rows === 0) {
				return false;
			}

			$resultObj = $resource->fetch_object();

			$loginDetails = [
				"hash" => $resultObj->password,
				"salt" => $resultObj->salt,
				"blid" => $resultObj->blid, //no need to come up with two numerical identifiers
				"username" => $resultObj->username //we might need to change this to pull from the user-log (from in-game auth); alternatively, have the user-log update the username var
			];
			$resource->close();

			//while($row = $resource->fetch_object()) {
			//	$hash = $row->password;
			//	$salt = $row->salt;
			//}
			apc_store('loginAttempt_' . $email, $loginDetails, AccountManager::$cacheTime);
			//$loginDetails = apc_fetch('loginDetails_' . $email); //causing error?
		}
		return $loginDetails;
	}

	private static function getLoginDetailsFromBLID($blid) {
		$loginDetails = apc_fetch('loginDetails_' . $blid);

		if($loginDetails === false) {
			$database = new DatabaseManager();
			AccountManager::verifyTable($database);
			$resource = $database->query("SELECT password, salt, blid, username, email FROM users WHERE `blid` = '" . $database->sanitize($blid) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}

			if($resource->num_rows === 0) {
				return false;
			}

			$resultObj = $resource->fetch_object();

			$loginDetails = [
				"hash" => $resultObj->password,
				"salt" => $resultObj->salt,
				"blid" => $resultObj->blid, //no need to come up with two numerical identifiers
				"username" => $resultObj->username //we might need to change this to pull from the user-log (from in-game auth); alternatively, have the user-log update the username var
			];
			$resource->close();

			//while($row = $resource->fetch_object()) {
			//	$hash = $row->password;
			//	$salt = $row->salt;
			//}
			apc_store('loginAttempt_' . $blid, $loginDetails, AccountManager::$cacheTime);
			//$loginDetails = apc_fetch('loginDetails_' . $blid); - causing error?
		}
		return $loginDetails;
	}

	//private static function filterUsername($input) {
	//	//the only characters allowed are a-z, A-Z, 0-9, '.', '/', '-', '_', ' '
	//	//there are more characters allowed in filepaths, but I will add those cases as they come up
	//	return preg_replace("/[^a-zA-Z0-9\.\-\/\_\ ]/", "", $input);
	//}

	private static function validUsername($username) {
		//the allowed characters are a-z, A-Z, 0-9, '.', '/', '-', '_', ' '
		//requires between 3 and 20 characters (inclusive)
		return preg_match("/[^a-zA-Z0-9\.\-\/\_\ ]{3,20}/", $username);
	}

	private static function verifyTable($database) {
		//maybe replace verified/banned with 'status'
		if(!$database->query("CREATE TABLE IF NOT EXISTS `users` (
			username VARCHAR(20) NOT NULL,
			displayname VARCHAR(20) NOT NULL,
			blid INT NOT NULL DEFAULT '-1',
			password VARCHAR(64) NOT NULL,
			email VARCHAR(64) NOT NULL,
			salt VARCHAR(10) NOT NULL,
			session_last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			groups MEDIUMTEXT,
			verified TINYINT NOT NULL DEFAULT 0,
			banned TINYINT NOT NULL DEFAULT 0,
			admin TINYINT NOT NULL DEFAULT 0,
			PRIMARY KEY (blid))")) {
			throw new Exception("Error creating users table: " . $database->error());
		}
	}
}
?>
