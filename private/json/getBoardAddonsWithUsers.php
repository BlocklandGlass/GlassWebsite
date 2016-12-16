<?php
	$addons = include(realpath(dirname(__FILE__) . "/getBoardAddons.php"));
	use Glass\UserManager;
	use Glass\AddonManager;
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
