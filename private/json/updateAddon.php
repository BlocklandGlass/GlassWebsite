<?php
	if(!isset($_SESSION)) {
		session_start();
	}
	//we give the session a unique csrf token so malicious links on other sites cannot take advantage of users
	if(!isset($_SESSION['csrftoken'])) {
		$_SESSION['csrftoken'] = mt_rand();
	}

	require_once(realpath(dirname(__DIR__) . "/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/SemVer.php"));
	require_once(realpath(dirname(__DIR__) . "/class/AddonFileHandler.php"));
	$user = UserManager::getCurrent();

	if($user === false || !isset($_REQUEST['id'])) {
		$response = [
			"redirect" => "/index.php"
		];
		return $response;
	} else {
		$addonObject = AddonManager::getFromId($_REQUEST['id']);
	}

	if(!isset($_POST['submit'])) {
		$response = [
			"message" => "Updating " . $addonObject->getName(),
			"version" => $addonObject->getVersion()
		];
		return $response;
	}

	if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
		$response = [
			"message" => "Cross site request forgery attempt blocked",
			"version" => $addonObject->getVersion()
		];
		return $response;
	}

	if(!isset($_FILES['uploadfile']['name']) || !isset($_FILES['uploadfile']['size']) || !$_FILES['uploadfile']['size']) {
		$response = [
			"message" => "No file was selected to be uploaded",
			"version" => $addonObject->getVersion()
		];
		return $response;
	}
	$uploadExt = pathinfo($_FILES['uploadfile']['name'], PATHINFO_EXTENSION);

	if($uploadExt != "zip") {
		$response = [
			"message" => "Only .zip files are allowed",
			"version" => $addonObject->getVersion()
		];
		return $response;
	}
	require_once(realpath(dirname(__DIR__) . "/class/AddonManager.php"));

	if($_FILES['uploadfile']['size'] > AddonManager::$maxFileSize) {
		$response = [
			"message" => "File too large - The maximum upload file size is 50 MB.  Contact an administrator if you need to upload a larger file.",
			"version" => $addonObject->getVersion()
		];
		return $response;
	}
	$uploadContents = file($_FILES['uploadfile']['tmp_name']);
	$tempPath = $_FILES['uploadfile']['tmp_name'];
	$uploadFileName = basename($_FILES['uploadfile']['name'], ".zip");

	if(isset($_POST['beta'])) {
		if($_POST['beta']) {
			$betaUpload = true;
		} else {
			$betaUpload = false;
		}
	} else {
		$betaUpload = false;
	}

	if(isset($_POST['addonversion']) && $_POST['addonversion'] != "") {
		//trim .bls from end of file name if it exists
		//$uploadBuildName = preg_replace("/\\.bls$/", "", $_POST['buildname']);
		$uploadVersion = $_POST['addonversion'];
		$newVersion = new SemVer($uploadVersion);
		$oldVersion = new SemVer($addonObject->getVersion());

		if(!$newVersion->greaterThan($oldVersion)) {
			$response = [
				"message" => "Version must be increased",
				"version" => $addonObject->getVersion()
			];
			return $response;
		}
		$filename = "update_" . $addonObject->getId() . ".zip";
		$tempLocation = dirname(dirname(__DIR__)) . "/addons/upload/files/" . $filename;
		if(!is_dir(dirname(dirname(__DIR__)) . "/addons/upload/files/")) {
			mkdir(dirname(dirname(__DIR__)) . "/addons/upload/files/");
		}

		//to do: aws stuff instead of this
		move_uploaded_file($tempPath, $tempLocation);
		chmod($tempLocation, 0777);
	}

	if(isset($_POST['changelog'])) {
		$uploadChangelog = $_POST['changelog'];
	} else {
		$uploadChangelog = "";
	}

	if(isset($uploadVersion)) {
		//repeated but slightly different path from above?
		$tempLocation = realpath(dirname(__DIR__) . "/../addons/upload/files/" . $filename);
		if(!$betaUpload) {
			$res = AddonManager::submitUpdate($addonObject, $uploadVersion, $tempLocation, $uploadChangelog);
			if(is_array($res)) {
				return $res;
			}
		} else {
			$res = AddonManager::uploadBetaAddon($addonObject, $uploadVersion, $tempLocation);
		}
		$response = [
			"redirect" => "/addons/review/update.php?id=" . $addonObject->getId(),
		];
		return $response;
	}
	return $response;
?>
