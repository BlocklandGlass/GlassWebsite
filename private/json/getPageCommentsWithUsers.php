<?php
	if(!isset($_GET['aid'])) {
		return [];
	}
	require_once(realpath(dirname(__DIR__) . "/class/CommentManager.php"));
	require_once(realpath(dirname(__DIR__) . "/class/CommentManager.php"));
	$aid = $_GET['aid'] + 0; //force it to be a number
	$comments = CommentManager::getCommentsFromAddon($aid);
	$users = [];

	foreach($comments as $comment) {
		if(!isset($users[$comment->blid])) {
			$users[$comment->blid] = UserManager::getFromBLID($comment->blid);
		}
	}
	$response = [
		"comments" => $comments,
		"users" => $users
	];
	return $response;
?>
