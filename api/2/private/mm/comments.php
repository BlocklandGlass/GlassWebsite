<?php
$_GET['aid'] = $_REQUEST['id'];
$comments = include(dirname(__DIR__) . "/../../../private/json/getPageCommentsWithUsers.php");
echo json_encode($comments, JSON_PRETTY_PRINT);

/*require_once dirname(__DIR__) . "/../../../private/class/AddonManager.php";
require_once dirname(__DIR__) . "/../../../private/class/CommentManager.php";
$aid = $_REQUEST['id'];
if(!isset($_REQUEST['page'])) {
  $page = 0;
} else {
  $page = $_REQUEST['page'];
}

$addonObject = AddonManager::getFromID($aid);

$ret = array();

$start = $page*10;
$end = $start+10;

$comments = CommentManager::getCommentIDsFromAddon($addonObject->getId(), $start, 10);
foreach($comments as $comid) {
  $comment = CommentManager::getFromId($comid);

  $commento = new stdClass();
  $commento->id = $comment->getId();
  $commento->author = UserLog::getCurrentUsername($comment->getBLID());
  $commento->authorblid = $comment->getBlid();
  $text = str_replace("\r\n", "<br>", $comment->getComment());
  $text = str_replace("\n", "<br>", $text);
  $commento->text = $text;
  $commento->date = date("F j, g:i a", strtotime($comment->getTimeStamp()));
  $ret[] = $commento;
}

echo json_encode($ret, JSON_PRETTY_PRINT);*/
?>
