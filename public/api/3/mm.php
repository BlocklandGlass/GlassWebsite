<?php
require_once dirname(__DIR__) . '/../../private/autoload.php';
require_once dirname(__FILE__) . "/private/ClientConnection.php";
require_once dirname(__FILE__) . "/private/BlocklandAuth.php";

header('Content-Type: text/json');

if(isset($_REQUEST['ident'])) {
  $con = ClientConnection::loadFromIdentifier($_REQUEST['ident']);
} else {
  $con = false;
}

if($con === false) {
  $ret = new \stdClass();
  $ret->status = "error";
  $ret->error = "Session error";
  $ret->action = "auth";

  die(json_encode($ret, JSON_PRETTY_PRINT));
}

$call = $_REQUEST['call']; //board, tag, home, search, addon, comments, build

if(is_file(dirname(__FILE__) . "/private/mm/" . $call . ".php")) {
  require_once dirname(__FILE__) . "/private/mm/" . $call . ".php";
} else {
  $ret = new \stdClass();
  $ret->status = "error";
  $ret->error = "API Call doesn't exist";
  die(json_encode($ret, JSON_PRETTY_PRINT));
}
