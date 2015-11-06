<?php
require_once dirname(__FILE__) . '/DatabaseManager.php';
require_once dirname(__FILE__) . '/AddonObject.php';

//this should be the only class to interact with table `addon_addons`
class AddonManager {
	//private static $classname = "AddonObject";
	//private static $instances = array();
	private static $cacheTime = 3600;

	public static function getFromId($id, $resource) {
		$addonObject = apc_fetch('addonObject_' . $id);

		if($addonObject === false)
		{
			if(isset($resource)) {
				$addonObject = new AddonObject($resource);
				apc_store('addonObject_' . $id, $addonObject, AddonManager::$cacheTime);
			} else {
				$database = new DatabaseManager();
				AddonManager::verifyTable($database);
				$resource = $database->query("SELECT * FROM `addon_addons` WHERE `id` = '" . $database->sanitize($id) . "' AND DELETED = 0");

				if(!$resource) {
					throw new Exception("Database error: " . $database->error());
				}

				if($resource->num_rows == 0) {
					return false;
				}
				$addonObject = new AddonObject($resource[0]);
				$resource->close();
			}
			//cache result for one hour
			apc_store('addonObject_' . $id, $addonObject, AddonManager::$cacheTime);
		}
		return $addonObject;
	}

	//	if(isset(AddonManager::$instances[$id])) {
	//		return AddonManager::$instances[$id];
	//	} else {
	//		$obj = new AddonManager::$classname();
	//		$obj->initFromId($id);
	//		return AddonManager::$instances[$id] = $obj;
	//	}
	//}

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
		$boardAddons = apc_fetch('boardAddons_' . $id)

		if($boardAddons === false) {
			$boardAddons = array();

			$database = new DatabaseManager();
			AddonManager::verifyTable($database);
			$query = "SELECT * FROM `addon_addons` WHERE board='" . $database->sanitize($id) . "' AND deleted=0 ORDER BY `name` ASC";

			if($limit > 0) {
				$query .= " LIMIT " . $database->sanitize($offset) . ", " . $database->sanitize($limit);
			}
			$resource = $database->query($query);

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}

			//if($limit != 0) {
			//	$res = $db->query("SELECT `id` FROM `addon_addons` WHERE board='" . $db->sanitize($id) . "' AND bargain='" . $bargain . "' AND deleted=0 ORDER BY `name` asc LIMIT $offset, $limit");
			//} else {
			//	$res = $db->query("SELECT `id` FROM `addon_addons` WHERE board='" . $db->sanitize($id) . "' AND bargain='" . $bargain . "' AND deleted=0 ORDER BY `name` asc");
			//}

			while($row = $resource->fetch_object()) {
				$boardAddons[$obj->id] = AddonManager::getFromId($row->id, $row);
			}
			$resource->close();
		}
		return $boardAddons;
	}

	//bargain bin should probably just be a board instead of a flag in the database
	public static function getBargain() {
		$ret = array();

		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_addons` WHERE bargain=1 AND deleted=0 AND danger=0");
		while($obj = $res->fetch_object()) {
			$ret[$obj->id] = AddonManager::getFromId($obj->id);
		}
		$res->close();
		return $ret;
	}

	//this should probably be a board too
	public static function getDangerous() {
		$ret = array();

		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_addons` WHERE deleted=0 AND danger=1");
		while($obj = $res->fetch_object()) {
			$ret[$obj->id] = AddonManager::getFromId($obj->id);
		}
		return $ret;
	}

	//this function should probably take a blid or aid instead of an object
	public static function getFromAuthor($blid) {
		$authorAddons = apc_fetch('authorAddons_' . $blid);

		if($authorAddons === false) {
			$authorAddons = array();
			$database = new DatabaseManager();
			AddonManager::verifyTable($database);
			$resource = $database->query("SELECT * FROM `addon_addons` WHERE author='" . database->sanitize($blid) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}

			while($row = $resource->fetch_object()) {
				$authorAddons[$row->id] = AddonManager::getFromId($row->id, $row);
			}
			$resource->close();
			apc_store('authorAddons_' . $blid, $authorAddons, AddonManager::$cacheTime);
		}
		return $authorAddons;
	}
	//	$ret = array();
    //
	//	$db = new DatabaseManager();
	//	//$res = $db->query("SELECT `id` FROM `addon_addons` WHERE author='" . database->sanitize($blid) . "'");
	//	while($obj = $res->fetch_object()) {
	//		$ret[$obj->id] = AddonManager::getFromId($obj->id);
	//	}
	//	return $ret;
	//}

	//from a caching perspective, I already have each board cached, so I would like to avoid duplicate data
	//oh well, this function isn't actually used anyway
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
			apc_store('boardData_count_' . $boardID, $count, AddonManager::$cacheTime);
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
