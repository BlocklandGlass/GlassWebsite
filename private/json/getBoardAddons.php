<?php
	//collects together some of the information needed by /addons/board.php
	if(!isset($_GET['id'])) {
		return [];
	}
	use Glass\BoardManager;
	$aid = $_GET['id'] + 0; //force it to be a number

	//may throw exceptions with bad input, should fix
	if(isset($_GET['page'])) {
		$offset = (intval($_GET['page']) - 1)*10;
		$limit = 10;
		$addonIDs = BoardManager::getAddonsFromBoardID($aid, $offset, $limit);
	} elseif(isset($_GET['offset']) && isset($_GET['limit'])) {
		$offset = $_GET['offset'] + 0;
		$limit = $_GET['limit'] + 0;
		$addonIDs = BoardManager::getAddonsFromBoardID($aid, $offset, $limit);
	} else {
		$addonIDs = BoardManager::getAddonsFromBoardID($aid);
	}
	$addons = [];

	foreach($addonIDs as $aid) {
		$addon = AddonManager::getFromID($aid);

		if($addon !== false) {
			$addons[] = $addon;
		}
	}
	return $addons;
?>
