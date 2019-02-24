<?php
	use Glass\AddonManager;
	$searchArray = [];

	if(isset($_POST['query'])) {
		$searchArray['name'] = $_POST['query'];
	} else if(isset($_GET['q'])) {
    $searchArray['name'] = $_GET['q'];
  }

  if(strlen(trim($searchArray['name'])) < 1) {
    return [];
  }

	if(isset($_POST['blid'])) {
		$searchArray['blid'] = intval($_POST['blid']);
	}

	if(isset($_POST['board'])) {
		$searchArray['board'] = intval($_POST['board']);
	}

	if(isset($_POST['offset'])) {
		$searchArray['offset'] = intval($_POST['offset']);
	}

	if(isset($_POST['limit'])) {
		$searchArray['limit'] = intval($_POST['limit']);
	}

	if(isset($_POST['sort'])) {
		$searchArray['sort'] = intval($_POST['sort']);
	}
	$addonIDs = AddonManager::searchAddons($searchArray);
	$addons = [];

	foreach($addonIDs as $aid) {
		$addon = AddonManager::getFromID($aid);

		if($addon !== false) {
			$addons[] = $addon;
		}
	}
	return $addons;
?>
