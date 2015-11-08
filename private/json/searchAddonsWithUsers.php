<?php
	$addons = include(realpath(dirname(__FILE__) . "/searchAddons.php"));
	require_once(realpath(dirname(__DIR__) . "/class/UserManager.php"));
	$users = [];

	foreach($addons as $addon) {
		if(!isset($users[$addon->blid])) {
			$users[$addon->blid] = UserManager::getFromBLID($addon->blid);
		}
	}
	$response = [
		"addons" => $addons,
		"users" => $users
	];
	return $response;
?>
