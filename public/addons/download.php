<?php
use Glass\AddonManager;
use Glass\AWSFileManager;
use Glass\StatManager;

$id = $_REQUEST['id'];
$addonObject = AddonManager::getFromId($id);
if($addonObject !== false) {
  StatManager::downloadAddon($addonObject);
  if(isset($_REQUEST['beta'])) {
    $bid = ($_REQUEST['beta'] == 1 ? 2 : 1);
  } else {
    $bid = 1;
  }
  //echo 'Location: http://' . AWSFileManager::getBucket() . '/addons/' . $id . "_" . $bid;
  header('Location: http://' . AWSFileManager::getBucket() . '/addons/' . $id . "_" . $bid);
} else {
  header('Status: 404');
  header('Location: /error.php');
}
?>
