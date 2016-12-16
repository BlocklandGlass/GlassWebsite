<?php
/*$_GET['aid'] = $_REQUEST['id'];
$comments = include(dirname(__DIR__) . "/../../../private/json/getPageCommentsWithUsers.php");
echo json_encode($comments, JSON_PRETTY_PRINT);*/

use Glass\AddonManager;
use Glass\CommentManager;

$res = new \stdClass();
$res->status = "success";
$aid = $_REQUEST['id'];
$addonObject = AddonManager::getFromID($aid);

if(isset($_REQUEST['newcomment'])) {
  if($con->isAuthed()) {
    CommentManager::submitComment($addonObject->getId(), $con->getBlid(), stripcslashes($_REQUEST['newcomment']));
  } else {
    $res->status = "failed";
  }
} else {
  $res->status = "failed";
}


echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
