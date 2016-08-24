<?php
require_once dirname(__DIR__) . "/../../../private/class/BoardManager.php";
require_once dirname(__DIR__) . "/../../../private/class/AddonManager.php";
require_once dirname(__DIR__) . "/../../../private/class/RTBAddonManager.php";
require_once dirname(__DIR__) . "/../../../private/class/UserManager.php";
require_once dirname(__DIR__) . "/../../../private/class/UserLog.php";

header('Content-Type: text/json');


if(isset($_REQUEST['page'])) {
  $page = $_REQUEST['page'];
} else {
  $page = 1;
}

if($_REQUEST['id'] == "rtb") {
  $ret = new stdClass();
  $ret->rtb = 1;
  $ret->addons = array();
  $addons = RTBAddonManager::getAddons($page);

  foreach($addons as $ad) {
    $ao = new stdClass();
    $ao->id = $ad->id;
    $ao->name = $ad->title;
    $ao->author = "RTB";
    $ao->ratings = "0";
    $ao->downloads = "N/A";

    $ret->addons[] = $ao;
  }

  $ret->status = "success";
  $ret->board_id = "rtb";
  $ret->board_name = "RTB Archives";
  $ret->page = $page;
  $ret->pages = ceil(RTBAddonManager::getCount()/10);

  echo json_encode($ret, JSON_PRETTY_PRINT);
  return;
}

$boardObject = BoardManager::getFromID($_REQUEST['id']);
$addonIds = AddonManager::getFromBoardID($boardObject->getID(), ($page-1)*10, 10);

$ret = new stdClass();
$ret->addons = array();

foreach($addonIds as $aid) {
  $addon = AddonManager::getFromID($aid);

  if($addon->getRating() == null) {
    $rating = 0;
  } else {
    $rating = $addon->getRating();
  }

  $retboard = new stdClass();
  $retboard->id = $addon->getId();
  $retboard->name = $addon->getName();

  $user = UserLog::getCurrentUsername($addon->getManagerBLID());
  if($user == false) {
    $uo = UserManager::getFromBlid($addon->getManagerBLID());
    if($uo !== false) {
      $user = $uo->getUsername();
    } else {
      $user = "Blockhead";
    }
  }

  $retboard->author = $user;
  $retboard->rating = $rating;
  $retboard->downloads = $addon->getDownloads("web")+$addon->getDownloads("ingame");
  $ret->addons[] = $retboard;
}

$ret->status = "success";
$ret->board_id = $boardObject->getId();
$ret->board_name = $boardObject->getName();
$ret->page = $page;
$ret->pages = ceil($boardObject->getCount()/10);

echo json_encode($ret, JSON_PRETTY_PRINT);
?>
