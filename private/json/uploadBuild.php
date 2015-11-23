<?php
	if(!isset($_SESSION)) {
		session_start();
	}
	//we give the session a unique csrf token so malicious links on other sites cannot take advantage of users
	if(!isset($_SESSION['csrftoken'])) {
		$_SESSION['csrftoken'] = rand();
	}
	require_once(realpath(dirname(__DIR__) . "/class/UserManager.php"));
	$user = UserManager::getCurrent();

	if($user === false) {
		$response = [
			"redirect" => "/index.php"
		];
		return $response;
	}

	if(!isset($_POST["submit"])) {
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

	if(!isset($_FILES["uploadfile"]["name"]) || !$_FILES["uploadfile"]["size"]) {
		$response = [
			"message" => "No file was selected to be uploaded"
		];
		return $response;
	}
	require_once(realpath(dirname(__DIR__) . "/class/BuildManager.php"));

	if($_FILES["uploadfile"]["size"] > BuildManager::$maxFileSize) {
		$response = [
			"message" => "File too large - The maximum build file size is 10 MB"
		];
		return $response;
	}
	$name = basename($_FILES["uploadfile"]["name"]); //to do

	//basic parse of .bls file
	//$contents = explode("\n", file_get_contents($_FILES["uploadfile"]["tmp_name"]));
	$contents = file($_FILES["uploadfile"]["tmp_name"]);
	$response = BuildManager::uploadBuild($user->getBLID(), $name, $contents);
	return $response;
?>
