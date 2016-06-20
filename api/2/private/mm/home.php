<?php
require_once dirname(__DIR__) . "/../../../private/class/AddonManager.php";

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
$dlg = new stdClass();
$dlg->type = "recent";
$ar = array();
foreach($recent as $r) {
  $o = new stdClass();
  $o->id = $r->getId();
  $o->name = $r->getName();
  $un = UserLog::getCurrentUsername($r->getManagerBLID());
  if($un === false) {
    $un = UserManager::getFromBLID($r->getManagerBLID())->getUsername();
  }
  $o->author = UserLog::getCurrentUsername($r->getManagerBLID());
  $ar[] = $o;
}
$dlg->uploads = $ar;
$dlg->date = time();

$res = array($dlg);
$ret = new stdClass();
$ret->status = "success";
$ret->data = $res;

echo json_encode($ret, JSON_PRETTY_PRINT);
?>
