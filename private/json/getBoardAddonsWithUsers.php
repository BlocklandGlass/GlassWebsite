<?php
	$addons = include(realpath(dirname(__FILE__) . "/getBoardAddons.php"));
	require_once(realpath(dirname(__DIR__) . "/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/AddonManager.php"));
	$users = [];

	foreach($addons as $addon) {
		if(!isset($users[$addon->blid])) {
			$users[$addon->blid] = UserManager::getFromBLID($addon->blid);
		}
	}
	$response = [
		"addon" => $addon,
		"users" => $users
	];
	return $response;
?>
