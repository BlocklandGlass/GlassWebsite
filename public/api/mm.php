<?php
header('Content-Type: text/json');
use Glass\AddonManager;
use Glass\BoardManager;
use Glass\UserManager;
use Glass\Comment;
require_once dirname(__DIR__) . '/class/api/ApiSessionManager.php';
use Glass\Notification;
$request = $_GET['request'];

if($request == "boards") {
  $ret = array();

  $boards = BoardManager::getAllBoards();
  usort($boards, function($a, $b) {
      return strcmp($a->getName(), $b->getName());
  });
  $subcat = array();
  foreach($boards as $board) {
    $subcat[$board->getSubCategory()][] = $board;
  }
  foreach($subcat as $subName=>$sub) {
    foreach($sub as $board) {
      $ro = new stdClass();
      $ro->id = $board->getId();
      $ro->image = $board->getImage();
      $ro->name = $board->getName();
      $ro->files = $board->getCount();
      $ro->sub = $subName;
      $ret[] = $ro;
    }
  }

  $bargainBin = new stdClass();
  $bargainBin->id = "-1";
  $bargainBin->special = "1";
  $bargainBin->name = "Bargain Bin";
  $bargainBin->image = "bin";
  $bargainBin->sub = "Special";
  $bargainBin->files = sizeof(AddonManager::getBargain());
  $ret[] = $bargainBin;

  $bargainBin = new stdClass();
  $bargainBin->id = "-2";
  $bargainBin->special = "1";
  $bargainBin->name = "RTB Archive";
  $bargainBin->image = "bricks";
  $bargainBin->sub = "Special";
  $ret[] = $bargainBin;


  echo json_encode($ret, JSON_PRETTY_PRINT);
  return;
}

if($request == "submitcomment") {
  $addonObject = AddonManager::getFromId($_REQUEST['id']);
  if(isset($_REQUEST['sid'])) {
  	$apiManager = new ApiSessionManager($_REQUEST['sid']);
    if($apiManager->isRemoteVerified() && $apiManager->isVerified()) {
      $current = $apiManager->getSiteAccount();
    	(new Comment(array($_REQUEST['comment'], $current)))->toDatabase($addonObject->getId());
    	(new NewCommentNotification(array($current->getId(), $addonObject->getId())))->toDatabase();
    }
  } else {
    $ret = new stdClass();
    $ret->status = "error";
    $ret->error = "Must be authed with Glass";
  }
}

if($request == "board") {
  $boardId = $_GET['board'];

  $ret = array();

  if($boardId < 0) {
    if($boardId == -1) {
      foreach(AddonManager::getBargain() as $addon) {

        $ratingData = $addon->getRatingData();
        $ro = new stdClass();
        $ro->id = $addon->getId();
        $ro->title = $addon->getName();
        //$ro->rating = $ratingData['average'];
        $ro->author = $addon->getAuthor()->getName();

        $fo = $addon->getFile($addon->getLatestBranch());
        $ro->server = $fo->isServer();
        $ro->client = $fo->isClient();

        $ro->temp_branch = $addon->getLatestBranch();
        $ro->temp_filename = $addon->getFilename();

        $ro->downloads = ($addon->getDownloads(1) + $addon->getDownloads(2));

        $ret[] = $ro;
      }
      echo json_encode($ret, JSON_PRETTY_PRINT);
      return;
    }
  } else {
    $boardObject = BoardManager::getFromId($boardId);
    $addons = $boardObject->getAddons();
    foreach($addons as $addon) {
      $ratingData = $addon->getRatingData();
      $ro = new stdClass();
      $ro->id = $addon->getId();
      $ro->title = $addon->getName();
      //$ro->rating = $ratingData['average'];
      $ro->author = $addon->getAuthor()->getName();

      $fo = $addon->getFile($addon->getLatestBranch());
      $ro->server = $fo->isServer();
      $ro->client = $fo->isClient();

      $ro->temp_branch = $addon->getLatestBranch();
      $ro->temp_filename = $addon->getFilename();

      $ro->downloads = ($addon->getDownloads(1) + $addon->getDownloads(2));

      $ret[] = $ro;
    }

    echo json_encode($ret, JSON_PRETTY_PRINT);
    return;
  }
}

if($request == "addon") {
  $addonId = $_GET['id'];
  $addonObject = AddonManager::getFromId($addonId);

  $ret = new stdClass();

  $ret->aid = $addonId;
  $ret->filename = $addonObject->getFilename();
  $ret->board = $addonObject->getBoard()->getId();
  $ret->screenshotcount = $addonObject->getScreenshotCount();
  $ret->name = $addonObject->getName();

  $ret->description = htmlspecialchars_decode($addonObject->getDescription());

  $author = new stdClass();
  $author->blid = $addonObject->getAuthor()->getBlid();
  $author->name = $addonObject->getAuthor()->getName();
  $ret->author = $author;

  $ret->dependencies = array();
  $depend = $addonObject->getDependancies();
  foreach($depend->addons as $ad) {
    $ao = AddonManager::getFromId($ad);

    $obj = new stdClass();
    $obj->name = $ao->getName();
    $obj->id = $ao->getId();
    $obj->filename = $ao->getFilename();
    $obj->board = $ao->getBoard()->getId();
    $ret->dependencies[] = $obj;
  }

  for($i = 0; $i < $addonObject->getScreenshotCount(); $i++) {
    $screenshot = new stdClass();
    $screenshot->id = $i;
    $screenshot->url = "http://api.blocklandglass.com/files/screenshots/" . $addonObject->getId() . "/" . $i . ".png";
    $screenshot->thumbnail = "http://api.blocklandglass.com/files/screenshots/" . $addonObject->getId() . "/" . $i . "_thumb.png";
    list($width, $height) = getimagesize(dirname(__DIR__) . "/files/screenshots/" . $addonObject->getId() . "/" . $i . ".png");
    $screenshot->extent = $width . " " . $height;
    $ret->screenshots[] = $screenshot;
  }

  for($i = 0; $i < 3; $i++) {
    try {
      $fo = $addonObject->getFile($i+1);
      $branchDat = new stdClass();
      $branchDat->id = $i+1;
      $branchDat->file = $fo->getId();
      $branchDat->version = $addonObject->getLatestVersion($i+1);
      $branchDat->mal = $fo->getMalicious();
      $ret->branches[] = $branchDat;
    } catch (Exception $e) {
      continue;
    }
  }
  echo json_encode($ret, JSON_PRETTY_PRINT);
  return;
}

if($request == "comments") {
  $ret = array();
  if(!isset($_GET['page'])) {
    $page = 0;
  } else {
    $page = $_GET['page'];
  }
  $start = $page*10;
  $end = $start+10;

  $addonObject = AddonManager::getFromId($_GET['aid']);

  $comments = $addonObject->getCommentsRange($start, $end);
  foreach($comments as $comment) {
    $commento = new stdClass();
    $commento->author = $comment->getAuthor()->getName();
    $commento->authorblid = $comment->getAuthor()->getBlid();
    $text = str_replace("\r\n", "<br>", $comment->getText());
    $text = str_replace("\n", "<br>", $text);
    $commento->text = $text;
    $commento->date = date("F j, g:i a", strtotime($comment->getTime()));
    $ret[] = $commento;
  }

  echo json_encode($ret, JSON_PRETTY_PRINT);
  return;
}
?>
