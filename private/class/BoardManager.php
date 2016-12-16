<?php
namespace Glass;

require_once(realpath(dirname(__FILE__) . "/BoardObject.php"));

//it might be possible to put the requirement inline to avoid unnecessary file system calls
require_once(realpath(dirname(__FILE__) . "/DatabaseManager.php"));

/*TO DO:
	System to update cached data
*/

class BoardManager {
	public static function getFromID($id) {
		//force $id to be an integer
		//actually, $id should be filtered on input, not in these classes
		//$id += 0;
		$boardData = BoardManager::getBoardIndexData();

		if(isset($boardData[$id])) {
			return $boardData[$id];
		} else {
			return false;
		}
	}

	public static function getAllBoards() {
		return BoardManager::getBoardIndexData();
	}

	public static function getAddonsFromBoardID($id, $offset, $limit) {
		if(isset($limit)) {
			return AddonManager::getFromBoardID($id, $offset, $limit);
		} else {
			return AddonManager::getFromBoardID($id);
		}
	}

	private static function getBoardIndexData() {
		$database = new DatabaseManager();
		BoardManager::verifyTable($database);
		$resource = $database->query("SELECT * FROM `addon_boards` ORDER BY `name` ASC");

		if(!$resource) {
			throw new \Exception("Error getting data from database: " . $database->error());
		}
		$boardData = array();

		while($row = $resource->fetch_object()) {
			$boardData[$row->id] = new BoardObject($row);
		}
		$resource->close();

		return $boardData;
	}

	public static function getBoardGroups() {
		$database = new DatabaseManager();
		BoardManager::verifyTable($database);

		$resource = $database->query("SELECT DISTINCT `group` FROM `addon_boards` ORDER BY `group` DESC;");

		$boardData = array();

		while($row = $resource->fetch_object()) {
			$boardData[] = $row->group;
		}
		$resource->close();

		return $boardData;
	}

	public static function getGroup($group = "") {
		$database = new DatabaseManager();
		BoardManager::verifyTable($database);

		$resource = $database->query("SELECT * FROM `addon_boards` WHERE `group`='" . $database->sanitize($group)  . "' ORDER BY `name` ASC");

		$boardData = array();

		while($row = $resource->fetch_object()) {
			$boardData[] = new BoardObject($row);
		}
		$resource->close();

		return $boardData;
	}

	public static function verifyTable($database) {
		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_boards` (
			`id` INT AUTO_INCREMENT,
			`group` VARCHAR(20) NOT NULL,
			`name` VARCHAR(20) NOT NULL,
			`icon` VARCHAR(24) NOT NULL,
			`description` VARCHAR(255),
			PRIMARY KEY (`id`))")) {
			throw new \Exception("Error attempting to create addon_boards table: " . $database->error());
		}
	}
}
?>
