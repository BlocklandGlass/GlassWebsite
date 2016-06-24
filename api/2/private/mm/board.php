<?php
require_once dirname(__DIR__) . "/../../../private/class/BoardManager.php";
require_once dirname(__DIR__) . "/../../../private/class/AddonManager.php";
require_once dirname(__DIR__) . "/../../../private/class/UserManager.php";

$boardObject = BoardManager::getFromID($_REQUEST['id']);
if(isset($_REQUEST['page'])) {
  $page = $_REQUEST['page'];
} else {
  $page = 1;
}
$addonIds = AddonManager::getFromBoardID($boardObject->getID(), ($page-1)*10, 10);

$ret = new stdClass();
$ret->addons = array();
$boards = BoardManager::getAllBoards();

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
  $retboard->author = UserManager::getFromBLID($addon->getManagerBLID())->getName();
  $retboard->rating = $rating;
  $retboard->downloads = $addon->getDownloads();
  $ret->addons[] = $retboard;
}

$ret->status = "success";
$ret->board_id = $boardObject->getId();
$ret->board_name = $boardObject->getName();
$ret->page = $page;
$ret->pages = ceil($boardObject->getCount()/10);

echo json_encode($ret, JSON_PRETTY_PRINT);
?>
