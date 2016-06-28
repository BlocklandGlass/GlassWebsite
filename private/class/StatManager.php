<?php
require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));
require_once(realpath(dirname(__FILE__) . '/StatObject.php'));
require_once(realpath(dirname(__FILE__) . '/AddonManager.php'));
require_once(realpath(dirname(__FILE__) . '/BuildManager.php'));
require_once(realpath(dirname(__FILE__) . '/TagManager.php'));
require_once(realpath(dirname(__FILE__) . '/GroupManager.php'));

class StatManager {
	private static $previousCacheTime = 86400; //24 hours
	private static $objectCacheTime = 3600; //60 minutes
	private static $trendingCacheTime = 600;

	public static $addonCount = 10;
	public static $tagCount = 5;
	public static $buildCount = 3;

	public static function getFromID($id, $resource = false) {
		$statObject = apc_fetch('statObject_' . $id, $success);

		if($success === false) {
			if($resource !== false) {
				$statObject = new StatObject($resource);
			} else {
				$database = new DatabaseManager();
				StatManager::verifyTable($database);
				$resource = $database->query("SELECT * FROM `statistics` WHERE `id` = '" . $database->sanitize($id) . "'");

				if(!$resource) {
					throw new Exception("Database error: " . $database->error());
				}

				if($resource->num_rows == 0) {
					$statObject = false;
				}
				$statObject = new StatObject($resource->fetch_object());
				$resource->close();
			}
			//cache result for one hour
			apc_store('statObject_' . $id, $statObject, StatManager::$objectCacheTime);
		}
		return $statObject;
	}

	public static function getMasterServerStats($force = false) {
		$stats = apc_fetch('masterServer', $success);

		if($force || $success === false || time()-$stats["time"] > 6000) {
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
			apc_store('masterServer', $stats);
		}

    return $stats;
	}

