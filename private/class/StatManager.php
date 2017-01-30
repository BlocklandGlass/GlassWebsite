<?php
namespace Glass;

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
			throw new \Exception("Database error: " . $database->error());
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
		} else if($type == "iteration") {
			$sql = "iterationDownloads";
		} else {
			$sql = "webDownloads";
		}

		$db = new DatabaseManager();
		$res = $db->query("SELECT `$sql` FROM `addon_stats` WHERE `aid`=" . $db->sanitize($id));
		if($res->num_rows > 0) {
			$sum = $res->fetch_object()->$sql;
		} else {
			$sum = 0;
		}

		return $sum;
	}

	public static function downloadAddonID($aid, $context = "web", $ip = false) {
		$addon = AddonManager::getFromID($aid);

		if(!$addon) {
			return false;
		}
		return StatManager::downloadAddon($addon, $context, $ip);
	}

	public static function downloadAddon($addon, $context = "web", $ip = false) {
		$database = new DatabaseManager();
		StatManager::verifyTable($database);

		if($ip !== false) {
			if(!StatManager::canDoIncrement($ip, $addon->getId(), $context)) {
				return true;
			}
		}

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
			throw new \Exception("failed to register new download: " . $database->error());
		}
		return true;
	}

	public static function canDoIncrement($ip, $aid, $type) {
		$db = new DatabaseManager();
		StatManager::verifyTable($db);

		$aid  = $db->sanitize($aid );
		$ip   = $db->sanitize($ip  );
		$type = $db->sanitize($type);

		$res = $db->query("SELECT * FROM `addon_download_cache` WHERE `aid`='$aid' AND `ip`='$ip' AND `type`='$type'");
		if($res->num_rows > 0) {
			return false;
		} else {
			$db->query("INSERT INTO `addon_download_cache` (aid, ip, type) VALUES ('$aid', '$ip', '$type')");
			return true;
		}
	}

	public static function getTrendingAddons($count = 10) {
		$count += 0; //force to be an integer

		$database = new DatabaseManager();
		StatManager::verifyTable($database);
		$resource = $database->query("SELECT `aid` FROM `addon_stats` WHERE `aid` != 11 AND `aid` != 193
			ORDER BY `iterationDownloads` DESC LIMIT " . $database->sanitize($count));

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
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
			throw new \Exception("Database Error: " . $database->error());
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

	 /*

	 addon_stats_hist saves hourly snapshots of download counts
	 my intention is that, as time passes, these are reduced to daily, weekly,
	 and eventually monthly

	 theses are meant to be absolute records for later analysis, not differential

	 	*/

	public static function saveHistory() {
		$database = new DatabaseManager();
		StatManager::verifyTable($database);
		$res = $database->query("SELECT * FROM `addon_stats`");

		if($res == false || $res == null)
			return;

		$date = date("Y-m-d H:00:00");

		while($obj = $res->fetch_object()) {
			$userCount = 0;

			$database->query("INSERT INTO `addon_stats_hist` (`date`,`aid`,`webDownloads`,`ingameDownloads`,`updateDownloads`) VALUES ('" .
				$date . "','" .
				$obj->aid . "','" .
				$obj->webDownloads . "','" .
				$obj->ingameDownloads . "','" .
				$obj->updateDownloads . "')");

		}
	}

	public static function getHourlyDownloads($aid, $hours = 24) {
		$database = new DatabaseManager();
		$res = $database->query("SELECT * FROM `addon_stats_hist` WHERE `aid`='" . $aid . "' AND date > DATE_SUB(NOW(), INTERVAL " . $hours . " HOUR)");
		if($res == false || $res == null)
			return [];

		$ret = [];
		while($obj = $res->fetch_object()) {
			$ret[$obj->date] = $obj;
		}
		return $ret;
	}

	public static function getStatistics($aid) {
		$database = new DatabaseManager();
		$res = $database->query("SELECT * FROM `addon_stats` WHERE `aid`='" . $aid . "'");
		if($res == false || $res == null)
			return new \stdClass();

		return $res->fetch_object();
	}

	public static function endIteration() {
		$post = StatManager::createNewsPost();
		NewsManager::publishNews($post);

		$database = new DatabaseManager();
		$database->query("UPDATE `addon_stats` SET `iterationDownloads`=0");
		$database->query("DELETE FROM `addon_download_cache`");
	}

	public static function createNewsPost() {
		$totalDownloads = 0;
		foreach(AddonManager::getAll() as $addon) {
			$totalDownloads += $addon->getDownloads('iteration');
		}

		$msg  = "<font:verdana bold:13>Weekly Top Picks<br><br>";
		$msg .= "<font:verdana:13>We had a total of <font:verdana bold:13>$totalDownloads<font:verdana:13> downloads this week. Below are our top add-ons of the week!";
		$msg .= "<br><br>";

		$ct = 0;
		$top = StatManager::getTrendingAddons();
		foreach($top as $aid) {
			$ct++;
			$addon = AddonManager::getFromId($aid);
			if($addon->getDownloads('iteration') == 0)
				break;
			$msg .= "$ct. <font:verdana:13><a:glass://aid-" . $addon->getID() . ">" . $addon->getName() . "</a> by <font:verdana bold:13>" . $addon->getAuthor()->getName() . "<font:verdana:13> with " . $addon->getDownloads('iteration') . " downloads<br>";
		}

		$msg .= "<br><br><font:verdana bold:13>- <color:9b59b6>GlassBot";

		return utf8_encode($msg);
	}

	public static function verifyTable($database) {
		UserManager::verifyTable($database);
		AddonManager::verifyTable($database);

		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_stats` (
			`aid` INT NOT NULL,

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
			throw new \Exception("Failed to create addon stats table: " . $database->error());
		}

		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_stats_hist` (
			`id` INT NOT NULL AUTO_INCREMENT,
			`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`aid` INT NOT NULL DEFAULT 0,

			`webDownloads` INT NOT NULL DEFAULT 0,
			`ingameDownloads` INT NOT NULL DEFAULT 0,
			`updateDownloads` INT NOT NULL DEFAULT 0,
			FOREIGN KEY (`aid`)
				REFERENCES addon_addons(`id`)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
			PRIMARY KEY (`id`))")) {
			throw new \Exception("Failed to create addon stat history table: " . $database->error());
		}

		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_download_cache` (
			`aid`  INT NOT NULL,
			`ip`   TEXT NOT NULL,
			`type` VARCHAR(16) NOT NULL,
			`date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

			FOREIGN KEY (`aid`)
				REFERENCES addon_addons(`id`)
				ON UPDATE CASCADE
				ON DELETE CASCADE)")) {
			throw new \Exception("Failed to create addon download cache table: " . $database->error());
		}
	}
}
?>
