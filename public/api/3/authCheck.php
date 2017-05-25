<?php
require_once dirname(__DIR__) . '/../../private/autoload.php';
require_once dirname(__FILE__) . "/private/ClientConnection.php";

use Glass\UserLog;
use Glass\UserManager;

header('Content-Type: text/json; charset=ascii');

$barred = [118256,43364,21186,209987,43861];
//43861 - Steee

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
	$con = ClientConnection::loadFromIdentifier($_REQUEST['ident']);
  $ret = new \stdClass();
  if(!is_object($con)) {
    $ret->status = "fail";
		error_log("Auth failed for ident " . $_REQUEST['ident']);
  } else {
		error_log("Auth pass for " . $_REQUEST['ident']);
    $ret->ident = $con->getIdentifier();
    $ret->blid = $con->getBLID();

		if(in_array($ret->blid, $barred)) {
			$ret->status = "barred";
			$json = json_encode($ret, JSON_PRETTY_PRINT);
			die($json);
		}

		$ret->username = iconv("ISO-8859-1", "UTF-8", UserLog::getCurrentUsername($ret->blid));
		error_log("Username is " . $ret->username);

		$ret->admin = false;
		$ret->mod = false;

		$ret->geoip_country_name = $country_name;
		$ret->geoip_country_code = $country_code;

		$user = UserManager::getFromBLID($ret->blid);
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

		if($ret->blid == 27323) {
			$ret->beta = true;
		}

    $ret->status = "success";
  }
	error_log("Auth completed");
  $json = json_encode($ret, JSON_PRETTY_PRINT);
	if($json === false) {
		error_log("...but JSON conversion failed!");
	} else {
		die($json);
	}
}
?>
