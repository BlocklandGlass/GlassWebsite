<?php

require dirname(__DIR__) . '/../../../../private/autoload.php';

require_once dirname(__DIR__) . "/../private/ClientConnection.php";
require_once dirname(__DIR__) . "/../private/BlocklandAuth.php";

use Glass\ServerTracker;
use Glass\NotificationManager;
use Glass\UserManager;
use Glass\UserLog;

$SAFE_MODE = false;


$action = $_REQUEST['action'] ?? false;
$ident  = $_GET['ident'] ?? false;
$ip     = $_SERVER['REMOTE_ADDR'];

$ret    = new stdClass();

if(!$action) {
  $ret->status = "error";
  $ret->error  = "No action specified";
  die(json_encode($ret));
}

$client = false;
if($ident) {
  $client = ClientConnection::loadFromIdentifier($ident);

  if($client === false || !$client->checkIp($ip) || $client->isServer()) {
    unauthorized();
  }

} else {
  if($action == "checkin") {
    $action = "ident";
  }
}


switch($action) {
  case "ident":
    $authType  = $_REQUEST['authType'] ?? "default"; // daa or default

    $joinToken = $_REQUEST['joinToken'] ?? false;
    $blid      = $_REQUEST['blid']      ?? false;
    $steamid   = $_REQUEST['steamid']   ?? false;
    $username  = $_REQUEST['username']   ?? false;



    // check parameters
    if($joinToken === false || $blid === false || $steamid === false || $username === false) badParameters();

    $client = new ClientConnection(array($blid, $username, $ip));


    // check if DAA is required
    $require_daa = false;
    $user = UserManager::getFromBLID($blid);
    if($user) {
      if($user->inGroup("Administrator")) {
        $require_daa = "Administrator";
      } else if($user->inGroup("Moderator")) {
        $require_daa = "Moderator";
      }
    }

    $require_daa = false; // yet again implementing this while we figure things out

    if($authType == "daa" || $require_daa !== false) {

      // send back DAA keys so they can proceed
      $client->setDigestAccessAuth(true);

      $ret->status = ($authType == "daa") ? "daa-keys" : "daa-required";
      $ret->daa    = $client->getDigestData();

      if($require_daa !== false && $authType != "daa") {
        $ret->role = $require_daa;
      }
    } else {

      if ($SAFE_MODE) {
        $ret->status = "failed";
        $ret->message = "Blockland Glass is in safe mode, general authentication is disabled.";
        break;
      }

      // start normal auth, no DAA
      $success = $client->attemptBlocklandAuth($joinToken);
      if($success) {
        // blockland authenticated
        $ret->status = "success";

        // check if user has a pending, unverified account
        if(!$client->hasGlassAccount()) {
          $userArray = $client->getUnverifiedAccounts();
          if(sizeof($userArray) > 0) {

            $ret->action = "verify";

            $verifyData = array();
            foreach($userArray as $user) {
              $verifyData[] = $user->getEmail();
            }

            $ret->verify_data = $verifyData;

            // account verification requires DAA
            // if the user opts to not verify, all pending accounts will be
            // deleted and this will no occur again
            $client->setDigestAccessAuth(true);
            $ret->daa = $client->getDigestData();
          }
        }

        $ret->ident  = $client->getIdentifier(); //could be opaque now

        $client->setAuthed(true);
      } else {
        // Blockland Auth failed
        $ret->status = "failed";
        $ret->message = "Blockland Authentication failed";
      }
    }

  break;

  case "daa-ident":

    if($client === false) badParameters();

    $json   = file_get_contents('php://input');
    $object = json_decode($json);

    $user = UserManager::getFromBLID($client->getBlid());
    if(!$user) {
      unauthorized();
    }

    $digest = $client->getDigest();

    if($user->getDAAHash() == null || $user->getDAAHash() == "") {
      daaHashMissing();
    }

    $data = $digest->validate($object, "POST", $user->getDAAHash());
    if($data !== null) {
      $name = $data->name;
      $blid = $data->blid;

      if($name == $client->getUsername() && $blid == $client->getBlid()) {
        // do blockland auth
        // $success = $client->attemptBlocklandAuth();

        $success = true; // bypass blockland auth because we authed with the account
        if($success) {

          $ret->status = "success";
          $client->setAuthed(true);

        } else {

          $ret->status = "failed";
          $ret->message = "Blockland Authentication failed!";

        }
      } else {
        // inconsistency!
        $ret->status = "failed";
        $ret->message = "Initial request and DAA are different! MITM?";
      }

    } else {
      $ret->status = "failed";
      $ret->message = "Bad DAA!";
    }

  break;

  case "checkin":
    if(!$client->isAuthed()) unauthorized();


    $ret->status = "success";
		$client->setAuthed(true);
  break;

  case "daa-checkin":
    if(!$client->isAuthed()) unauthorized();

    // TODO
    $client->setAuthed(true);
    $ret->status = "success";

  break;

  case "verify":
    // TODO this will be disabled after 4.2
    if(!$client->isAuthed()) unauthorized();

    // client does not have 4.2 if we've gotten here
    // so turn off DAA
    $client->setDigestAccessAuth(false);

    if($client->hasGlassAccount()) {
      $ret->result = "error";
      $ret->error = "already verified";
    } else {
      $email = $_REQUEST['email'] ?? "";

      $userArray = $client->getUnverifiedAccounts();

      $ret->result = "verify_failed";

      foreach($userArray as $user) {
        if(strtolower($user->getEmail()) == strtolower($email)) {
          $user->setVerified(true);
          $ret->result = "verify_success";
          break;
        }
      }
    }
  break;


  case "daa-verify-account":
    if($client === false || !$client->isAuthed()) unauthorized();

    if($client->hasGlassAccount()) {
      $ret->status = "verify-has-account";
      break;
    }

    // we need to check the user's message against every possible password

    $json     = file_get_contents('php://input');
    $object   = json_decode($json);
    $digest   = $client->getDigest();

    $accounts = $client->getUnverifiedAccounts();

    $ret->status = "verify-failed";

    $nonceCount = $digest->getNonceCount(); // hacky

    foreach($accounts as $account) {
      // this could be problematic for compatibility?
      if($account->getDAAHash() == null || $account->getDAAHash() == "")
        continue;

      $digest->restore($object->nonce, $object->opaque, $nonceCount); // we're checking multiple hashes under the same count

      $data = $digest->validate($object, "POST", $account->getDAAHash());
      if($data !== null) {
        // success! make sure the email contained is the email for the account
        if(strtolower($data->email) == strtolower($account->getEmail())) {

          $account->setVerified(true);
          $ret->status = "verify-success";
          break;

        } else {
          // right password but wrong email!
          // keep trying, another pair may be correct`
        }

      }
    }
  break;

  case "verify-reject":
    if(!$client->isAuthed()) unauthorized();

    // TODO need a way to delete the accounts!
    $ret->status = "empty";
  break;

  default:
    $ret->status = "error";
    $ret->error  = "Unknown call \"$action\"";
  break;
}

echo json_encode($ret, JSON_PRETTY_PRINT);
