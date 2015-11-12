<?php
	require_once(realpath(dirname(__DIR__) . "/class/TagManager.php"));
	$response = TagManager::getAllTags();
	return $response;
?>
