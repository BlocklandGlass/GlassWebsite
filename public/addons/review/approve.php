<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
  use Glass\UserManager;
  use Glass\AddonManager;
  
  $userObject = UserManager::getCurrent();
  if(!$userObject || !$userObject->inGroup("Reviewer")) {
    header('Location: /addons');
    return;
  }
  if(isset($_POST['action']) && is_object($userObject)) {
    if($_POST['action'] == "Approve") {
      // approve
      AddonManager::approveAddon($_POST['aid'], $_POST['board'], $userObject->getBLID());
      header('Location: list.php');
    } else if($_POST['action'] == "Reject") {
      AddonManager::rejectAddon($_POST['aid'], $_POST['reason'], $userObject->getBLID());
      header('Location: list.php');
    }
  }
?>
