<?php
/*$_GET['aid'] = $_REQUEST['id'];
$comments = include(dirname(__DIR__) . "/../../../private/json/getPageCommentsWithUsers.php");
echo json_encode($comments, JSON_PRETTY_PRINT);*/

use Glass\AddonManager;
use Glass\CommentManager;
$aid = $_REQUEST['id'];
if(!isset($_REQUEST['page'])) {
  $page = 0;
} else {
  $page = $_REQUEST['page'];
}

$addonObject = AddonManager::getFromID($aid);

if(isset($_REQUEST['newcomment'])) {
  if($con->isAuthed()) {
    CommentManager::submitComment($addonObject->getId(), $con->getBlid(), stripcslashes($_REQUEST['newcomment']));
  }
}

$res = new \stdClass();
$res->status = "success";
$ret = array();

$start = $page*10;

$comments = CommentManager::getCommentIDsFromAddon($addonObject->getId(), $start, 10, 1);
foreach($comments as $comid) {
  $comment = CommentManager::getFromId($comid);

  $commento = new \stdClass();
  $commento->id = $comment->getId();
  $commento->author = utf8_encode(UserLog::getCurrentUsername($comment->getBLID()));
  $commento->authorblid = $comment->getBlid();
  $text = str_replace("\r\n", "<br>", $comment->getComment());
  $text = str_replace("\n", "<br>", $text);
  $commento->text = utf8_encode($text);
  $commento->date = date("M jS Y, g:i A", strtotime($comment->getTimeStamp()));
  $ret[] = $commento;
}

$res->comments = $ret;

echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
