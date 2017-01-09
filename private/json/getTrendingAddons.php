<?php
	use Glass\AddonManager;
	use Glass\StatManager;
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
