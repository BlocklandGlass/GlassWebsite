<?php
	use Glass\TagManager;
	$tagIDs = TagManager::getAllTags();
	$tags = [];

	foreach($tagIDS as $tid) {
		$tags[] = TagManager::getFromID($tid);
	}
	return $tags;
?>
