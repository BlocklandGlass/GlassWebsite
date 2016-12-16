<?php
session_start();
use Glass\AddonManager;
var_dump($_POST);

$addon = AddonManager::getFromID($_POST['aid']);
$userObject = UserManager::getCurrent();

$reviewer = false;

$owner = false;
if($userObject->getBlid() == $addon->getManagerBLID()) {
  $owner = true;
}

if((!$userObject || !$userObject->inGroup("Reviewer")) && !$owner) {
  header('Location: /addons');
  return;
} else {
  $reviewer = true;
}
if($reviewer) {
  if(isset($_POST['action']) && is_object($userObject)) {
    if($_POST['action'] == "Approve") {
      // approve
      $update = AddonManager::getUpdates($addon)[0];
      try {
        AddonManager::approveUpdate($update);
      } catch(Exception $e) {

      }
      header('Location: updates.php');
    } else if($_POST['action'] == "Reject") {
      // reject
    }
  }
}

if($owner) {
  if($_POST['action'] == "Cancel Update") {

  }
}
?>
