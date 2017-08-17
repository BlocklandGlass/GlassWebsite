<?php
require dirname(__DIR__) . '/../../private/autoload.php';

require_once dirname(__FILE__) . "/private/ClientConnection.php";

use Glass\UserManager;

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

$ident  = $_GET['ident'] ?? false;
$ip     = $_SERVER['REMOTE_ADDR'];

$ret    = new stdClass();

$client = false;
if($ident) {
  $client = ClientConnection::loadFromIdentifier($ident);

  if($client === false || !$client->checkIp($ip)) {
    unauthorized();
  }
}

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
  $call   = $data->call; // the equivalent local .php file
  $params = $data->params; // the GET/POST variables associated

  if(!ctype_alpha($cal))
    unauthorized();

  $file = dirname(__FILE__) . '/' . $call . '.php';
  if(is_file($file)) {

    foreach($params as $key=>$val) {
      $_REQUEST[$key] = $val;
    }
    require realpath($file);

  } else {
    badParameters();
  }
} else {
  unauthorized();
}
