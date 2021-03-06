<?php
require_once dirname(__DIR__) . '/../../../../private/autoload.php';
use Glass\RTBAddonManager;
use Glass\AddonManager;
use Glass\UserManager;

$name = $_REQUEST['name'] ?? false;
$author = $_REQUEST['author'] ?? false;
$board = $_REQUEST['board'] ?? false;

$rtb = $_REQUEST['rtb'] ?? false;

$search = [];
if($name) {
  $search['name'] = $name;
}

if($author) {
  if(is_numeric($author)) {
    $search['blid'] = $author;
  } else {
    $user = UserManager::getFromUsername($author);
    if($user) {
      $search['blid'] = $user->getBlid();
    }
  }
}

if($board) {
  $search['board'] = $board;
}

if(sizeof($search) > 0) {
  $res = AddonManager::searchAddons($search);
} else {
  $ret = new \stdClass();
  $ret->status = "error";
  $ret->error = "invalid search type";
  die(json_encode($ret, JSON_PRETTY_PRINT));
}

$ret = new \stdClass();
$ret->results = array();
//$ret->count = ?

foreach($res as $result) {
  $r = new \stdClass();
  $addon = AddonManager::getFromId($result);
  $r->title = $addon->getName();
  $r->id = $addon->getId();

  $author = UserManager::getFromBLID($addon->getManagerBLID());
  $r->author = new stdClass();
  $r->author->username = $author->username;
  $r->author->blid = $author->blid;

  $r->summary = $addon->getSummary();
  //$r->description = $addon->getDescription();
  $ret->results[] = $r;
}

$searchRTB = $_REQUEST['rtb'] ?? false;

if($searchRTB && $name) {
  $res = RTBAddonManager::searchByName($name);
  foreach($res as $result) {
    $r = new \stdClass();

    $r->title = $result->title;
    $r->filename = $result->filename;
    $r->type = $result->type;

    $r->rtb_id = $result->id;
    $r->isRTB = true;

    $ret->results[] = $r;
  }
}

$ret->status = "success";

echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
