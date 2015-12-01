<?php
	if(!isset($_SESSION)) {
		session_start();
	}
	//we give the session a unique csrf token so malicious links on other sites cannot take advantage of users
	if(!isset($_SESSION['csrftoken'])) {
		$_SESSION['csrftoken'] = mt_rand();
	}

	if(!isset($_GET['id'])) {
		$response = [
			"redirect" => "/addons/index.php"
		];
		return $response;
	}
	require_once(realpath(dirname(__DIR__) . "/class/AddonManager.php"));
	$addon = AddonManager::getFromID($_GET['id'] + 0);

	if($addon === false) {
		$response = [
			"redirect" => "/addons/index.php"
		];
		return $response;
	}

	//do we want to do this or just use session info?
	require_once(realpath(dirname(__DIR__) . "/class/UserManager.php"));
	$user = UserManager::getCurrent();

	if($user === false || $addon->getBLID() !== $user->getBLID()) {
		$response = [
			"redirect" => "/addons/index.php"
		];
		return $response;
	}

	//this tells us whether or not the user has just completed an upload and been automatically redirected
	if(isset($_POST['init']) && $_POST['init']) {
		$response = [
			"message" => "Upload Successful!",
			"addon" => $addon,
			"user" => $user
		];
		return $response;
	}

	if(!isset($_POST['submit'])) {
		$response = [
			"message" => "Manage your Addon",
			"addon" => $addon,
			"user" => $user
		];
		return $response;
	}

	if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
		$response = [
			"message" => "Cross site request forgery attempt blocked",
			"addon" => $addon,
			"user" => $user
		];
		return $response;
	}
	$changed = false;

	if(isset($_FILES['screenshots']['name'])  && $_FILES['screenshots']['size']) {
		$tempPath = $_FILES['screenshots']['tmp_name'];
		$check = getimagesize($tempPath);

		if($check === false) {
			$response = [
				"message" => "Invalid image uploaded",
				"addon" => $addon,
				"user" => $user
			];
			return $response;
		}
		$uploadExt = pathinfo($_FILES['screenshots']['name'], PATHINFO_EXTENSION);

		if($uploadExt != "png" && $uploadExt != "jpg") {
			$response = [
				"message" => "Only .png and .jpg screenshots are allowed",
				"addon" => $addon,
				"user" => $user
			];
			return $response;
		}
		require_once(realpath(dirname(__DIR__) . "/class/ScreenshotManager.php"));

		if($_FILES['screenshots']['size'] > ScreenshotManager::$maxFileSize) {
			$response = [
				"message" => "File too large - The maximum Screenshot file size is 3 MB",
				"addon" => $addon,
				"user" => $user
			];
			return $response;
		}
		require_once(realpath(dirname(__DIR__) . "/class/ScreenshotManager.php"));
		ScreenshotManager::uploadScreenshotForAddon($addon, $tempPath);
		$changed = true;
	}

	if(!isset($_POST['addonname']) || !isset($_POST['filename']) || !isset($_POST['description'])) {
		$response = [
			"message" => "Some form elements missing",
			"addon" => $addon,
			"user" => $user
		];
		return $response;
	}
	$subResponse = AddonManager::updateAddon($addon, $_POST['addonname'], $_POST['filename'], $_POST['description']);

	if($subResponse['message'] !== "") {
		$response = [
			"message" => $subResponse['message'],
			"addon" => $addon,
			"user" => $user
		];
		return $response;
	} else {
		if($changed) {
			$response = [
				"message" => "Screenshots Updated",
				"addon" => $addon,
				"user" => $user
			];
			return $response;
		} else {
			$response = [
				"message" => "No changes were made",
				"addon" => $addon,
				"user" => $user
			];
			return $response;
		}
	}
?>
