<?php
require dirname(__FILE__) . '/autoload.php';
use Glass\UserManager;
use Glass\AddonManager;
use Glass\RTBAddonManager;
?>
<div class="navcontainer darkgreen">
  <div class="navcontent">
    <?php
      include(dirname(__FILE__) . '/searchbar.php');
    ?>
    <ul>
      <li><a class="navbtn" href="/addons/boards.php">Boards</a></li>
      <li><a class="navbtn" href="/addons/rtb/">RTB Archive</a></li>
      <?php
        if($user && $user->inGroup("Reviewer")) {
      ?>
      <li><a class="navbtn" href="/addons/review">Review<?php if(sizeof(AddonManager::getUnapproved()) > 0) { echo " <span class=\"notice\">" . sizeof(AddonManager::getUnapproved()) . "</span>"; } ?></a></li>
      <li><a class="navbtn" href="/addons/review/updates.php">Updates<?php if(sizeof(AddonManager::getPendingUpdates()) > 0) { echo " <span class=\"notice\">" . sizeof(AddonManager::getPendingUpdates()) . "</span>"; } ?></a></li>
      <li><a class="navbtn" href="/addons/review/reclaims.php">Reclaims<?php if(sizeof(RTBAddonManager::getPendingReclaims()) > 0) { echo " <span class=\"notice\">" . sizeof(RTBAddonManager::getPendingReclaims()) . "</span>"; } ?></a></li>
    </ul>
    <?php } ?>
  </div>
</div>