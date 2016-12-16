<?php
use Glass\AddonManager;
$uo = UserManager::getCurrent();
if(isset($_REQUEST['aid']) && isset($_REQUEST['rating'])) {
  $aid = $_REQUEST['aid'];
  $rating = $_REQUEST['rating'];
  $blid = $uo->getBLID();

  $newAvg = AddonManager::submitRating($aid, $blid, $rating);
  echo $newAvg;
}
?>
