<?php
require_once(realpath(dirname(__DIR__) . "/private/class/AddonManager.php"));
$uo = UserManager::getCurrent();
if(isset($_REQUEST['aid']) && isset($_REQUEST['rating'])) {
  $aid = $_REQUEST['aid'];
  $rating = $_REQUEST['rating'];
  $blid = $uo->getBLID();

  $newAvg = AddonManager::submitRating($aid, $blid, $rating);
  apc_delete('addonObject_' . $aid);
  echo $newAvg;
}
?>
