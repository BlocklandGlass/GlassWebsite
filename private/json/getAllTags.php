<?php
	require_once(realpath(dirname(__DIR__) . "/class/TagManager.php"));
	$tagIDs = TagManager::getAllTags();
	$tags = [];

	foreach($tagIDS as $tid) {
		$tags[] = TagManager::getFromID($tid);
	}
	return $tags;
?>
