<?php
	require dirname(__DIR__) . '/../../private/autoload.php';

  use Glass\AddonManager;
  use Glass\BugManager;
  use Glass\UserManager;
  use Glass\UserLog;

	require_once(realpath(dirname(__DIR__) . "/../../private/lib/Parsedown.php"));

  $id   = $_GET['id']   ?? false;
  $open = $_GET['open'] ?? 0;
  $open = $open == 1;

	$user = UserManager::getCurrent();
  if($user && $id) {
    $bug  = BugManager::getFromId($id);
    if($bug) {
      $addon = AddonManager::getFromId($bug->aid);
      if($addon->getManagerBLID() == $user->getBLID()) {
        BugManager::closeBugReport($id, $open);
      }
    }
  }

  header('Location: view.php?id=' . $_GET['id'])
?>
