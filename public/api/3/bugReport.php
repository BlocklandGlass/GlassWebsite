<?php

require_once dirname(__DIR__) . '/../../private/autoload.php';

require_once dirname(__FILE__) . "/private/ClientConnection.php";

use Glass\UserLog;
use Glass\BugManager;

header("Content-Type: text/json");

$ret = new stdClass();
$ret->status = "failed";

$title = $_REQUEST['title'] ?? false;
$body  = $_REQUEST['body']  ?? false;
$aid   = $_REQUEST['aid']   ?? false;
$ident = $_REQUEST['ident'] ?? false;

if(!$title || !$body || !$aid || $ident) {
  die(json_encode($ret, JSON_PRETTY_PRINT));
}

$con = ClientConnection::loadFromIdentifier();
if(!is_object($con) || !$con->isAuthed()) {
  die(json_encode($ret, JSON_PRETTY_PRINT));
}

if(strlen($title) < 5 || strlen($body) < 5 || $aid < 0) {
  die(json_encode($ret, JSON_PRETTY_PRINT));
}

$success = BugManager::newBug($aid, $con->getBlid(), $title, $body);

if($success !== false) {
  $ret->status = "success";
}
die(json_encode($ret, JSON_PRETTY_PRINT));
?>
