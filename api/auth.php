<?php
require_once dirname(__DIR__) . '/private/class/ApiSessionManager.php';
header('Content-Type: text/json');
if(isset($_REQUEST['sid'])) {
	$apiManager = new ApiSessionManager($_REQUEST['sid']);
} else {
	$apiManager = new ApiSessionManager("");
}

if(isset($_REQUEST['version'])) {
	$apiManager->setVersion($_REQUEST['version']);
}

$ret = new stdClass();
$ret->sid = session_id();

$request = @$_GET['request'];

if($request == "checkauth") {
	if($apiManager->attemptRemoteVerification($_GET['name'])) {
		$ret->status = "success";
		try {
			if(!$apiManager->isVerified()) {
				$ret->action = "verify";
				$ret->actiondata = new stdClass();
				$ret->actiondata->name = $apiManager->getSiteAccount()->getUsername();
				$ret->actiondata->blid = $apiManager->getBlid();
			} else {
				$ret->action = "none";
				$ret->hasGlassAccount = "1";
				//$ret->debugInfo = "verified";
			}
		} catch (Exception $e) {
			unset($ret->actiondata);
			//this means user is not registered.
			$ret->action = "none";
			$ret->debugInfo = "excep\t" . $e->getMessage();
		}

		die(json_encode($ret, JSON_PRETTY_PRINT));
	} else {
		$ret->error = "auth.blockland.us gave response \"NO\"";
		die(json_encode($ret, JSON_PRETTY_PRINT));
	}
}

if($request == "verify") {
	if($apiManager->isRemoteVerified()) {
		if($_GET['action'] == "confirm") {
			$apiManager->onVerificationSuccess();
			$ret->status = "success";
			$ret->action = "none";
			die(json_encode($ret, JSON_PRETTY_PRINT));
		} else {
			$ret->status = "error";
			$ret->action = "no action";
			die(json_encode($ret, JSON_PRETTY_PRINT));
		}
	} else {
		$ret->status = "error";
		$ret->error = "blockland auth has not succeeded";
		die(json_encode($ret, JSON_PRETTY_PRINT));
	}
}


$ret->status = "error";
$ret->error = "no action selected";

die(json_encode($ret, JSON_PRETTY_PRINT));
?>
