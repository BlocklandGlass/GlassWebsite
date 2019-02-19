<?php
	//TO DO: actually implement this page
	//	needs multiple files per request support
	if(!isset($_SESSION)) {
		session_start();
	}
	//we give the session a unique csrf token so malicious links on other sites cannot take advantage of users
	if(!isset($_SESSION['csrftoken'])) {
		$_SESSION['csrftoken'] = mt_rand();
	}
	use Glass\UserManager;
  use Glass\AddonManager;

  $aid = $_GET['id'];

	$user = UserManager::getCurrent();
	$addonObject = AddonManager::getFromId($aid);
	if($user === false || $addonObject === false || ($addonObject->getManagerBLID() !== $user->getBlid() && !$user->inGroup("Administrator"))) {
		$response = [
			"redirect" => "/index.php"
		];
		return $response;
	}

	if(!isset($_screenshotContext)) {
		$response = [
			"message" => "Internal Error: No Context"
		];
		return $response;
	}

	if(!isset($_POST['submit'])) {
		$response = [
			"message" => "Upload a Screenshot"
		];
		return $response;
	}

	if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
		$response = [
			"message" => "Cross site request forgery attempt blocked"
		];
		return $response;
	}

	if(!isset($_FILES['uploadfile']['name']) || !$_FILES['uploadfile']['size']) {
	//if(!count($_FILES['uploads']['uploadfile'])) {
		$response = [
			"message" => "No file was selected to be uploaded"
		];
		return $response;
	}
	$uploadExt = pathinfo($_FILES['uploadfile']['name'], PATHINFO_EXTENSION);
	$uploadExt = strtolower($uploadExt);

	if($uploadExt != "png" && $uploadExt != "jpg") {
		$response = [
			"message" => "Only .png and .jpg screenshots are allowed"
		];
		return $response;
	}
	use Glass\ScreenshotManager;

	if($_FILES['uploadfile']['size'] > ScreenshotManager::$maxFileSize) {
		$response = [
			"message" => "File too large - The maximum screenshot file size is 3 MB"
		];
		return $response;
	}
	$tempPath = $_FILES['uploadfile']['tmp_name'];

	if($_screenshotContext == "addon") {
		ScreenshotManager::uploadScreenshotForAddon(AddonManager::getFromId($aid), $uploadExt, $tempPath);
	}
	$response = [
		"message" => "idk"
	];
	return $response;
?>
