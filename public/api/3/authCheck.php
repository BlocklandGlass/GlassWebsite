<?php
	require_once dirname(__DIR__) . '/../../private/autoload.php';
	require_once dirname(__FILE__) . "/private/ClientConnection.php";

	// Glass Live Server authentication verification

	use Glass\UserLog;
	use Glass\UserManager;
	use Glass\DigestAccessAuthentication;


	header('Content-Type: text/json; charset=ascii');

	$barred = [118256,43364,21186,209987,43861,219615,206488,34728];
	//43861 - Steee
	//206488, 34728 - https://forum.blockland.us/index.php?topic=316286.msg9773227#msg9773227

	$ip = $_REQUEST['ip'] ?? false;
	$country_code = "No IP";
	$country_name = "No IP";
	if($ip) {
		if($pos = strrpos($ip, ":")) {
			$ip = substr($ip, $pos+1);
		}
		$loc = geoip_record_by_name($ip);
		$country_code = $loc["country_code"] ?? "N/A";
		$country_name = $loc["country_name"] ?? "N/A";
	}

	if(isset($_REQUEST['ident']) && $_REQUEST['ident'] != "") {
		$client = ClientConnection::loadFromIdentifier($_REQUEST['ident']);
		$ret = new \stdClass();

		// check to see if there was a valid client connection with that identity
		if(!is_object($client)) {
			$ret->status = "fail";
			$ret->failure = "Ident does not exist!";
			error_log("Auth failed for ident " . $_REQUEST['ident'] . ": " . $ret->failure);
			die(json_encode($ret, JSON_PRETTY_PRINT));
		}


		$user = UserManager::getFromBLID($client->getBLID());

		// check DAA
		if($_REQUEST['daa'] ?? false) {
			$json     = file_get_contents('php://input');
			$object   = json_decode($json);
			$digest   = $client->getDigest();

			if($user === false) {
				$ret->status = "fail";
				$ret->failure = "Requested DAA but no user!";
				error_log("DAA Auth failed for ident " . $_REQUEST['ident'] . " (no user)");
				die(json_encode($ret, JSON_PRETTY_PRINT));
			}

			$res = $digest->validate($object, "POST", $user->getDAAHash());
			if(!$res) {
				$ret->status = "fail";
				$ret->failure = "DAA failure!";
				error_log("DAA Auth failed for blid " . $user->getBLID());
				die(json_encode($ret, JSON_PRETTY_PRINT));
			}
		}

		// set up return info
		$ret->ident = $client->getIdentifier();
		$ret->blid = $client->getBLID();

		// check to see if user is barred, need a permanent solution
		if(in_array($ret->blid, $barred)) {
			$ret->status = "barred";
			$json = json_encode($ret, JSON_PRETTY_PRINT);
			die($json);
		}

		// some special characters stuff
		$ret->username = iconv("ISO-8859-1", "UTF-8", UserLog::getCurrentUsername($ret->blid));

		// defaulting flags
		$ret->admin = false;
		$ret->mod = false;

		// location info
		$ret->geoip_country_name = $country_name;
		$ret->geoip_country_code = $country_code;

		// checking to see if user is privileged
		if($user !== false) {
			$ret->beta = false;

			if($user->inGroup("Administrator")) {
				$ret->admin = true;
				$ret->mod = true;
				$ret->beta = true;
			} else if($user->inGroup("Moderator")) {
				$ret->mod = true;
				$ret->beta = true;
			}

			if($user->inGroup("Beta") || $user->inGroup("Reviewer")) {
				$ret->beta = true;
			}
		}

		$ret->status = "success";

		$json = json_encode($ret, JSON_PRETTY_PRINT);
		die($json);
	}
?>
