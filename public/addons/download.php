<?php
	require dirname(__DIR__) . '/../private/autoload.php';
  use Glass\AddonManager;
  use Glass\AWSFileManager;
  use Glass\StatManager;

  $id = $_REQUEST['id'];
  $addonObject = AddonManager::getFromId($id);
  if($addonObject !== false && $addonObject->getApproved() && !$addonObject->isRejected() && !$addonObject->getDeleted()) {
    StatManager::downloadAddon($addonObject, "web", $_SERVER['REMOTE_ADDR']);

		header('Location: http://' . AWSFileManager::getBucket() . '/addons/' . $id);
  } else {
    header("Location: /index.php");
    die();
  }
?>