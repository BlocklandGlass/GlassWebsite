<?php
	$addons = include(realpath(dirname(__FILE__) . "/getTrendingAddons.php"));
	use Glass\UserManager;
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
	//to do: add stats
	return $response;
?>
