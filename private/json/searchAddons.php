<?php
	require_once(realpath(dirname(__DIR__) . "/class/AddonManager.php"));
	$searchArray = [];

	if(isset($_GET['query'])) {
		$searchArray['name'] = $_GET['query'];
	}

	if(isset($_GET['author'])) {
		$searchArray['blid'] = intval($_GET['author']);
	}

	if(isset($_GET['board'])) {
		$searchArray['board'] = intval($_GET['board']);
	}

	if(isset($_GET['tag'])) {
		$searchArrau['tag'] = $_GET['tag'];
	}

	if(isset($_GET['offset'])) {
		$searchArray['offset'] = intval($_GET['offset']);
	}

	if(isset($_GET['limit'])) {
		$searchArray['limit'] = intval($_GET['limit']);
	}

	if(isset($_GET['sort'])) {
		$searchArray['sort'] = intval($_GET['sort']);
	}
	$response = AddonManager::searchAddons($searchArray);
	return $response;
?>
