<?php
	require dirname(__DIR__) . '/../private/autoload.php';
  use Glass\AddonManager;
  use Glass\AWSFileManager;
  use Glass\StatManager;
  use Glass\UserManager;

  $id = $_REQUEST['id'];
  $addonObject = AddonManager::getFromId($id);

  if($addonObject === false) {
    header("Location: /addons");
    die();
  }

  $user = UserManager::getCurrent();

  if($user && $user->inGroup("Reviewer") && !$addonObject->getApproved() || $addonObject->isRejected() && !$addonObject->getDeleted()) {
    header('Location: http://' . AWSFileManager::getBucket() . '/addons/' . $id);
  } else {
    if($addonObject->getApproved() && !$addonObject->isRejected() && !$addonObject->getDeleted()) {
      StatManager::downloadAddon($addonObject, "web", $_SERVER['REMOTE_ADDR']);

      header('Location: http://' . AWSFileManager::getBucket() . '/addons/' . $id);
    } else {
      header("Location: /addons");
    }
  }
?>