<?php
require_once dirname(__FILE__) . '/DatabaseManager.php';
require_once dirname(__FILE__) . '/AddonObject.php';

class AddonManager {
	private static $classname = "AddonObject";
	private static $instances = array();

	public static function getFromId($id) {
		if(isset(AddonManager::$instances[$id])) {
			return AddonManager::$instances[$id];
		} else {
			$obj = new AddonManager::$classname();
			$obj->initFromId($id);
			return AddonManager::$instances[$id] = $obj;
		}
	}

	public static function getUnapproved() {
		$ret = array();
		foreach(AddonManager::getAll() as $addon) {
			if($addon->isDeleted() || $addon->getFile($addon->getLatestBranch())->getMalicious() == 2) {
				continue;
			}

			$info = json_decode($addon->getApprovalInfo());
			if(isset($info->format) && $info->format == 2) {
				if(sizeof($info->reports) < 5) {
					$ret[] = $addon;
				}
			} else if($info == null) {
				$ret[] = $addon;
			}
		}
		return $ret;
	}

	public static function getFromBoardId($id, $bargain = false, $limit = 0, $offset = 0) {
		$ret = array();

		$db = new DatabaseManager();
    if($limit != 0) {
      $res = $db->query("SELECT `id` FROM `addon_addons` WHERE board='" . $db->sanitize($id) . "' AND bargain='" . $bargain . "' AND deleted=0 ORDER BY `name` asc LIMIT $offset, $limit");
    } else {
		  $res = $db->query("SELECT `id` FROM `addon_addons` WHERE board='" . $db->sanitize($id) . "' AND bargain='" . $bargain . "' AND deleted=0 ORDER BY `name` asc");
    }

    while($obj = $res->fetch_object()) {
			$ret[$obj->id] = AddonManager::getFromId($obj->id);
		}
		return $ret;
	}

	public static function getBargain() {
		$ret = array();

		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_addons` WHERE bargain=1 AND deleted=0 AND danger=0");
		while($obj = $res->fetch_object()) {
			$ret[$obj->id] = AddonManager::getFromId($obj->id);
		}
		return $ret;
	}

	public static function getDangerous() {
		$ret = array();

		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_addons` WHERE deleted=0 AND danger=1");
		while($obj = $res->fetch_object()) {
			$ret[$obj->id] = AddonManager::getFromId($obj->id);
		}
		return $ret;
	}

	public static function getFromAuthor($author) {
		$ret = array();

		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_addons` WHERE author='" . $author->getBLID() . "'");
		while($obj = $res->fetch_object()) {
			$ret[$obj->id] = AddonManager::getFromId($obj->id);
		}
		return $ret;
	}

	public static function getAll() {
		$ret = array();

		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_addons`");
		while($obj = $res->fetch_object()) {
			$ret[$obj->id] = AddonManager::getFromId($obj->id);
		}
		return $ret;
	}

	public static function getCountFromBoard($boardID) {		
		$count = apc_fetch('boardData_count_' . $boardID);

		if($count === false) {
			$database = new DatabaseManager();
			AddonManager::verifyTable($database);
			$resource = $database->query("SELECT COUNT(*) FROM `addon_addons` WHERE board='" . $boardID . "'  AND deleted=0");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}
			$count = $resource->fetch_row()[0];
			$resource->close();

			//Cache result for 1 hour
			//Ideally we cache indefinitely and flush the value when it updates
			//But I get the feeling that we may forget and end up with stale values
			apc_store('boardData_count_' . $boardID, $count, 3600);
		}
		return $count;
	}

	private static function verifyTable($database) {
		/*TO DO:
			screenshots
			tags
			approval info should probably be in a different table
			do we really need stable vs testing vs dev?
			bargain/danger should probably be boards
			figure out how data is split between addon and file
		*/
		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_addons` (
			id INT AUTO_INCREMENT,
			board INT NOT NULL,
			author INT NOT NULL,
			name VARCHAR(30) NOT NULL,
			filename TEXT NOT NULL,
			description TEXT NOT NULL DEFAULT '',
			file INT NOT NULL,
			deleted TINYINT NOT NULL DEFAULT 0,
			dependencies TEXT NOT NULL DEFAULT '',
			downloads_web INT NOT NULL DEFAULT 0,
			downloads_ingame INT NOT NULL DEFAULT 0,
			downloads_update INT NOT NULL DEFAULT 0,
			updaterInfo TEXT NOT NULL,
			approvalInfo TEXT NOT NULL,
			PRIMARY KEY (id))")) {
			throw new Exception("Failed to create table addon_addons: " . $database->error());
		}
	}
}
?>
