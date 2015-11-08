<?php
	$comments = include(realpath(dirname(__FILE__) . "/getPageComments.php"));
	require_once(realpath(dirname(__DIR__) . "/class/UserManager.php"));

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
