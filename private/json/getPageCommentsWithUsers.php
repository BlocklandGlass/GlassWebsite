<?php
	$comments = include(realpath(dirname(__FILE__) . "/getPageComments.php"));
	use Glass\UserManager;
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
