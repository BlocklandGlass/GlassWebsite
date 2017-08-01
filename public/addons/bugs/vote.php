<?php
	require dirname(__DIR__) . '/../../private/autoload.php';

  use Glass\AddonManager;
  use Glass\BugManager;
  use Glass\UserManager;
  use Glass\UserLog;

	require_once(realpath(dirname(__DIR__) . "/../../private/lib/Parsedown.php"));

  $id   = $_GET['id']   ?? false;
  $vote = $_GET['vote'] ?? false;

	$user = UserManager::getCurrent();
  if($user && $id && $vote && is_numeric($vote)) {
    BugManager::bugVote($id, $user->getBLID(), ($vote > 0));
  }

  header('Location: view.php?id=' . $_GET['id'])
?>
