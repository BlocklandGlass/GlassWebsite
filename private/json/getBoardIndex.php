<?php
	//all the information needed by /addons/boards.php
	require_once(realpath(dirname(__DIR__) . "/class/BoardManager.php"));
	$boards = BoardManager::getAllBoards();

	//usort($boards, function($a, $b) {
	//	return strcmp($a->getName(), $b->getName());
	//});
	//$response = [];
    //
	//foreach($boards as $board) {
	//	$response[$board->getSubCategory()][] = $board;
	//}
	//return $response;
	return $boards;
?>
