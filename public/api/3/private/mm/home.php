<?php
require_once dirname(__DIR__) . '/../../../../private/autoload.php';
use Glass\AddonManager;
use Glass\BoardManager;
use Glass\UserLog;
use Glass\UserManager;
use Glass\StatManager;
use Glass\NewsManager;

$recent = AddonManager::getRecentAddons();
$recentUpdates = AddonManager::getRecentUpdates();


/*
 * Recent
 */

$dlg = new \stdClass();
$dlg->type = "recent";

$ar = array();
foreach($recent as $ao) {
  if(!$ao->getApproved() || $ao->getDeleted()) {
    continue;
  }

  $o = new \stdClass();
  $o->id = $ao->getId();
  $o->name = $ao->getName();
  $o->board = BoardManager::getFromID($ao->getBoard())->getName();

  $un = utf8_encode(UserLog::getCurrentUsername($ao->getManagerBLID()));
  if($un === false) {
    $un = UserManager::getFromBLID($ao->getManagerBLID())->getUsername();
  }
  $o->author = $un;

  $ar[] = $o;
}
$dlg->uploads = $ar;

$ar = array();
foreach($recentUpdates as $r) {
  $ao = $r->getAddon();

  if(!$ao->getApproved() || $ao->getDeleted()) {
    continue;
  }

  // bargain bin gone
  // if($ao->getBoard() == 10) // bargain bin
    // continue;

  $o = new \stdClass();
  $o->id = $ao->getId();
  $o->name = $ao->getName();
  $o->version = $r->getVersion();

  $ar[] = $o;
}

$dlg->updates = $ar;
$dlg->date = time();

/*
 * Message
 */

$res = array($dlg);

$news = NewsManager::getNews(0, 4);
foreach($news as $newsObj) {
  $msg = new \stdClass();
  $msg->type = "message";
  $msg->message = iconv("ISO-8859-1", "UTF-8", $newsObj->text);
  $msg->date = $newsObj->date;
  $res[] = $msg;
}

$ret = new \stdClass();
$ret->status = "success";
$ret->data = $res;

echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
