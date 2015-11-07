<?php
	if(!isset($_GET['aid'])) {
		return [];
	}
	require_once(realpath(dirname(__DIR__) . "/class/CommentManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/CommentManager.php"));
	$aid = $_GET['aid'] + 0; //force it to be a number
	$comments = CommentManager::getCommentsFromAddon($aid);

	if($comments === false) {
		return [];
	}
	$users = [];

	foreach($comments as $comment) {
		if(!isset($userFetched[$comment->blid])) {
			
?>
