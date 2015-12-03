<?php
	if(!isset($_SESSION)) {
		session_start();
	}
	//we give the session a unique csrf token so malicious links on other sites cannot take advantage of users
	if(!isset($_SESSION['csrftoken'])) {
		$_SESSION['csrftoken'] = mt_rand();
	}
	require_once(realpath(dirname(__DIR__) . "/class/UserManager.php"));
	$user = UserManager::getCurrent();

	if($user === false) {
		$response = [
			"redirect" => "/index.php"
		];
		return $response;
	}

	if(!isset($_POST['submit'])) {
		$response = [
			"message" => "Upload a Build"
		];
		return $response;
	}

	if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
		$response = [
			"message" => "Cross site request forgery attempt blocked"
		];
		return $response;
	}

	if(!isset($_FILES['uploadfile']['name']) || !isset($_FILES['uploadfile']['size']) || !$_FILES['uploadfile']['size']) {
		$response = [
			"message" => "No file was selected to be uploaded"
		];
		return $response;
	}
	$uploadExt = pathinfo($_FILES['uploadfile']['name'], PATHINFO_EXTENSION);

	if($uploadExt != "bls") {
		$response = [
			"message" => "Only .bls files are allowed"
		];
		return $response;
	}
	require_once(realpath(dirname(__DIR__) . "/class/BuildManager.php"));

	if($_FILES['uploadfile']['size'] > BuildManager::$maxFileSize) {
		$response = [
			"message" => "File too large - The maximum build file size is 10 MB"
		];
		return $response;
	}
	$uploadContents = file($_FILES['uploadfile']['tmp_name']);
	$tempPath = $_FILES['uploadfile']['tmp_name'];
	$uploadBuildName = basename($_FILES['uploadfile']['name'], ".bls");
	$uploadFileName = basename($_FILES['uploadfile']['name'], ".bls");

	if(isset($_POST['buildname']) && $_POST['buildname'] != "") {
		//trim .bls from end of file name if it exists
		//$uploadBuildName = preg_replace("/\\.bls$/", "", $_POST['buildname']);
		$uploadBuildName = $_POST['buildname'];
	}

	if(isset($_POST['filename']) && $_POST['filename'] != "") {
		//trim .bls from end of file name if it exists
		$uploadFileName = $_POST['filename'];

	}

	if(!preg_match("/\.bls$/", $uploadFileName)) {
		$uploadFileName .= ".bls";
	}

	if(!isset($_POST['description']) && $_POST['description'] != "") {
		$uploadDescription = $_POST['description'];
		$response = BuildManager::uploadBuild($user->getBLID(), $uploadBuildName, $uploadFileName, $uploadContents, $tempPath, $uploadDescription);
	} else {
		$response = BuildManager::uploadBuild($user->getBLID(), $uploadBuildName, $uploadFileName, $uploadContents, $tempPath);
	}
	return $response;
?>
