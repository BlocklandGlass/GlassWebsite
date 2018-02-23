<?php

require dirname(__DIR__) . '/../../../../private/autoload.php';

require_once dirname(__DIR__) . "/../private/ClientConnection.php";
require_once dirname(__DIR__) . "/../private/BlocklandAuth.php";

use Glass\ServerTracker;
use Glass\NotificationManager;
use Glass\UserManager;
use Glass\UserLog;

function processClientArg($clients, $ip, $port) {
  global $client;

  $clArray = array();
  if($clients !== null && trim($clients) != "") {
    $clients = stripcslashes(trim($clients));

    $clDatArray = explode("\n", $clients);
    foreach($clDatArray as $clDat) {
      if(trim($clDat) == "") continue;

      $dat = explode("\t", $clDat);
      $obj = new \stdClass();

      $obj->name = iconv("ISO-8859-1", "UTF-8", $dat[0]);
      $obj->blid = intval($dat[1]);
      $obj->status = $dat[2];
      $obj->version = $dat[3];

      $clArray[] = $obj;
    }
  }

  ServerTracker::updateRecord($ip, $port, $client->getUsername(), $clArray);
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

  if($client === false || !$client->checkIp($ip) || !$client->isServer()) {
    unauthorized();
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
    $client->setServer(true);


    // DAA is not required for servers

    if($authType == "daa") {
      // send back DAA keys so they can proceed

      $ret->status = "daa-keys";
      $ret->daa    = $client->getDigestData();
    } else {

      // start normal auth, no DAA
      $success = $client->attemptServerAuth();
      if($success) {
        $ret->status = "success";
        $ret->ident  = $client->getIdentifier();

        $client->setAuthed(true);

        $clients = $_REQUEST['clients'] ?? null;
        $port    = $_REQUEST['port'] ?? null;
        processClientArg($clients, $ip, $port);

      } else {
        // Blockland Auth failed
        $ret->status = "failed";
        $ret->message = "Blockland Authentication failed";
      }
    }

  break;

  case "daa-ident":
  case "daa-checkin":

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
        $success = $client->attemptServerAuth();
        if($success) {

          $ret->status = "success";
          $client->setAuthed(true);

          // check if the server has submitted client data
          $clients = null;
          if(is_object($data->clients)) {
            $clients = $data->clients;
          }

    			$port = $data->port;

    			ServerTracker::updateRecord($ip, $port, $name, $clients);

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

    // if we got here, the IP has already been checked
    // we're just keeping the session alive
    $ret->status = "success";
		$client->setAuthed(true);

    // this is bad, but maintaining backwards compatibility
    $clients = $_REQUEST['clients'] ?? null;
    $port    = $_REQUEST['port'] ?? null;
    processClientArg($clients, $ip, $port);

  break;

  case "verify":
    // this is a client call only
    unauthorized();
  break;

  default:
    $ret->status = "error";
    $ret->error  = "Unknown call";
}

echo json_encode($ret, JSON_PRETTY_PRINT);
