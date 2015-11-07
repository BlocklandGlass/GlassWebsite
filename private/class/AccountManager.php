<?php
require_once(realpath(dirname(__FILE__) . "/DatabaseManager.php"));

class AccountManager {
	//only cache for 60 seconds
	//in this case the only cache hits will be for people who mistype their password
	private static $cacheTime = 60;

	public static function login($identifier, $password, $redirect = "/index.php") {
		if(is_numeric($identifier)) {
			$blid = intval($identifier);

			if(is_int($blid)) {
				$loginDetails = AccountManager::getLoginDetailsFromBLID($blid);

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
			$loginDetails = AccountManager::getLoginDetailsFromEmail($email);
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
			//header("Location: " . $redirect);
			//die();
			return [
				"redirect" => $redirect
			];
		}
		return [
			"message" => "Incorrect login credentials"
		];
	}

	public static function register($email, $password1, $password2, $blid, $redirect = "/index.php") {
		/*if(!validUsername($username)) {
			return "Invalid username provided.  You must use 3-20 characters and may only use letters, numbers, spaces, periods, underscores, forward slashes, and dashes.";
		}*/

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

		if(!is_numeric($blid)) {
			return [
				"message" => "INVALID BL_ID"
			];
		}
		$loginDetails1 = AccountManager::getLoginDetailsFromBLID($blid);
		$loginDetails2 = AccountManager::getLoginDetailsFromEmail($email);

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
		//AccountManager::verifyTable($database);
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
			//$_SESSION['justregistered'] = 1;
			//header("Location: " . $redirect);

			//I think this is the only way to do a redirect containing post information
			//echo("<!doctype html><head><meta charset=\"utf-8\"></head><body>");
			//echo("<form class=\"hidden\" action=\"/login.php\" name=\"redirectForm\" method=\"post\">");
			//echo("<input type=\"hidden\" name=\"redirect\" value=\"" . htmlspecialchars($redirect) . "\">");
			//echo("<input type=\"hidden\" name=\"justregistered\" value=\"1\">");
			//echo("<input type=\"submit\" value=\"Click here if your browser does not automatically redirect you\">");
			//echo("</form>");
			//echo("<script language=\"JavaScript\">document.redirectForm.submit();</script>");
			//echo("</body></html>");
			//die();
			return [
				"redirect" => $redirect
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
			$loginDetails = AccountManager::buildLoginDetailsFromQuery($database, $query);
			apc_store('loginDetailsFromEmail_' . $email, $loginDetails, AccountManager::$cacheTime);
			//$loginDetails = apc_fetch('loginDetails_' . $email); //causing error?
		}
		return $loginDetails;
	}

	private static function getLoginDetailsFromBLID($blid) {
		$loginDetails = apc_fetch('loginDetailsFromBLID_' . $blid);

		if($loginDetails === false) {
			$database = new DatabaseManager();
			$query = "SELECT password, salt, blid, username FROM users WHERE `blid` = '" . $database->sanitize($blid) . "' AND  `verified` = 1";
			$loginDetails = AccountManager::buildLoginDetailsFromQuery($database, $query);
			apc_store('loginDetailsFromBLID_' . $blid, $loginDetails, AccountManager::$cacheTime);
			//$loginDetails = apc_fetch('loginDetails_' . $blid); - causing error?
		}
		return $loginDetails;
	}

	private static function buildLoginDetailsFromQuery($database, $query) {
		AccountManager::verifyTable($database);
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

	//private static function filterUsername($input) {
	//	//the only characters allowed are a-z, A-Z, 0-9, '.', '/', '-', '_', ' '
	//	//there are more characters allowed in filepaths, but I will add those cases as they come up
	//	return preg_replace("/[^a-zA-Z0-9\.\-\/\_\ ]/", "", $input);
	//}

	private static function validUsername($username) {
		//usernames need to be between 1 and 20 characters (inclusive) and cannot contain newlines
		return preg_match("/.{1,20}/", $username);
	}

	private static function verifyTable($database) {
		//maybe replace verified/banned with 'status'
		//the issue with using only blid and not keeping our own identifier
		//is that people can try and register other blids and lock them out
		//the workaround is to have a separate table for unregistered users
		//I think having KEY (blid) might work however
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
