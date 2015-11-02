<?php
require_once dirname(__FILE__) . "/DatabaseManager.php";
require_once dirname(__FILE__) . "/BoardObject.php";

class BoardManager {
	private static $classname = "BoardObject";
	private static $instances = array();

	public static function getFromId($id) {
		if(isset(BoardManager::$instances[$id]) && is_object(BoardManager::$instances[$id])) {
			return BoardManager::$instances[$id];
		} else {
			return BoardManager::$instances[$id] = new BoardManager::$classname($id);
		}
	}

	public static function getAllBoards() {
		$ret = array();

		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_boards`");

		if(!$res) {
			throw new Exception("Error getting data from database: " . $db->error());
		}

		while($obj = $res->fetch_object()) {
			$ret[$obj->id] = BoardManager::getFromId($obj->id);
		}
		//improves performance with simultaneous connections
		$res->close();
		return $ret;
	}
}
?>
