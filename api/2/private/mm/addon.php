<?php
require_once dirname(__DIR__) . "/../../../private/class/AddonManager.php";
require_once dirname(__DIR__) . "/../../../private/class/BoardManager.php";
require_once dirname(__DIR__) . "/../../../private/class/ScreenshotManager.php";

$ret = new stdClass();

if(isset($_REQUEST['id']) & $_REQUEST['id'] != "") {
  $aid = $_REQUEST['id'];
  $ret->status = "success";
} else {
  $ret->status = "error";
  $ret->error = "Add-On not found!";
  die(json_encode($ret, JSON_PRETTY_PRINT));
}

$addonObject = AddonManager::getFromID($aid);
//$screens = ScreenshotManager::getScreenshotsFromAddon($aid); //I dont think this is done

if(!$addonObject->getApproved()) {
  $ret->status = "error";
  $ret->error = "Add-On not approved";
  die(json_encode($ret, JSON_PRETTY_PRINT));
}


$ret->aid = $aid;
$ret->filename = $addonObject->getFilename();
$ret->boardId = $addonObject->getBoard();
$ret->board = BoardManager::getFromID($addonObject->getBoard())->getName();
$ret->name = $addonObject->getName();
$ret->description = htmlspecialchars_decode($addonObject->getDescription());
$ret->rating = $addonObject->getRating();

$ret->screenshots = array();
$screens = ScreenshotManager::getScreenshotsFromAddon($aid);
foreach($screens as $sid) {
  $ss = ScreenshotManager::getFromId($sid);
  $screenshot = new stdClass();
  $screenshot->id = $ss->getId();
  $screenshot->url = $ss->getUrl();
  $screenshot->thumbnail = $ss->getThumbUrl();
  $screenshot->extent =  $ss->getX() . " " . $ss->getY();
  $ret->screenshots[] = $screenshot;
}

$author = new stdClass();
$manager = UserManager::getFromBlid($addonObject->getManagerBLID());
$author->blid = $manager->getBlid();
$author->name = $manager->getName();
$ret->authors = array($author);

$channelId[1] = "stable";
$channelId[2] = "beta";
$channel = new stdClass();

$channel->id = 1;
$channel->name = "stable";
$channel->version = $addonObject->getVersion();
// TODO
//$channel->malicious = $chanDat->malicious;

$ret->branches[] = $channel;


echo json_encode($ret, JSON_PRETTY_PRINT);
?>
