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
  $o->author = $r->getManager()->getName();
  $ar[] = $o;
}
$dlg->uploads = $ar;
$dlg->date = time();

$ret = array($dlg);

echo json_encode($ret, JSON_PRETTY_PRINT);
?>
