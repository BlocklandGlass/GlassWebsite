<?php
	//all the information needed by /addons/boards.php
	use Glass\BoardManager;
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
