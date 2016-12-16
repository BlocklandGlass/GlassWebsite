<?php
	require dirname(__DIR__) . '/autoload.php';
	if(!isset($_SESSION)) {
		session_start();
	}
	//we give the session a unique csrf token so malicious links on other sites cannot take advantage of users
	if(!isset($_SESSION['csrftoken'])) {
		$_SESSION['csrftoken'] = mt_rand();
	}
	use Glass\UserManager;
	use Glass\AddonFileHandler;
	$user = UserManager::getCurrent();

	if($user === false) {
		$response = [
			"redirect" => "/index.php"
		];
		return $response;
	}

	if(!isset($_POST['submit'])) {
		$response = [
			"message" => "Upload New Add-On"
		];
		return $response;
	}

	if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
		$response = [
			"message" => "Cross site request forgery attempt blocked"
		];
		return $response;
	}

	if(empty($_FILES['uploadfile']['name'])) {
		$response = [
			"message" => "No file was selected to be uploaded"
		];
		return $response;
	}
	$uploadExt = pathinfo($_FILES['uploadfile']['name'], PATHINFO_EXTENSION);

	if($uploadExt != "zip") {
		$response = [
			"message" => "Only .zip files are allowed"
		];
		return $response;
	}
	use Glass\AddonManager;

	if($_FILES['uploadfile']['size'] > AddonManager::$maxFileSize) {
		$response = [
			"message" => "File too large - The maximum upload file size is 50 MB.  Contact an administrator if you need to upload a larger file."
		];
		return $response;
	}
	$uploadContents = file($_FILES['uploadfile']['tmp_name']);
	$tempPath = $_FILES['uploadfile']['tmp_name'];
	$uploadFileName = basename($_FILES['uploadfile']['name'], ".zip");

	if(isset($_POST['addonname']) && $_POST['addonname'] != "") {
		//trim .bls from end of file name if it exists
		//$uploadBuildName = preg_replace("/\\.bls$/", "", $_POST['buildname']);
		$uploadAddonName = $_POST['addonname'];
	}

	if(isset($_POST['filename']) && $_POST['filename'] != "") {
		//trim .bls from end of file name if it exists
		$uploadFileName = $_POST['filename'];
	}

	if(!preg_match("/\.zip$/", $uploadFileName)) {
		$uploadFileName .= ".zip";
	}

	if(isset($_POST['description'])) {
		$uploadDescription = $_POST['description'];
	}

	//if(isset($_GET['t']) && $_GET['t'] != "") {
  $filename = $user->getBlid() . "_" . time() . ".zip";
  $tempLocation = dirname(dirname(__DIR__)) . "/public/addons/upload/files/" . $filename;
	echo $tempLocation;
  if(!is_dir(dirname(dirname(__DIR__)) . "/public/addons/upload/files/")) {
    mkdir(dirname(dirname(__DIR__)) . "/public/addons/upload/files/");
  }

  //to do: aws stuff instead of this
  move_uploaded_file($tempPath, $tempLocation);
  chmod($tempLocation, 0777);

	$type = "";
  /*
  $type = $_GET['t'];

  //these should probably return an array with something like
  //	'ok' => true/false
  //	'message' => descriptive message
  if($type == "addon" || $type == "client") {
    $valid = AddonFileHandler::validateAddon($tempLocation);
  } else if($type == "other") {
    $valid = AddonFileHandler::validateAddon($tempLocation) || AddonFileHandler::validateColorset($tempLocation);
  } else {
    $valid = false;
  }
  */

  $valid = AddonFileHandler::validateAddon($tempLocation) || AddonFileHandler::validateColorset($tempLocation) || AddonFileHandler::validatePrint($tempLocation);

  if(!$valid) {
    $response = [
      "message" => "Your add-on is missing required files"
    ];
    return $response;
  } else {
    $response = AddonManager::uploadNewAddon($user, $uploadAddonName, $type, $tempLocation, $uploadFileName, $uploadDescription, $type);
    return $response;
  }
	//}
	return $response;
?>
