<?php
require_once dirname(__DIR__) . "/../../../private/class/AddonManager.php";
require_once dirname(__DIR__) . "/../../../private/class/UserLog.php";
require_once dirname(__DIR__) . "/../../../private/class/UserManager.php";

/*

[
  {
    type: "recent",
    uploads: [],
    updates: [],
    date: 138247923
  },{
    type: "summary",
    popular: [],
    statistics: [],
    date: 138247923
  },{
    type: "message",
    text: "asdf",
    date: 138247923
  }
]

*/

$recent = AddonManager::getRecentAddons();
$recentUpdates = AddonManager::getRecentUpdates();
$dlg = new stdClass();
$dlg->type = "recent";

$ar = array();
foreach($recent as $ao) {
  if($ao->getBoard() == 10) // bargain bin
    continue;

  $board[1] = "Client Mods";
  $board[2] = "Server Mods";
  $board[3] = "Bricks";
  $board[4] = "Cosmetics";
  $board[5] = "Gamemodes";
  $board[6] = "Tools";
  $board[7] = "Weapons";
  $board[8] = "Colorsets";
  $board[9] = "Vehicles";
  $board[10] = "Bargain Bin";
  $board[11] = "Sounds";

  $o = new stdClass();
  $o->id = $ao->getId();
  $o->name = $ao->getName();
  $o->board = $board[$ao->getBoard()];
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

$res = array($dlg);
$ret = new stdClass();
$ret->status = "success";
$ret->data = $res;

echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
