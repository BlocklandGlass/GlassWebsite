<?php
require_once dirname(__DIR__) . '/class/UserHandler.php';

class UserManager {
	private static $classname = "UserHandler";
	private static $instances = array();
	private static $instancesByBlid = array();

	public static function getFromId($id) {
		if(isset(UserManager::$instances[$id])) {
			return UserManager::$instances[$id];
		} else {
			$obj = new UserManager::$classname();
			$obj->initFromId($id);
			return UserManager::$instancesByBlid[$obj->getBLID()] = UserManager::$instances[$id] = $obj;
		}
	}

	public static function getFromBLID($blid) {
		if(isset(UserManager::$instancesByBlid[$blid])) {
			return UserManager::$instancesByBlid[$blid];
		} else {
			$obj = new UserManager::$classname();
			$obj->initFromBLID($blid);
			return UserManager::$instancesByBlid[$obj->getBLID()] = UserManager::$instances[$obj->getId()] = $obj;
		}
	}

	public static function getCurrent() {
		if(!isset($_SESSION)) {
			throw new Exception("No Session!");
		}

		if($_SESSION['logged']) {
			return UserManager::getFromId($_SESSION['uid']);
		} else {
			return false;
		}
	}
}
?>
