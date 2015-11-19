<?php
	require_once(realpath(dirname(__DIR__) . "/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/StatManager.php"));
	$addonIDs = StatManager::getTrendingAddons(10);
	$addons = [];

	foreach($addonIDs as $aid) {
		$addon = AddonManager::getFromID($aid);

		if($addon !== false) {
			$addons[] = $addon;
		}
	}
	return $addons;
?>
