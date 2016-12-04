<?php
require_once dirname(__DIR__) . "/../../../private/class/AddonManager.php";
require_once dirname(__DIR__) . "/../../../private/class/BoardManager.php";
require_once dirname(__DIR__) . "/../../../private/class/UserLog.php";
require_once dirname(__DIR__) . "/../../../private/class/UserManager.php";

$recent = AddonManager::getRecentAddons();
$recentUpdates = AddonManager::getRecentUpdates();


/*
 * Recent
 */

$dlg = new stdClass();
$dlg->type = "recent";

$ar = array();
foreach($recent as $ao) {

  $o = new stdClass();
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

  if(!$ao->getApproved())
    continue;

  if($ao->getBoard() == 10) // bargain bin
    continue;

  $o = new stdClass();
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

$msg = new stdClass();
$msg->type = "message";
$msg->message = "Hey look. We can do other things here";

$res = array($dlg, $msg);
$ret = new stdClass();
$ret->status = "success";
$ret->data = $res;

echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