	public static function getPreviousStatID() {
		$stats = apc_fetch('lastStats', $success);

		if($success === false) {
			$database = new DatabaseManager();
			StatManager::verifyTable($database);
			$resource = $database->query("SELECT * FROM `statistics` ORDER BY `date` ASC LIMIT 1"); //maybe this should be DESC idk im not a scienctist

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}

			if($resource->num_rows == 0) {
				$stats = false;
			} else {
				$row = $resource->fetch_object();
				$stats = StatManager::getFromID($row->id, $row)->getID();
			}
			$resource->close();
			apc_store('lastStats', $stats, StatManager::$previousCacheTime);
		}
		return $stats;
	}

	public static function getTotalAddonDownloads($id) {
		$count = apc_fetch('addonTotalDownloads_' . $id, $success);

		if($success === false) {
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
			apc_store('addonTotalDownloads_' . $id, $count, StatManager::$objectCacheTime);
		}
		return $count;
	}

	public static function getTotalBuildDownloads($id) {
		$count = apc_fetch('buildTotalDownloads_' . $id, $success);

		if($success === false) {
			$database = new DatabaseManager();
			StatManager::verifyTable($database);
			$resource = $database->query("SELECT `totalDownloads` FROM `build_stats` WHERE `bid` = '" . $database->sanitize($id) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}

			if($resource->num_rows == 0) {
				$count = 0;
			} else {
				$count = $resource->fetch_object()->totalDownloads;
			}
			$resource->close();
			apc_store('buildTotalDownloads_' . $id, $count, StatManager::$objectCacheTime);
		}
		return $count;
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

		apc_delete('addonTotalDownloads_' . $addon->getId());

		$tags = TagManager::getTagsFromAddonID($addon->getID());

		if(!empty($tags)) {
			$tagstr = implode(",", $tags);

			if(!$database->query("UPDATE `tag_stats` SET
				`totalDownloads` = `totalDownloads` + 1,
				`iterationDownloads` = `iterationDownloads` + 1
				WHERE `tid` IN (" . $tagstr . ")")) {
				throw new Exception("Database error: " . $database->error());
			}
		}
		return true;
	}

	public static function getTrendingAddons($count = 10) {
		$count += 0; //force to be an integer
		$addons = apc_fetch('trendingAddons_' . $count, $success);

		if($success === false) {
			$database = new DatabaseManager();
			StatManager::verifyTable($database);
			$resource = $database->query("SELECT `aid` FROM `addon_stats`
				ORDER BY `iterationDownloads` DESC LIMIT " . $database->sanitize($count));

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}
			$addons = [];

			while($row = $resource->fetch_object()) {
				//to do: this should create an AddonStatObject for caching purposes
				//$addons[] = AddonManager::getFromID($row->aid)->getID();
				//or not, meh
				$addons[] = $row->aid;
			}
			$resource->close();
			apc_store('trendingAddons_' . $count, $addons, StatManager::$trendingCacheTime);
		}
		return $addons;
	}

	public static function addStatsToBuild($bid) {
		$database = new DatabaseManager();
		StatManager::verifyTable($database);

		if(!$database->query("INSERT INTO `build_stats` (`bid`) VALUES ('" .
			$database->sanitize($bid) . "')")) {
			throw new Exception("Database Error: " . $database->error());
		}
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

	public static function endIteration() {
		$database = new DatabaseManager();
		StatManager::verifyTable($database);

		//gather lifetime counts
		//do we include AND `banned` = 0 ?
		$resource = $database->query("SELECT COUNT(*) FROM `users` WHERE `verified` = 1");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		} else {
			$users = $resource->fetch_row()[0];
			$resource->close();
		}
		$resource = $database->query("SELECT COUNT(*) FROM `addon_addons` WHERE `deleted` = 0");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		} else {
			$addons = $resource->fetch_row()[0];
			$resource->close();
		}
		$resource = $database->query("SELECT SUM(totalDownloads) FROM `addon_stats`");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		} else {
			$downloads = $resource->fetch_row()[0];
			$resource->close();
		}
		$resource = $database->query("SELECT COUNT(*) FROM `group_groups`");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		} else {
			$groups = $resource->fetch_row()[0];
			$resource->close();
		}
		/*$resource = $database->query("SELECT COUNT(*) FROM `build_builds` WHERE `deleted` = 0");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		} else {
			$builds = $resource->fetch_row()[0];
			$resource->close();
		}*/
		$resource = $database->query("SELECT COUNT(*) FROM `addon_tags`");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		} else {
			$tags = $resource->fetch_row()[0];
			$resource->close();
		}

		//gather top addons, tags, and builds
		$resource = $database->query("SELECT `aid`,`iterationDownloads` FROM `addon_stats`
			ORDER BY `iterationDownloads` DESC");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		}
		$topAddonID = [];
		$topAddonDownloads = [];

		while($row = $resource->fetch_object()) {
			$topAddonID[] = $row->aid;
			$topAddonDownloads[] = $row->iterationDownloads;
		}

		$resource->close();

		//construct a query
		$baseQuery = "INSERT INTO `statistics` (`users`, `addons`, `downloads`, `groups`, `comments`, `builds`, `tags`";
		$valQuery = ") VALUES ('" . $database->sanitize($users) . "', '" .
			$database->sanitize($addons) . "', '" .
			$database->sanitize($downloads) . "', '" .
			$database->sanitize($groups) . "', '" .
			$database->sanitize($comments) . "', '" .
			$database->sanitize($builds) . "', '" .
			$database->sanitize($tags) . "'";
		$endQuery = ")";

		$addonBase = "";
		$addonVal = "";
		$tagBase = "";
		$tagVal = "";
		$buildBase = "";
		$buildVal = "";

		for($i=0; $i<StatManager::$addonCount; $i++) {
			$addonBase .= ", `addon" . $i . "`, `addonDownloads" . $i . "`";
			$addonVal .= ", '" . $database->sanitize($topAddon[$i]) . "', '". $database->sanitize($topAddonDownloads[$i]) . "'";
		}

		for($i=0; $i<StatManager::$tagCount; $i++) {
			$tagBase .= ", `tag" . $i . "`, `tagDownloads" . $i . "`";
			$tagVal .= ", '" . $database->sanitize($topTag[$i]) . "', '". $database->sanitize($topTagDownloads[$i]) . "'";
		}

		for($i=0; $i<StatManager::$buildCount; $i++) {
			$buildBase .= ", `build" . $i . "`, `buildDownloads" . $i . "`";
			$buildVal .= ", '" . $database->sanitize($topBuild[$i]) . "', '". $database->sanitize($topBuildDownloads[$i]) . "'";
		}

		//push stats into database
		if(!$database->query($baseQuery . $addonBase . $tagBase . $buildBase . $valQuery . $addonVal . $tagVal . $buildVal . $endQuery)) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("UPDATE `addon_stats` SET `iterationDownloads` = 0")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("UPDATE `tag_stats` SET `iterationDownloads` = 0")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("UPDATE `build_stats` SET `iterationDownloads` = 0")) {
			throw new Exception("Database error: " . $database->error());
		}
		apc_delete('lastStats');
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
		$res = $db->query("SELECT sum(`$sql`) as sum FROM `addon_stats`");
		$sum = $res->fetch_object()->sum;

		return $sum;
	}

	public static function verifyTable($database) {
		if($database->debug()) {
			UserManager::verifyTable($database);
			AddonManager::verifyTable($database);
			TagManager::verifyTable($database);
			BuildManager::verifyTable($database);
			GroupManager::verifyTable($database);

			if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_stats` (
				`aid` INT NOT NULL,
				`rating` FLOAT,
				`totalDownloads` INT NOT NULL DEFAULT 0,
				`iterationDownloads` INT NOT NULL DEFAULT 0,
				`webDownloads` INT NOT NULL DEFAULT 0,
				`ingameDownloads` INT NOT NULL DEFAULT 0,
				`updateDownloads` INT NOT NULL DEFAULT 0,
				KEY (`totalDownloads`),
				KEY (`iterationDownloads`),
				FOREIGN KEY (`aid`)
					REFERENCES addon_addons(`id`)
					ON UPDATE CASCADE
					ON DELETE CASCADE)")) {
				throw new Exception("Failed to create addon stats table: " . $database->error());
			}

			if(!$database->query("CREATE TABLE IF NOT EXISTS `build_stats` (
				`bid` INT NOT NULL,
				`rating` FLOAT,
				`totalDownloads` INT NOT NULL DEFAULT 0,
				`iterationDownloads` INT NOT NULL DEFAULT 0,
				KEY (`totalDownloads`),
				KEY (`iterationDownloads`),
				FOREIGN KEY (`bid`)
					REFERENCES build_builds(`id`)
					ON UPDATE CASCADE
					ON DELETE CASCADE)")) {
				throw new Exception("Failed to create build stats table: " . $database->error());
			}

			if(!$database->query("CREATE TABLE IF NOT EXISTS `tag_stats` (
				`tid` INT NOT NULL,
				`totalDownloads` INT NOT NULL DEFAULT 0,
				`iterationDownloads` INT NOT NULL DEFAULT 0,
				KEY (`totalDownloads`),
				KEY (`iterationDownloads`),
				FOREIGN KEY (`tid`)
					REFERENCES addon_tags(`id`)
					ON UPDATE CASCADE
					ON DELETE CASCADE)")) {
				throw new Exception("Failed to create tag stats table: " . $database->error());
			}

			//includes a lot of foreign keys, not sure if it is a good idea to include them all
			if(!$database->query("CREATE TABLE IF NOT EXISTS `statistics` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`users` INT NOT NULL DEFAULT 0,
				`addons` INT NOT NULL DEFAULT 0,
				`downloads` INT NOT NULL DEFAULT 0,
				`groups` INT NOT NULL DEFAULT 0,
				`comments` INT NOT NULL DEFAULT 0,
				`builds` INT NOT NULL DEFAULT 0,
				`tags` INT NOT NULL DEFAULT 0,
				`addon0` INT NOT NULL,
				`addon1` INT NOT NULL,
				`addon2` INT NOT NULL,
				`addon3` INT NOT NULL,
				`addon4` INT NOT NULL,
				`addon5` INT NOT NULL,
				`addon6` INT NOT NULL,
				`addon7` INT NOT NULL,
				`addon8` INT NOT NULL,
				`addon9` INT NOT NULL,
				`addonDownloads0` INT NOT NULL,
				`addonDownloads1` INT NOT NULL,
				`addonDownloads2` INT NOT NULL,
				`addonDownloads3` INT NOT NULL,
				`addonDownloads4` INT NOT NULL,
				`addonDownloads5` INT NOT NULL,
				`addonDownloads6` INT NOT NULL,
				`addonDownloads7` INT NOT NULL,
				`addonDownloads8` INT NOT NULL,
				`addonDownloads9` INT NOT NULL,
				`tag0` INT NOT NULL,
				`tag1` INT NOT NULL,
				`tag2` INT NOT NULL,
				`tag3` INT NOT NULL,
				`tag4` INT NOT NULL,
				`tagDownloads0` INT NOT NULL,
				`tagDownloads1` INT NOT NULL,
				`tagDownloads2` INT NOT NULL,
				`tagDownloads3` INT NOT NULL,
				`tagDownloads4` INT NOT NULL,
				`build0` INT NOT NULL,
				`build1` INT NOT NULL,
				`build2` INT NOT NULL,
				`buildDownloads0` INT NOT NULL,
				`buildDownloads1` INT NOT NULL,
				`buildDownloads2` INT NOT NULL,
				KEY (`date`),
				PRIMARY KEY (`id`))")) {
				throw new Exception("Failed to create stat history table: " . $database->error());
			}
		}
	}
}
?>
