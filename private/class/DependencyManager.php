<?php
require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));
require_once(realpath(dirname(__FILE__) . '/DependencyObject.php'));
require_once(realpath(dirname(__FILE__) . '/AddonManager.php'));

//maybe should be integrated with AddonManager
class DependencyManager {
	//we can cache this indefinitely since it is not likely to change
	private static $objectCacheTime = 86400; //24 hours
	private static $addonCacheTime = 180;

	public static function getFromID($id, $resource = false) {

		if($resource !== false) {
			$depObject = new DependencyObject($resource);
		} else {
			$database = new DatabaseManager();
			DependencyManager::verifyTable($database);
			$resource = $database->query("SELECT * FROM `addon_dependencies` WHERE `id` = '" . $database->sanitize($id) . "' LIMIT 1");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}

			if($resource->num_rows == 0) {
				$depObject = false;
			} else {
				$depObject = new DependencyObject($resource->fetch_object());
			}
			$resource->close();
		}

		return $depObject;
	}

	//I don't think we care about the reverse direction, but it would be trivial to implement
	public static function getDependenciesFromAddonID($id) {

		$database = new DatabaseManager();
		DependencyManager::verifyTable($database);
		$resource = $database->query("SELECT * FROM `addon_dependencies` WHERE `target` = '" . $database->sanitize($id) . "'");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		}
		$addonDeps = [];

		while($row = $resource->fetch_object()) {
			$addonDeps[] = DependencyManager::getFromID($row->id, $row)->getID();
		}
		$resource->close();

		return $addonDeps;
	}

	public static function addDependencyByID($targetID, $requiredID) {
		$target = AddonManager::getFromID($targetID);

		if($target === false) {
			return false;
		}
		$required = AddonManager::getFromID($requiredID);

		if($required === false) {
			return false;
		}
		DependencyManager::addDependencyByAddon($target, $required);
	}

	public static function addDependencyByAddon($target, $required) {
		$database = new DatabaseManager();
		DependencyManager::verifyTable($database);
		$resource = $database->query("SELECT * FROM `addon_dependencies` WHERE `target` = '" . $database->sanitize($target->getID()) . "' AND `requirement` = '" . $database->sanitize($required->getID()) . "' LIMIT 0,1");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		}

		if($resource->num_rows > 0) {
			$resource->close();
			return false;
		}
		$resource->close();

		if(!$database->query("INSERT INTO `addon_dependencies` (target, requirement) VALUES ('" . $database->sanitize($target->getID()) . "', '" . $database->sanitize($required->getID()) . "')")) {
			throw new Exception("Error adding new dependency entry: " . $database->error());
		}
	}

	public static function removeDependencyByAddonID($targetID, $requiredID) {
		$target = AddonManager::getFromID($targetID);

		if($target === false) {
			return false;
		}
		$required = AddonManager::getFromID($requiredID);

		if($required === false) {
			return false;
		}
		DependencyManager::removeDependencyByAddon($target, $required);
	}

	public static function removeDependencyByAddon($target, $required) {
		$database = new DatabaseManager();
		DependencyManager::verifyTable($database);
		$resource = $database->query("SELECT `id` FROM `addon_dependencies` WHERE `target` = '" . $database->sanitize($target->getID()) . "' AND `requirement` = '" . $database->sanitize($required->getID()) . "' LIMIT 1");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		}

		if($resource->num_rows == 0) {
			$resource->close();
			return false;
		}
		$id = $resource->fetch_object()->id;
		$resource->close();

		//if(!$database->query("DELETE FROM `addon_dependencies` WHERE `target` = '" . $database->sanitize($target->getID()) . "' AND `requirement` = '" . $database->sanitize($required->getID()) . "'")) {
		if(!$database->query("DELETE FROM `addon_dependencies` WHERE `id` = '" . $database->sanitize($id) . "'")) {
			throw new Exception("Error removing dependency entry: " . $database->error());
		}
	}

	public static function verifyTable($database) {
		AddonManager::verifyTable($database);

		//this table might not need a primary key
		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_dependencies` (
			`id` INT NOT NULL AUTO_INCREMENT,
			`target` INT NOT NULL,
			`requirement` INT NOT NULL,
			FOREIGN KEY (`target`)
				REFERENCES addon_addons(id)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
			FOREIGN KEY (`requirement`)
				REFERENCES addon_addons(id)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
			PRIMARY KEY (`id`))")) {
			throw new Exception("Error creating dependency table: " . $database->error());
		}
	}
}
?>
