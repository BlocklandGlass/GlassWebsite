<?php
  require_once dirname(__DIR__) . '/../private/autoload.php';
  use Glass\UploadManager;
  use Glass\UserManager;
  use Glass\AddonManager;

  header('Content-Type: text/json');

  $id = $_REQUEST['id'] ?? false;

	$user = UserManager::getCurrent();
	$addonObject = AddonManager::getFromId($id);
	if($user === false || $addonObject === false || ($addonObject->getManagerBLID() !== $user->getBlid() && !$user->inGroup("Administrator"))) {
		header("Location: /index.php");
		die();
	}

  $file = $_FILES['image'] ?? false;

  $res = new stdClass();

  if($id === false || $file === false) {
    $res->status = "error";
    $res->error = "Missing parameter(s)";
  } else {
    try {
      $res = UploadManager::handleAJAXScreenshot($id, $file);
    } catch (\Exception $e) {
      $res->status = "error";
      $res->error = $e->getMessage();
    }
  }

  echo json_encode($res, JSON_PRETTY_PRINT);
?>
