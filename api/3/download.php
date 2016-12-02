<?php
require_once dirname(dirname(__DIR__)) . '/private/class/AddonManager.php';
require_once dirname(dirname(__DIR__)) . '/private/class/StatManager.php';
require_once dirname(dirname(__DIR__)) . '/private/class/AWSFileManager.php';
// the point here is just to log the download and redirect to AWS
// I hope TCPClient can follow redirects?
// If it can't, I'll both Greek2Me

$type = $_REQUEST['type']; //addon_update, addon_download, build, rtb

if(isset($_REQUEST['debug'])) {
  $debug = $_REQUEST['debug'];
}

if($type == "addon_update" || $type == "addon_download") {
  $id = $_REQUEST['id'];
  if(isset($_REQUEST['branch'])) {
    $branch = $_REQUEST['branch'];
  } else {
    $branch = 1;
  }

  if($type == "addon_update") {
    StatManager::downloadAddonID($id, "update");
  } else {
    StatManager::downloadAddonID($id, "ingame");
  }


  $head = 'Location: http://' . AWSFileManager::getBucket() . '/addons/' . $id . '_' . $branch;
  if($debug) {
    echo $head;
  } else {
    header($head);
  }

  $ao = AddonManager::getFromID($id);

  /*
  //ideal code? need to review how stats work and are kept
  //(object, type[0=web, 1=ingame, 2=update], increment)
  AddonManager::incrementDailyDownloads($ao, 1 ,1);
  AddonManager::incrementWeeklyDownloads($ao, 1, 1);
  AddonManager::incrementTotalDownloads($ao, 1, 1);
  */
  
} else if($type == "rtb") {
  $filename = $_REQUEST['fn'];
  $head = 'Location: http://' . AWSFileManager::getBucket() . '/rtb/' . $filename;
  if($debug) {
    echo $head;
  } else {
    header($head);
  }
}
?>
