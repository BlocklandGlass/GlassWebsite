<?php
	//this page is going to need to be redone to support advanced searches

	if(!isset($_GET['query'])) {
		return [];
	}
	require_once(realpath(dirname(__DIR__) . "/private/class/AddonManager.php"));

	if(isset($_GET['author']) {
		$blid = $_GET['author'] + 0;
		$response = AddonManager::searchAddons($_GET['query'], $blid);
	} else {
		$response = AddonManager::searchAddons($_GET['query']);
	}
	return $response;
}
?>
