<?php
require_once dirname(__FILE__) . "/BoardObject.php";

//it might be possible to put the requirement inline to avoid unnecessary file system calls
require_once(dirname(__FILE__) . "/DatabaseManager.php");

class BoardManager {
	public static function getFromId($id) {
		//force $id to be an integer
		$id += 0;
		$boardObj = apc_fetch('boardObject_' . $id);

		if($boardObj === false) {
			$boardObj = new BoardObject($id);
			apc_store('boardObject_' . $id);
		}
		return $boardObj;
	}

	public static function getAllBoards() {
		$boardData = BoardManager::getBoardIndexData();
		return $boardData;
	}

	public static function getBoardIndexFromId($id) {
		$id += 0;
		$boardData = BoardManager::getBoardIndexData();

		return $boardData[$id];
	}

	private static function getBoardIndexData() {
		$boardData = apc_fetch('boardIndexData');

		if($boardData === false) {
			$database = new DatabaseManager();

			//I would like to eliminate subcategories if possible
			if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_boards` (
				`id` INT AUTO_INCREMENT,
				`name` VARCHAR(20) NOT NULL,
				`icon` VARCHAR(24) NOT NULL,
				`subCategory` VARCHAR(20) NOT NULL,
				PRIMARY KEY (id))")) {
				throw new Exception("Error attempting to create addon_boards table: " . $database->error());
			}
			$resource = $database->query("SELECT * FROM `addon_boards`");

			if(!$resource) {
				throw new Exception("Error getting data from database: " . $database->error());
			}
			$boardData = array();

			while($row = $resource->fetch_object()) {
				//$boardObj = BoardManager::getFromId($row->id);
				$boardData[$row->id] = array(
					"id" => $row->id,
					"name" => $row->name,
					"icon" => $row->icon,
					"subCategory" => $row->subcategory
				);
			}
			$resource->close();
			apc_store('boardIndexData', $boardData);
		}
		return $boardData;
	}
}
?>
