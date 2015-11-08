<?php
	require_once(realpath(dirname(__DIR__) . "/class/AddonManager.php"));
	$searchArray = [];

	if(isset($_POST['query'])) {
		$searchArray['name'] = $_POST['query'];
	}

	if(isset($_POST['blid'])) {
		$searchArray['blid'] = intval($_POST['blid']);
	}

	if(isset($_POST['board'])) {
		$searchArray['board'] = intval($_POST['board']);
	}

	if(isset($_POST['tag'])) {
		$searchArrau['tag'] = $_POST['tag'];
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
	$response = AddonManager::searchAddons($searchArray);
	return $response;
?>
