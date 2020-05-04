<?php
require dirname(__DIR__) . '/../../private/autoload.php';

require_once dirname(__FILE__) . "/private/ClientConnection.php";
require_once dirname(__FILE__) . "/private/BlocklandAuth.php";

use Glass\ServerTracker;
use Glass\NotificationManager;
use Glass\UserLog;

ini_set('display_errors', 0);

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

//fields -
// ident    - unique numerical session identifier
// username - username
// blid     - blockland id
// version  - version of

header('Content-Type: text/json');

$isServer = ($_REQUEST['server'] ?? false) != false;

if($isServer) {
	require dirname(__FILE__) . '/private/auth/server.php';
	return;
} else {
	require dirname(__FILE__) . '/private/auth/client.php';
	return;
}
?>
