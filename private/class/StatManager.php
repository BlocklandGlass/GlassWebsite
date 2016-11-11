<?php
require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));
require_once(realpath(dirname(__FILE__) . '/StatObject.php'));
require_once(realpath(dirname(__FILE__) . '/AddonManager.php'));
require_once(realpath(dirname(__FILE__) . '/BuildManager.php'));
require_once(realpath(dirname(__FILE__) . '/GroupManager.php'));

class StatManager {
	public static function getMasterServerStats($force = false) {
		$url = 'http://master2.blockland.us/';
		$result = file_get_contents($url, false);
	  if($result === false) {
	    return [0, 0];
	  }
	  $entries = explode("\n", $result);
	  $users = 0;
	  $servers = 0;
	  foreach($entries as $entry) {
	    $field = explode("\t", $entry);
	    if($field[0] == "FIELDS") {
	      continue;
	    }
	    if(isset($field[5])) {
	      $users += $field[5];
	      $servers += 1;
	    }
	  }
		$stats = ["users" => $users, "servers" => $servers, "time" => time()];

    return $stats;
	}

	public static function getTotalAddonDownloads($id) {

		$database = new DatabaseManager();
		StatManager::verifyTable($database);
		$resource = $database->query("SELECT `totalDownloads` FROM `addon_stats` WHERE `aid` = '" . $database->sanitize($id) . "'");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		}

		if($resource->num_rows == 0) {
			$count = 0;
		} else {
			$count = $resource->fetch_object()->totalDownloads;
		}
		$resource->close();

		return $count;
	}

	public static function getAddonDownloads($id, $type) {
		if($type == "ingame") {
			$sql = "ingameDownloads";
		} else if($type == "update" || $type == "updates") {
			$sql = "updateDownloads";
		} else {
			$sql = "webDownloads";
		}

		$db = new DatabaseManager();
		$res = $db->query("SELECT `$sql` FROM `addon_stats` WHERE `aid`=" . $db->sanitize($id));
		$sum = $res->fetch_object()->$sql;

		return $sum;
	}

	public static function downloadAddonID($aid, $context = "web") {
		$addon = AddonManager::getFromID($aid);

		if(!$addon) {
			return false;
		}
		return StatManager::downloadAddon($addon, $context);
	}

	public static function downloadAddon($addon, $context = "web") {
		$database = new DatabaseManager();
		StatManager::verifyTable($database);

		if($context == "web") {
			$sql = "webDownloads";
		} else if($context == "ingame") {
			$sql = "ingameDownloads";
		} else if($context == "update") {
			$sql = "updateDownloads";
		}

		if(!$database->query("UPDATE `addon_stats` SET
			`totalDownloads` = (`totalDownloads` + 1),
			`iterationDownloads` = (`iterationDownloads` + 1),
			`$sql` = (`$sql` + 1)
			WHERE `aid` = '" . $addon->getID() . "'")) {
			throw new Exception("failed to register new download: " . $database->error());
		}
		return true;
	}

	public static function getTrendingAddons($count = 10) {
		$count += 0; //force to be an integer

		$database = new DatabaseManager();
		StatManager::verifyTable($database);
		$resource = $database->query("SELECT `aid` FROM `addon_stats`
			ORDER BY `iterationDownloads` DESC LIMIT " . $database->sanitize($count));

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		}

		$addons = [];

		while($row = $resource->fetch_object()) {
			$addons[] = $row->aid;
		}
		$resource->close();

		return $addons;
	}

	public static function addStatsToAddon($aid) {
		$database = new DatabaseManager();
		StatManager::verifyTable($database);

		$addon = AddonManager::getFromID($aid);

		if(!$addon->getApproved()) {
			return; //only create for approved add-ons
		}

		if(!$database->query("INSERT INTO `addon_stats` (`aid`) VALUES ('" .
			$database->sanitize($aid) . "')")) {
			throw new Exception("Database Error: " . $database->error());
		}
	}

	public static function getAllAddonDownloads($type) {
		if($type == "ingame") {
			$sql = "ingameDownloads";
		} else if($type == "update" || $type == "updates") {
			$sql = "updateDownloads";
		} else {
			$sql = "webDownloads";
		}

		$db = new DatabaseManager();
		StatManager::verifyTable($db);
		$res = $db->query("SELECT sum(`$sql`) as sum FROM `addon_stats`");
		$sum = $res->fetch_object()->sum;

		return $sum;
	}

	public static function verifyTable($database) {
		UserManager::verifyTable($database);
		AddonManager::verifyTable($database);

		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_stats` (
			`aid` INT NOT NULL,
			`rating` FLOAT,

			`totalDownloads` INT NOT NULL DEFAULT 0,

			`iterationDownloads` INT NOT NULL DEFAULT 0,

			`webDownloads` INT NOT NULL DEFAULT 0,
			`ingameDownloads` INT NOT NULL DEFAULT 0,
			`updateDownloads` INT NOT NULL DEFAULT 0,

			KEY (`totalDownloads`),
			FOREIGN KEY (`aid`)
				REFERENCES addon_addons(`id`)
				ON UPDATE CASCADE
				ON DELETE CASCADE)")) {
			throw new Exception("Failed to create addon stats table: " . $database->error());
		}

		//includes a lot of foreign keys, not sure if it is a good idea to include them all
		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_stats_hist` (
			`id` INT NOT NULL AUTO_INCREMENT,
			`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`aid` INT NOT NULL DEFAULT 0,

			`webDownloads` INT NOT NULL DEFAULT 0,
			`ingameDownloads` INT NOT NULL DEFAULT 0,
			`updateDownloads` INT NOT NULL DEFAULT 0,

			`commentCount` INT NOT NULL DEFAULT 0,

			`userCount` INT NOT NULL DEFAULT 0,
			FOREIGN KEY (`aid`)
				REFERENCES addon_addons(`id`)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
			PRIMARY KEY (`id`))")) {
			throw new Exception("Failed to create addon stat history table: " . $database->error());
		}
	}
}
?>
