<?php
require_once dirname(__DIR__) . "/../../../private/class/AddonManager.php";

$by = $_REQUEST['by'];
$type = $_REQUEST['type'];
$query = $_REQUEST['query'];

if($type == "addon") {
  if($by == "name" || $by == "blid") {
    $res = AddonManager::searchAddons(array($by=>$query));
  } else {
    $ret = new stdClass();
    $ret->status = "error";
    $ret->error = "invalid search type";
    die(json_encode($ret, JSON_PRETTY_PRINT));
  }

  $ret = new stdClass();
  $ret->results = array();
  //$ret->count = ?

  foreach($res as $result) {
    $r = new stdClass();
    $addon = AddonManager::getFromId($result);
    $r->type = "addon";
    $r->title = $addon->getName();
    //$r->author
    $r->description = addslashes($addon->getDescription());
    $ret->results[] = $r;
  }
} else {
  //coming soon?
}

$ret->status = "success";

echo json_encode($ret, JSON_PRETTY_PRINT);
?>
