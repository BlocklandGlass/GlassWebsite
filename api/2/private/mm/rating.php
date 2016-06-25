<?php
require_once dirname(__DIR__) . "/../../../private/class/AddonManager.php";

$con = ClientConnection::loadFromIdentifier($_REQUEST['ident']);
if(is_object($con) && $con->isAuthed()) {
  if(isset($_REQUEST['id']) && isset($_REQUEST['rating'])) {
    $aid = $_REQUEST['id'];
    $rating = $_REQUEST['rating'];
    $blid = $con->getBLID();

    $newAvg = AddonManager::submitRating($aid, $blid, $rating);
    apc_delete('addonObject_' . $aid);

    $ret = new stdClass();
    $ret->status = "success";
    $ret->rating = $newAvg;

    echo(json_encode($ret, JSON_PRETTY_PRINT));
  }
}
?>
