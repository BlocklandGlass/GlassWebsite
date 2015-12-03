<?php
	if(!isset($_GET['id'])) {
		$response = [
			"redirect" => "/builds/index.php"
		];
		return $response;
	}
	require_once(realpath(dirname(__DIR__) . "/class/BuildManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/ScreenshotManager.php"));
//	require_once(realpath(dirname(__DIR__) . "/class/TagManager.php"));
//	require_once(realpath(dirname(__DIR__) . "/class/DependencyManager.php"));

	$build = BuildManager::getFromID($_GET['id'] + 0);

	if($build === false) {
		$response = [
			"redirect" => "/builds/index.php"
		];
		return $response;
	}
	$user = UserManager::getFromBLID($build->blid);
//	$tagIDs = $build->getTags();
//	$dependencyIDs = $build->getDependencies();
//	$tags = [];
//	$dependencies = [];
//
//	foreach($tagIDS as $tid) {
//		$tags[] = TagManager::getFromID($tid);
//	}
//
//	foreach($dependencyIDs as $did) {
//		$dependencies[] = DependencyManager::getFromID($did);
//	}

	$screenshotIDs = ScreenshotManager::getScreenshotsFromBuild($build->id);
	$primaryScreenshotID = ScreenshotManager::getBuildPrimaryScreenshot($build->id);
	$screenshots = [];

	foreach($screenshotIDs as $sid) {
		$screenshots[$sid] = ScreenshotManager::getFromID($sid);
	}

	//to do: replace "downloads" with "stats"
	$response = [
		"build" => $build,
		"user" => $user,
		"downloads" => $build->getTotalDownloads(),
		"screenshots" => [
			"data" => $screenshots,
			"primaryid" => $primaryScreenshotID
		]
	];
	return $response;
?>
