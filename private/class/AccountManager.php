<?php
require_once(realpath(dirname(__FILE__) . "/DatabaseManager.php"));

class AccountManager {
	//only cache for 60 seconds
	//in this case the only cache hits will be for people who mistype their password
	private static $cacheTime = 60;

	public static function login($username, $password, $redirect) {
		//$safe_username = filterUsername($username);
        //
		//if($username !== $safe_username) {
		//	return "Invalid username/password provided.  You may only use letters, numbers, spaces, periods, underscores, forward slashes, and dashes.";
		//}
		$hash = $loginDetails['password'];
		$salt = $loginDetails['salt'];
		$loginDetails = AccountManager::getLoginDetails($username);

		if(!$loginDetails) {
			//username not found
			return "Incorrect username and/or password";
		}

		if($hash == hash("sha256", $password . $salt)) {
			$_SESSION['loggedin'] = 1;
			$_SESSION['uid'] = $loginDetails['uid'];
			$_SESSION['username'] = $loginDetails['username'];

			if(isset($redirect)) {
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

	public static function register($username, $password1, $password2, $blid) {
		if(!validUsername($username)) {
			return "Invalid username provided.  You must use 3-20 characters and may only use letters, numbers, spaces, periods, underscores, forward slashes, and dashes.";
		}

		if($password1 !== $password2) {
			return "Your passwords do not match.";
		}

		if(strlen($password) < 4) {
			return "Your password must be at least 4 characters";
		}

		if(!is_numeric($blid)) {
			return "INVALID BL_ID";
		}
		$loginDetails = AccountManager::getLoginDetails($username);

		if($loginDetails) {
			return "That username is already taken.  Please try another.";
		}
		$database = new DatabaseManager();
		//AccountManager::verifyTable($database);
		$intermediateSalt = md5(uniqid(rand(), true));
		$salt = substr($intermediateSalt, 0, 6);
		$hash = hash("sha256", $password . $salt);

		if($database->query("INSERT INTO users (username, password, salt, blid) VALUES ('" . $database->sanitize($safe_username) . "', '" . $database->sanitize($hash) . "', '" . $database->sanitize($salt) . "', '" . $database->sanitize($blid) . "')"))
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

	private static function getLoginDetails($username) {
		$loginDetails = apc_fetch('loginDetails_' . $username);

		if($loginDetails === false) {
			$database = new DatabaseManager();
			AccountManager::verifyTable($database);
			$resource = $database->query("SELECT password, salt, id, username FROM users WHERE username = '" . $database->sanitize($username) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}

			if($resource->num_rows === 0) {
				return false;
			}

			$loginDetails = [
				"hash" => $resource[0]->password,
				"salt" => $resource[0]->salt,
				"uid" => $resource[0]->id,
				"username" => $resource[0]->username
			];
			$resource->close();

			//while($row = $resource->fetch_object()) {
			//	$hash = $row->password;
			//	$salt = $row->salt;
			//}
			apc_store('loginAttempt_' . $username, $loginDetails, AccountManager::$cacheTime);
			$loginDetails = apc_store('loginDetails_' . $username);
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
			id INT AUTO_INCREMENT,
			username VARCHAR(20) NOT NULL,
			displayname VARCHAR(20) NOT NULL,
			blid INT NOT NULL DEFAULT '-1',
			password VARCHAR(64) NOT NULL,
			salt VARCHAR(10) NOT NULL,
			session_last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			groups MEDIUMTEXT,
			verified TINYINT NOT NULL DEFAULT 0,
			banned TINYINT NOT NULL DEFAULT 0,
			admin TINYINT NOT NULL DEFAULT 0
			PRIMARY KEY (id))")) {
			throw new Exception("Error creating users table: " . $database->error());
		}
	}
}
?>
