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
}
?>
