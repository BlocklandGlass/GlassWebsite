<?php
require_once dirname(__DIR__) . "/../../../private/class/AddonManager.php";
require_once dirname(__DIR__) . "/../../../private/class/BoardManager.php";
require_once dirname(__DIR__) . "/../../../private/class/CommentManager.php";
require_once dirname(__DIR__) . "/../../../private/class/UserLog.php";
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

if($addonObject == false) {
  $ret->status = "error";
  $ret->error = "Add-On does not exist";
  die(json_encode($ret, JSON_PRETTY_PRINT));
}

if(!$addonObject->getApproved()) {
  $ret->status = "error";
  $ret->error = "Add-On not approved";
  die(json_encode($ret, JSON_PRETTY_PRINT));
}


$ret->aid = $aid;
$ret->filename = $addonObject->getFilename();

$ret->board_id = $addonObject->getBoard();
$ret->board = BoardManager::getFromID($addonObject->getBoard())->getName();

$ret->name = $addonObject->getName();
$ret->description = utf8_encode(htmlspecialchars_decode($addonObject->getDescription()));

$ret->date = date("M jS Y, g:i A", strtotime($addonObject->getUploadDate()));

$ret->downloads = $addonObject->getDownloads("web") + $addonObject->getDownloads("ingame");
$ret->rating = $addonObject->getRating();

$ret->author = UserManager::getFromBLID($addonObject->getManagerBLID())->getName();

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

$user = UserLog::getCurrentUsername($addonObject->getManagerBLID());
if($user == false) {
  $user = UserManager::getFromBlid($addonObject->getManagerBLID())->getUsername();
} else {
  $user = utf8_encode($user);
}

$author->blid = $addonObject->getManagerBLID();
$author->name = $user;

$ret->contributors = array($author);

$channelId[1] = "stable";
$channelId[2] = "beta";
$channel = new stdClass();

$channel->id = 1;
$channel->name = "stable";
$channel->version = $addonObject->getVersion();

$ret->branches[] = $channel;

//================================
// comments and updates
//================================

$activity = [];

$start = 0;

$comments = CommentManager::getCommentIDsFromAddon($addonObject->getId(), $start, 15, 1);
foreach($comments as $comid) {
  $comment = CommentManager::getFromId($comid);

  $action = new stdClass();
  $action->type = "comment";
  $action->timestamp = $comment->getTimeStamp();

  $action->date = date("M jS Y, g:i A", strtotime($comment->getTimeStamp()));

  $action->author = utf8_encode(UserLog::getCurrentUsername($comment->getBLID()));
  $action->authorBlid = $comment->getBlid();

  $text = str_replace("\r\n", "<br>", $comment->getComment());
  $text = str_replace("\n", "<br>", $text);
  $action->comment = utf8_encode($text);

  $activity[] = $action;
}

$updates = AddonManager::getUpdates($addonObject);
foreach($updates as $update) {
  $action = new stdClass();
  $action->type = "update";
  $action->timestamp = $update->getTimeSubmitted();

  $action->date = date("M jS Y, g:i A", strtotime($action->timestamp));

  $action->version = $update->getVersion();

  $action->changelog = utf8_encode($update->getChangeLog());

  $activity[] = $action;
}

usort($activity, function($a, $b)
{
    return strtotime($a->timestamp) < strtotime($b->timestamp) ? 1 : -1;
});

$ret->activity = $activity;

echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
