<?php
	if(!isset($_GET['id'])) {
		return false;
	}
	require_once(realpath(dirname(__DIR__) . "/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/UserManager.php"));

	$addon = AddonManager::getFromID($_GET['id'] + 0);

	if($addon === false) {
		return false;
	}
	$user = UserManager::getFromBLID($addon->blid);
	//my plan was to have tags and dependencies be added direct to the object here
	//but it seems that I cannot simply add keys to an object
	//to do: replace "downloads" with "stats"
	$response = [
		"addon" => $addon,
		"user" => $user,
		"tags" => $addon->getTags(),
		"dependencies" => $addon->getDependencies(),
		"downloads" => $addon->getTotalDownloads()
	];
	return $response;
?>
