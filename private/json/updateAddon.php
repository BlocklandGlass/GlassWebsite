<?php
function codeToMessage($code) {
	switch ($code) {
		case UPLOAD_ERR_INI_SIZE:
			$message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
			break;
		case UPLOAD_ERR_PARTIAL:
			$message = "The uploaded file was only partially uploaded";
			break;
		case UPLOAD_ERR_NO_FILE:
			$message = "No file was uploaded";
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$message = "Missing a temporary folder";
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$message = "Failed to write file to disk";
			break;
		case UPLOAD_ERR_EXTENSION:
			$message = "File upload stopped by extension";
			break;

		default:
			$message = "Unknown upload error: " . $code;
			break;
	}
	return $message;
}

	if(!isset($_SESSION)) {
		session_start();
	}
	//we give the session a unique csrf token so malicious links on other sites cannot take advantage of users
	if(!isset($_SESSION['csrftoken'])) {
		$_SESSION['csrftoken'] = mt_rand();
	}

	use Glass\AddonManager;
	use Glass\UserManager;
	use Glass\SemVer;
	use Glass\AddonFileHandler;

  if(!isset($_REQUEST['id'])) {
		$response = [
			"redirect" => "/index.php"
		];
		return $response;
  } else {
    $addonObject = AddonManager::getFromId($_REQUEST['id']);
  }

  $user = UserManager::getCurrent();

  if($user === false || ($addonObject->getManagerBLID() !== $user->getBLID() && !$user->inGroup("Administrator"))) {
		$response = [
			"redirect" => "/index.php"
		];
		return $response;
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

	if($_FILES['uploadfile']['error'] !== UPLOAD_ERR_OK) {
		$response = [
			"message" => "Upload error: " . codeToMessage($_FILES['uploadfile']['error']),
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
		$filename = "update." . $addonObject->getId() . "." . time() . ".zip";
		$tempLocation = dirname(dirname(__DIR__)) . "/filebin/upload/" . $filename;
		if(!is_dir(dirname($tempLocation))) {
			mkdir(dirname($tempLocation));
		}

		//to do: aws stuff instead of this
		$res = move_uploaded_file($tempPath, $tempLocation);
		if($res) {
			chmod($tempLocation, 0777);
		} else {
			$response = [
				"message"=> "Error moving uploaded file; please contact an administrator",
				"version" => $addonObject->getVersion()
			];
		}
	} else {
		$response = [
			"message"=> "Invalid version",
			"version" => $addonObject->getVersion()
		];
	}

	if(isset($_POST['changelog'])) {
		$uploadChangelog = $_POST['changelog'];
	} else {
		$uploadChangelog = "";
	}

	if(isset($uploadVersion)) {
		$restart = isset($_REQUEST['restart']);
		return AddonManager::submitUpdate($addonObject, $uploadVersion, $tempLocation, $uploadChangelog, $restart);
	}
	return $response;
?>
