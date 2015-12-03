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
			"redirect" => "/builds/index.php"
		];
		return $response;
	}
	require_once(realpath(dirname(__DIR__) . "/class/BuildManager.php"));
	$build = BuildManager::getFromID($_GET['id'] + 0);

	if($build === false) {
		$response = [
			"redirect" => "/builds/index.php"
		];
		return $response;
	}

	//do we want to do this or just use session info?
	require_once(realpath(dirname(__DIR__) . "/class/UserManager.php"));
	$user = UserManager::getCurrent();

	if($user === false || $build->getBLID() !== $user->getBLID()) {
		$response = [
			"redirect" => "/builds/index.php"
		];
		return $response;
	}

	//we don't need the user as it turns out
	//to do: remove
	if(isset($_POST['init']) && $_POST['init']) {
		$response = [
			"message" => "Upload Successful!",
			"build" => $build,
			"user" => $user
		];
		return $response;
	}

	if(!isset($_POST['submit'])) {
		$response = [
			"message" => "Manage your Build",
			"build" => $build,
			"user" => $user
		];
		return $response;
	}

	if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
		$response = [
			"message" => "Cross site request forgery attempt blocked",
			"build" => $build,
			"user" => $user
		];
		return $response;
	}

	if(!isset($_POST['buildname']) || !isset($_POST['description'])) {
		$response = [
			"message" => "Some form elements missing",
			"build" => $build,
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
				"build" => $build,
				"user" => $user
			];
			return $response;
		}
		$uploadExt = pathinfo($_FILES['screenshots']['name'], PATHINFO_EXTENSION);

		if($uploadExt != "png" && $uploadExt != "jpg") {
			$response = [
				"message" => "Only .png and .jpg screenshots are allowed",
				"build" => $build,
				"user" => $user
			];
			return $response;
		}
		require_once(realpath(dirname(__DIR__) . "/class/ScreenshotManager.php"));

		if($_FILES['screenshots']['size'] > ScreenshotManager::$maxFileSize) {
			$response = [
				"message" => "File too large - The maximum Screenshot file size is 3 MB",
				"build" => $build,
				"user" => $user
			];
			return $response;
		}
		require_once(realpath(dirname(__DIR__) . "/class/ScreenshotManager.php"));
		ScreenshotManager::uploadScreenshotForBuild($build, $uploadExt, $tempPath);
		$changed = true;
	}
	$subResponse = BuildManager::updateBuild($build, $_POST['buildname'], $_POST['description']);

	if($subResponse['message'] !== "") {
		$response = [
			"message" => $subResponse['message'],
			"build" => $build,
			"user" => $user
		];
		return $response;
	} else {
		if($changed) {
			$response = [
				"message" => "Screenshots Updated",
				"build" => $build,
				"user" => $user
			];
			return $response;
		} else {
			$response = [
				"message" => "No changes were made",
				"build" => $build,
				"user" => $user
			];
			return $response;
		}
	}
?>
