<?php
require_once dirname(__DIR__) . '/../../../../private/autoload.php';
use Glass\BoardManager;
use Glass\AddonManager;
use Glass\RTBAddonManager;
use Glass\UserManager;
use Glass\UserLog;

header('Content-Type: text/json; charset=utf-8');


if(isset($_REQUEST['page'])) {
  $page = $_REQUEST['page'];
} else {
  $page = 1;
}

if($_REQUEST['id'] == "rtb") {
  $ret = new \stdClass();
  $ret->rtb = 1;
  $ret->addons = array();
  $addons = RTBAddonManager::getAddons($page);

  foreach($addons as $ad) {
    $ao = new \stdClass();
    $ao->id = $ad->id;
    $ao->name = $ad->title;
    $ao->author = "RTB";
    $ao->downloads = "N/A";

    $ret->addons[] = $ao;
  }

  $ret->status = "success";
  $ret->board_id = "rtb";
  $ret->board_name = "RTB Archive";
  $ret->page = $page;
  $ret->pages = ceil(RTBAddonManager::getCount()/10);

  echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  return;
}

$boardObject = BoardManager::getFromID($_REQUEST['id']);
$addonIds = AddonManager::getFromBoardID($boardObject->getID(), ($page-1)*10, 10);

$ret = new \stdClass();
$ret->addons = array();

foreach($addonIds as $aid) {
  $addon = AddonManager::getFromID($aid);

  $retboard = new \stdClass();
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
  } else {
    $user = utf8_encode($user);
  }

  $retboard->author = $user;
  $retboard->downloads = $addon->getDownloads("web") + $addon->getDownloads("ingame");
  $ret->addons[] = $retboard;
}

$ret->status = "success";
$ret->board_id = $boardObject->getId();
$ret->board_name = $boardObject->getName();
$ret->page = $page;
$ret->pages = ceil($boardObject->getCount()/10);

echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
