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
foreach($recent as $r) {
  $ao = $r->getAddon();

  if($ao->getBoard() == 10) // bargain bin
    continue;

  // $category[1] = "Client Mods";
  // $category[2] = "Server Mods";
  // $category[3] = "Bricks";
  // $category[4] = "Cosmetics";
  // $category[5] = "Gamemodes";
  // $category[6] = "Tools";
  // $category[7] = "Weapons";
  // $category[8] = "Colorsets";
  // $category[9] = "Vehicles";
  // $category[10] = "Bargain Bin";
  // $category[11] = "Sounds";

  $o = new stdClass();
  $o->id = $r->getId();
  $o->name = $r->getName();
  // $o->category = $category[$ao->getBoard()];
  $un = utf8_encode(UserLog::getCurrentUsername($r->getManagerBLID()));
  if($un === false) {
    $un = UserManager::getFromBLID($r->getManagerBLID())->getUsername();
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
