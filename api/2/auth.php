<?php
require_once dirname(__FILE__) . "/private/ClientConnection.php";
require_once dirname(__FILE__) . "/private/BlocklandAuth.php";
require_once dirname(__DIR__) . "/../private/class/ServerTracker.php";
require_once dirname(__DIR__) . "/../private/class/NotificationManager.php";

//fields -
// ident    - unique numerical session identifier
// username - username
// blid     - blockland id
// version  - version of glass

if(isset($_REQUEST['server'])) {
	if($_REQUEST['server']) {
		$isServer = true;
	} else {
		$isServer = false;
	}
} else {
	$isServer = false;
}

header('Content-Type: text/json');
if(isset($_REQUEST['ident']) && $_REQUEST['ident'] != "") {
  // glass checks in every 5 (?) minutes
  // on the old site, this was used to keep the "currently active" list
  // this will get added back later

	$con = ClientConnection::loadFromIdentifier($_REQUEST['ident']);
  $ret = new stdClass();

	if(is_object($con)) {
	  $ret->ident = $con->getIdentifier();
	} else {
		$ret->action = "reauth";
	}

  if(is_object($con) && $con->isAuthed()) {
		$con->setServer($isServer);
    // action   - verify
    // email    - correct email
    if($_REQUEST['action'] == "verify") {
      if($con->hasGlassAccount()) {
        $ret->result = "error";
        $ret->error = "already verified";
      } else {
        $email = $_REQUEST['email'];

        $userArray = $con->getUnverifiedAccounts();

        foreach($userArray as $user) {
          if(strtolower($user->getEmail()) == strtolower($email)) {
            $user->setVerified(true);
            $ret->result = "success";
						//NotificationManager::sendPushNotification($user->getBlid(), "Verified", "Your email address <color:ff0000>\"" . $_REQUEST['email'] . "\"<color:000000> has been verified!", "accept_button", null, 5000);
            break;
          }
        }
      }
    } else if($_REQUEST['action'] == "checkin") {
      $ret->status = "success";
			$con->setAuthed(true);
			if($isServer) {
				$clients = stripcslashes($_REQUEST['clients']);
				$clArray = array();
				$clDatArray = explode("\n", $clients);
				foreach($clDatArray as $clDat) {
					$dat = explode("\t", $clDat);
					$obj = new stdClass();

					$obj->name = $dat[0];
					$obj->blid = $dat[1];

					$clArray[] = $obj;
				}
				$ret->cl = $clArray;

				$username = $_REQUEST['username'];
			  $blid = $_REQUEST['blid'];
			  $port = $_REQUEST['port'];
			  $ip = $_SERVER['REMOTE_ADDR'];

				ServerTracker::updateRecord($ip, $port, $username, $clArray);
			}
    }
  }
  echo json_encode($ret, JSON_PRETTY_PRINT);
} else {
  $ret = new stdClass();

  $username = $_REQUEST['username'];
  $blid = $_REQUEST['blid'];
  $ip = $_SERVER['REMOTE_ADDR'];

	$con = new ClientConnection(array($blid, $username, $ip));
	$con->setServer($isServer);

  $blAuth = $con->attemptBlocklandAuth();
  if($blAuth === true) {
    $ret->status = "success";
    $ret->ident = $con->getIdentifier();


    $con->setAuthed(true);

		if($isServer) {
			$clients = stripcslashes($_REQUEST['clients']);
			$clArray = array();
			if($clients != "") {
				$clDatArray = explode("\n", $clients);
				foreach($clDatArray as $clDat) {
					$dat = explode("\t", $clDat);
					$obj = new stdClass();

					$obj->name = $dat[0];
					$obj->blid = intval($dat[1]);
					$obj->status = $dat[2];
					$obj->version = $dat[3];

					$clArray[] = $obj;
				}
				$ret->cl = $clArray;
			}

			$username = $_REQUEST['username'];
			$blid = $_REQUEST['blid'];
			$port = $_REQUEST['port'];
			$ip = $_SERVER['REMOTE_ADDR'];

			ServerTracker::updateRecord($ip, $port, $username, $clArray);
		}


    if($con->hasGlassAccount()) {
      $ret->debug = "glass account found";
    } else {
      $userArray = $con->getUnverifiedAccounts();
      if(sizeof($userArray) > 0) {
        $ret->action = "verify";
        $verifyData = array();
        foreach($userArray as $user) {
          $verifyData[] = $user->getEmail();
        }
        $ret->verify_data = $verifyData;
      }
    }
  } else {
    $ret->status = "failed";
    $ret->msg = $blAuth[0];
    $ret->ident = $con->getIdentifier();
  }

  echo json_encode($ret, JSON_PRETTY_PRINT);
}
?>
