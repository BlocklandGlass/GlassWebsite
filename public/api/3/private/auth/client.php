<?php

require dirname(__DIR__) . '/../../../../private/autoload.php';

require_once dirname(__DIR__) . "/../private/ClientConnection.php";
require_once dirname(__DIR__) . "/../private/BlocklandAuth.php";

use Glass\ServerTracker;
use Glass\NotificationManager;
use Glass\UserManager;
use Glass\UserLog;


function unauthorized() {
  $ret = new stdClass();
  $ret->status = "unauthorized";
  die(json_encode($ret, JSON_PRETTY_PRINT));
}

function badParameters() {
  $ret = new stdClass();
  $ret->status = "error";
  $ret->error  = "Bad Paramaters";
  die(json_encode($ret, JSON_PRETTY_PRINT));
}

function daaHashMissing() {
  $ret = new stdClass();
  $ret->status = "daa-hash-missing";
  die(json_encode($ret, JSON_PRETTY_PRINT));
}


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

  //ensure that the client is still who they say!
  if(!$client->checkIp($ip)) {

  }

} else {
  if($action == "checkin") {
    $action = "ident";
  }
}


switch($action) {
  case "ident":
    $username = $_REQUEST['username'] ?? false;
    $blid     = $_REQUEST['blid']     ?? false;
    $authType = $_REQUEST['authType'] ?? "default"; // daa or default



    // check parameters
    if($username === false || $blid === false) badParameters();

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

    if($authType == "daa" || $require_daa !== false) {

      // send back DAA keys so they can proceed
      $client->setDigestAccessAuth(true);

      $ret->status = ($authType == "daa") ? "daa-keys" : "daa-required";
      $ret->daa    = $client->getDigestData();

      if($require_daa !== false && $authType != "daa") {
        $ret->role = $require_daa;
      }
    } else {

      // start normal auth, no DAA
      $success = $client->attemptBlocklandAuth();
      if($success) {
        $ret->status = "success";
        $ret->ident  = $client->getIdentifier();

        $client->setAuthed(true);

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
          }
        }
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
        $success = $client->attemptBlocklandAuth();
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

  break;

  case "verify":
    if(!$client->isAuthed()) unauthorized();

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
}

echo json_encode($ret, JSON_PRETTY_PRINT);
