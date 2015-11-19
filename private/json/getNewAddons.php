<?php
	require_once(realpath(dirname(__DIR__) . "/class/AddonManager.php"));
	$addonIDs = AddonManager::getNewAddons(10);
	$addons = [];

	foreach($addonIDs as $aid) {
		$addon = AddonManager::getFromID($aid);

		if($addon !== false) {
			$addons[] = $addon;
		}
	}
	return $addons;
?>
