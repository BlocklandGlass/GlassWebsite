<?php
require_once dirname(__FILE__) . '/DatabaseManager.php';
require_once dirname(__FILE__) . '/CommentObject.php';

class CommentManager {
	private static $cacheTime = 60;

	public static function getFromID($id, $resource = false) {
		$commentObject = apc_fetch('commentObject_' . $id);

		if($commentObject === false) {
			if($resource !== false) {
				$commentObject = new CommentObject($resource);
			} else {
				$database = new DatabaseManager();
				CommentManager::verifyTable($database);
				$resource = $database->query("SELECT * FROM `addon_comments` WHERE `id` = '" . $database->sanitize($id) . "'");

				if(!$resource) {
					throw new Exception("Database error: " . $database->error());
				}

				if($resource->num_rows == 0) {
					return false;
				}
				$commentObject = new CommentObject($resource->fetch_object());
				$resource->close();
			}
			apc_store('commentObject_' . $id, $commentObject, CommentObject::getCacheTime());
		}
		return $commentObject;
	}

	public static function getCommentsFromBLID($blid) {
		$userComments = apc_fetch('userComments_' . $blid);

		if($userComments === false) {
			$database = new DatabaseManager();
			CommentManager::verifyTable($database);
			$resource = $database->query("SELECT * FROM `addon_comments` WHERE `blid` = '" . $database->sanitize($blid) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}
			$userComments = [];

			while($row = $resource->fetch_object()) {
				$userComments[] = CommentManager::getFromID($row->id, $row);
			}
			$resource->close();
			apc_store('userComments_' . $blid, $userComments, CommentManager::$cacheTime);
		}
		return $userComments;
	}

	public static function getCommentsFromAddon($aid) {
		$addonComments = apc_fetch('addonComments_' . $aid);

		if($addonComments === false) {
			$database = new DatabaseManager();
			CommentManager::verifyTable($database);
			$resource = $database->query("SELECT * FROM `addon_comments` WHERE `aid` = '" . $database->sanitize($aid) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}
			$addonComments = [];

			while($row = $resource->fetch_object()) {
				$addonComments[] = CommentManager::getFromID($row->id, $row);
			}
			$resource->close();
			apc_store('addonComments_' . $aid, $addonComments, CommentManager::$cacheTime);
		}
		return $addonComments;
	}

	private static function verifyTable($database) {
		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_comments` (
			id INT AUTO_INCREMENT,
			blid INT NOT NULL,
			aid INT NOT NULL,
			comment TEXT NOT NULL,
			timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			lastedit TIMESTAMP,
			PRIMARY KEY (id))")) {
			throw new Exception("Unable to create table addon_comments: " . $database->error());
		}
	}
}
?>
