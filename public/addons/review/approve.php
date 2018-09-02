<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
  use Glass\UserManager;
  use Glass\AddonManager;
  use Glass\CommentManager;

  $userObject = UserManager::getCurrent();

  if(!$userObject || !$userObject->inGroup("Reviewer")) {
    header('Location: /addons');
    return;
  }

  if(!isset($_POST['aid'])) {
    die('AID not specified.');
  }

  if(!isset($_POST['board'])) {
    die('Board not specified.');
  }

  if(!isset($_POST['action'])) {
    die('No action specified.');
  }

  if(!isset($_POST['confirmed'])) {
    die('Inspection action was not confirmed.');
  }

  $addonObject = AddonManager::getFromId($_POST['aid']);

  if(!$addonObject) {
    die('Invalid add-on specified.');
  }

  if($addonObject->approved != 0 && !$userObject->inGroup("Administrator")) {
    die('Add-on already approved/rejected.');
  }

  if($addonObject->getDeleted()) {
    die('Add-on no longer available.');
  }

  if($_POST['action'] == "Approve") {
    AddonManager::approveAddon($_POST['aid'], $_POST['board'], $userObject->getBLID());
    CommentManager::deleteCommentsFromAddon($_POST['aid']);
    header('Location: index.php');
  } else if($_POST['action'] == "Reject") {
    if(!isset($_POST['reason'])) {
      die('No reason specified.');
    }

    AddonManager::rejectAddon($_POST['aid'], $_POST['reason'], $userObject->getBLID());
    header('Location: index.php');
  } else {
    die('Invalid action specified.');
  }
?>
