<?php
	if(!isset($_GET['id'])) {
		return [];
	}
	require_once(realpath(dirname(__DIR__) . "/class/BoardManager.php"));
	$aid = $_GET['id'] + 0; //force it to be a number

	if(isset($_GET['offset']) && isset($_GET['limit'])) {
		$offset = $_GET['offset'] + 0;
		$limit = $_GET['limit'] + 0;
		$response = BoardManager::getAddonsFromBoardID($aid, $offset, $limit);
	} else {
		$response = BoardManager::getAddonsFromBoardID($aid);
	}
	return $response;
?>
