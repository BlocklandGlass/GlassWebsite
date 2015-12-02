<?php
	if(!isset($_GET['id'])) {
		return false;
	}
	require_once(realpath(dirname(__DIR__) . "/class/BuildManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/ScreenshotManager.php"));
//	require_once(realpath(dirname(__DIR__) . "/class/TagManager.php"));
//	require_once(realpath(dirname(__DIR__) . "/class/DependencyManager.php"));

	$build = BuildManager::getFromID($_GET['id'] + 0);

	if($build === false) {
		return false;
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

	//to do: replace "downloads" with "stats"
	$response = [
		"build" => $build,
		"user" => $user,
		"downloads" => $build->getTotalDownloads(),
		"screenshots" => "to do"
	];
	return $response;
?>
