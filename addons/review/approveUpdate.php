<?php
session_start();
require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
var_dump($_POST);
$userObject = UserManager::getCurrent();
if(!$userObject || !$userObject->inGroup("Reviewer")) {
  header('Location: /addons');
  return;
}
if(isset($_POST['action']) && is_object($userObject)) {
  if($_POST['action'] == "Approve") {
    // approve
    $addon = AddonManager::getFromID($_POST['aid']);
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
?>
