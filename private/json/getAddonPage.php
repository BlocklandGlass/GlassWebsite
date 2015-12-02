<?php
	//collects together all the information needed by /addons/addon.php
	if(!isset($_GET['id'])) {
		return false;
	}
	require_once(realpath(dirname(__DIR__) . "/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/TagManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/DependencyManager.php"));

	$addon = AddonManager::getFromID($_GET['id'] + 0);

	if($addon === false) {
		return false;
	}
	$user = UserManager::getFromBLID($addon->blid);
	$tagIDs = $addon->getTags();
	$dependencyIDs = $addon->getDependencies();
	$tags = [];
	$dependencies = [];

	foreach($tagIDS as $tid) {
		$tags[] = TagManager::getFromID($tid);
	}

	foreach($dependencyIDs as $did) {
		$dependencies[] = DependencyManager::getFromID($did);
	}

	//to do: replace "downloads" with "stats"
	$response = [
		"addon" => $addon,
		"user" => $user,
		"tags" => $tags,
		"dependencies" => $dependencies,
		"downloads" => $addon->getTotalDownloads()
	];
	return $response;
?>
