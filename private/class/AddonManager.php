<?php
namespace Glass;

require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));
require_once(realpath(dirname(__FILE__) . '/AddonObject.php'));
require_once(realpath(dirname(__FILE__) . '/AddonUpdateObject.php'));
require_once(realpath(dirname(__FILE__) . '/AddonFileHandler.php'));
require_once(realpath(dirname(__FILE__) . '/NotificationManager.php'));

use Glass\AWSFileManager;

//this should be the only class to interact with table `addon_addons`
class AddonManager {
	private static $indexCacheTime = 3600;
	private static $objectCacheTime = 3600;
	private static $searchCacheTime = 600;

	public static $maxFileSize = 50000000; //50 mb

	public static $SORTNAMEASC = 0;
	public static $SORTNAMEDESC = 1;
	public static $SORTDOWNLOADASC = 2;
	public static $SORTDOWNLOADDESC = 3;
	public static $SORTRATINGASC = 4; //aka bad ratings first I think
	public static $SORTRATINGDESC = 5;

	public static function submitUpdate($addon, $version, $file, $changelog, $restart) {
		if(!is_object($addon)) {
			$addon = AddonManager::getFromID($addon);
		}

		//remove pre-existing updates, merge changelogs

		$db = new DatabaseManager();
		$ups = AddonManager::getUpdates($addon);
		foreach($ups as $up) {
			if($up->isPending()) {
				return array(
					"message" => "Update already pending. Wait for approval or cancel previous update."
				);
			}
		}

		$db->query("INSERT INTO `addon_updates` (`id`, `aid`, `version`, `tempfile`, `changelog`, `submitted`, `restart`, `approved`) VALUES (NULL, " .
			"'" . $addon->getId() . "'," .
			"'" . $db->sanitize($version) . "'," .
			"'" . $db->sanitize($file) . "'," .
			"'" . $db->sanitize($changelog) . "'," .
			"CURRENT_TIMESTAMP," .
			"b'" . ($up ? 1 : 0) . "'," .
			"NULL);");

		$error = $db->error();

		if($error != "") {
			return array(
				"message"=>"Database error: " . $error
			);
		}

		return array(
			"message"=>"You're being redirected...",
			"redirect"=>"/addons/review/update.php?id=" . $addon->getId()
		);
	}

	public static function uploadBetaAddon($addon, $version, $file) {
		if(!is_object($addon)) {
			$addon = AddonManager::getFromID($addon);
		}

		// TODO check sequential version

		$db = new DatabaseManager();
		$db->query("UPDATE `addon_addons` SET `betaVersion`='" . $db->sanitize($version) . "' WHERE `id`='" . $db->sanitize($addon->getId()) . "'");

		$addon = AddonManager::getFromID($addon->getId());

		AddonFileHandler::injectGlassFile($addon->getId(), $file);
		AddonFileHandler::injectVersionInfo($addon->getId(), 2, $file);
		AWSFileManager::uploadNewAddon($addon->getId(), 2, $addon->getFilename(), $file);

		copy($file, dirname(__DIR__) . '/../addons/files/local/' . $addon->getId() . '_beta.zip');
	}

	public static function uploadNewAddon($user, $boardId, $name, $file, $filename, $description, $summary, $version) {
		$database = new DatabaseManager();
		AddonManager::verifyTable($database);

		//================================
    // Validation
    //================================

		$rsc = $database->query("SELECT * FROM `addon_addons` WHERE `name` = '" . $database->sanitize($name) . "' AND `approved` != '-1' LIMIT 1");

		if($rsc->num_rows > 0) {
			$response = [
				"message" => "An add-on by this name already exists!"
			];
			$rsc->close();
			return $response;
		}
		$rsc->close();

		$rsc = $database->query("SELECT * FROM `addon_addons` WHERE `filename` = '" . $database->sanitize($filename) . "'");
		if($rsc->num_rows > 0) {
			$response = [
				"message" => "An add-on with this filename already exists!"
			];
			$rsc->close();
			return $response;
		}
		$rsc->close();

		//================================
    // Insertion
    //================================

		$res = $database->query("INSERT INTO `addon_addons` (`board`, `blid`, `name`, `filename`, `description`, `summary`, `version`, `deleted`, `approved`, `uploadDate`) VALUES " .
		"(" .
		"'" . $boardId . "'," .
		"'" . $database->sanitize($user->getBlid()) . "'," .
		"'" . $database->sanitize($name) . "'," .
		"'" . $database->sanitize($filename) . "'," .
		"'" . $database->sanitize($description) . "'," .
		"'" . $database->sanitize($summary) . "'," .
		"'" . $database->sanitize($version) . "'," .
		"'0'," .
		"'0'," .
		"CURRENT_TIMESTAMP);");
		if(!$res) {
			$response = [
				"message" => "Database error encountered: " . $database->error()
			];
			return $response;
		}

		$id = $database->fetchMysqli()->insert_id;

		$addon = AddonManager::getFromId($id);

		AddonFileHandler::injectGlassFile($id, $file);
		AddonFileHandler::injectVersionInfo($id, 1, $file);

		AWSFileManager::uploadNewAddon($id, $filename, $file);

		$colorset = AddonFileHandler::getColorset($file);
		if($colorset !== false) {
			$newPath = dirname(dirname(__DIR__)) . '/filebin/temp/colorset.' . $id . '.png';
			if(!file_exists(dirname($newPath))) {
	      mkdir(dirname($newPath), 0777, true);
	    }
			ScreenshotManager::generateColorsetImage($colorset, $newPath);
			ScreenshotManager::uploadScreenshotForAddon($addon, "png", $newPath);
			//unlink($newPath);
		}

		$newPath = dirname(dirname(__DIR__)) . '/filebin/aws_sync/' . $id;

		if(!file_exists(dirname($newPath))) {
      mkdir(dirname($newPath), 0777, true);
    }

		rename($file, $newPath);

		$response = [
			"redirect" => "/addons/upload/screenshots.php?id=" . $id
		];
		return $response;
	}

	public static function approveAddon($id, $board, $approver) {
		$database = new DatabaseManager();
		$database->query("UPDATE `addon_addons` SET `approved`='1', `board`='" . $database->sanitize($board) . "' WHERE `id`='" . $database->sanitize($id) . "'");

		$manager = AddonManager::getFromId($id)->getManagerBLID();

		$params = new \stdClass();
		$params->vars = array();

		$user = new \stdClass();
		$user->type = "user";
		$user->blid = $approver;

		$addon = new \stdClass();
		$addon->type = "addon";
		$addon->id = $id;

		$params->vars[] = $user;
		$params->vars[] = $addon;
		NotificationManager::createNotification($manager, '$2 was approved by $1', $params);

		StatManager::addStatsToAddon($id);
	}

	public static function rejectAddon($id, $reason, $rejecter) {
		$revInf = new \stdClass();
		$revInf->rejected = true;
		$revInf->rejectReason = $reason;

		var_dump($revInf);

		$database = new DatabaseManager();
		$database->query("UPDATE `addon_addons` SET `approved`='-1', `reviewInfo`='" . $database->sanitize(json_encode($revInf)) . "' WHERE `id`='" . $database->sanitize($id) . "'");

		$manager = AddonManager::getFromId($id)->getManagerBLID();

		$params = new \stdClass();
		$params->vars = array();

		$user = new \stdClass();
		$user->type = "user";
		$user->blid = $rejecter;

		$addon = new \stdClass();
		$addon->type = "addon";
		$addon->id = $id;

		$params->vars[] = $user;
		$params->vars[] = $addon;
		NotificationManager::createNotification($manager, '$2 was rejected by $1', $params);
	}

	public static function getFromID($id, $resource = false) {
		if($resource !== false) {
			$addonObject = new AddonObject($resource);
		} else {
			$database = new DatabaseManager();
			AddonManager::verifyTable($database);
			$resource = $database->query("SELECT * FROM `addon_addons` WHERE `id` = '" . $database->sanitize($id) . "'");

			if(!$resource) {
				throw new \Exception("Database error: " . $database->error());
			}

			if($resource->num_rows == 0) {
				$addonObject = false;
			} else {
				$addonObject = new AddonObject($resource->fetch_object());
			}
			$resource->close();
		}
		return $addonObject;
	}

	/**
	 *  $search - contains a number of optional parameters in an array
	 *  	$name - (STRING) string to search for in addon name
	 *  	$blid - (INT) BLID of addon uploader
	 *  	$board - (INT) id of board to search in
	 *  	$offset - (INT) offset for results
	 *  	$limit - (INT) maximum number of results to return, defaults to 10
	 *  	$sort - (INT) a number representing the sorting method, defaults to ORDER BY `name` ASC
	 *
	 */
	public static function searchAddons($search) { //$name = false, $blid = false, $board = false) {
		//Caching this seems difficult and can cause issues with stale data easily
		//oh well whatever
		if(!isset($search['offset'])) {
			$search['offset'] = false;
		}

		if(!isset($search['limit'])) {
			$search['limit'] = false;
		}

		if(!isset($search['sort'])) {
			$search['sort'] = AddonManager::$SORTNAMEASC;
		}
		$cacheString = serialize($search);


		$database = new DatabaseManager();
		AddonManager::verifyTable($database);
		$query = "SELECT * FROM `addon_addons` WHERE ";

		$queries = array();

		if(isset($search['name'])) {
			$queries[] = "`name` LIKE '%" . $database->sanitize($search['name']) . "%'";
		}

		if(isset($search['blid'])) {
			$queries[] = "`blid` = '" . $database->sanitize($search['blid']) . "'";
		}

		if(isset($search['board'])) {
			$queries[] = "`board` = '" . $database->sanitize($search['board']) . "'";
		}

		$deleted = $search['deleted'] ?? 0;
		if($deleted !== false) { //false approved means it doesnt matter
			 $queries[] = "`deleted` = '" . $database->sanitize($deleted) .  "'";
		}

		$approved = $search['approved'] ?? 1;
		if($approved !== false) { //false approved means it doesnt matter
			 $queries[] = "`approved` = '" . $database->sanitize($approved) .  "'";
		}

		foreach($queries as $idx=>$q) {
			$query .= $q;
			if($idx < sizeof($queries)-1) {
				$query .= ' AND ';
			}
		}

		$query .= "ORDER BY ";

		switch($search['sort']) {
			case AddonManager::$SORTNAMEASC:
				$query .= "`name` ASC ";
				break;
			case AddonManager::$SORTNAMEDESC:
				$query .= "`name` DESC ";
				break;
			case AddonManager::$SORTDOWNLOADASC:
				$query .= "(`downloads_web` + `downloads_ingame` + `downloads_update`) ASC ";
				break;
			case AddonManager::$SORTDOWNLOADSDESC:
				$query .= "(`downloads_web` + `downloads_ingame` + `downloads_update`) DESC ";
				break;
			case AddonManager::$SORTRATINGASC:
				$query .= "-rating DESC "; //this forces NULL values to be last
				break;
			case AddonManager::$SORTRATINGDESC:
				$query .= "`rating` ASC ";
				break;
			default:
				$query .= "`name` ASC ";
		}

		if($search['offset'] !== false && $search['limit'] !== false) {
			$query .= "LIMIT " . $database->sanitize(intval($search['offset'])) . ", " . $database->sanitize(intval($search['limit']));
		}
		$resource = $database->query($query);

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}
		$searchAddons = [];

		while($row = $resource->fetch_object()) {
			$searchAddons[] = AddonManager::getFromID($row->id, $row)->getID();
		}
		$resource->close();
		return $searchAddons;
	}

	//bargain should be changed to a board
	//this should probably just call searchAddons()
	public static function getFromBoardID($id, $offset = 0, $limit = 10) {
		//the downside to this is that managing the cache is more difficult
		return AddonManager::searchAddons([
			"board" => $id,
			"offset" => $offset,
			"limit" => $limit,
			"approved" => 1
		]);
	}

	//bargain bin should probably just be a board instead of a flag in the database
//	public static function getBargain() {
//		$ret = array();
//
//		$db = new DatabaseManager();
//		$res = $db->query("SELECT `id` FROM `addon_addons` WHERE bargain=1 AND deleted=0 AND danger=0");
//		while($obj = $res->fetch_object()) {
//			$ret[$obj->id] = AddonManager::getFromId($obj->id);
//		}
//		$res->close();
//		return $ret;
//	}

	//this should probably be a board too
//	public static function getDangerous() {
//		$ret = array();
//
//		$db = new DatabaseManager();
//		$res = $db->query("SELECT `id` FROM `addon_addons` WHERE deleted=0 AND danger=1");
//		while($obj = $res->fetch_object()) {
//			$ret[$obj->id] = AddonManager::getFromId($obj->id);
//		}
//		return $ret;
//	}

	//this function should probably take a blid or aid instead of an object
	//should probably switch from Author to BLID for consistency
	//this should also probably just use searchAddons(0
	public static function getFromBLID($blid, $param) {
		if($param !== null && !is_array($param)) {
			throw new \Exception("Using old AddonManager::getFromBlid!");
		}

		$arr = $param ?? array();
		$search = array_merge($arr, ["blid"=>$blid]);
		return AddonManager::searchAddons($search);
	}

	//from a caching perspective, I already have each board cached, so I would like to avoid duplicate data
	//oh well, this function isn't actually used anyway
	public static function getAll() {
		$ret = array();

		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_addons`");
		while($obj = $res->fetch_object()) {
			$ret[$obj->id] = AddonManager::getFromId($obj->id);
		}
		return $ret;
	}

	public static function getUnapproved() {
		$ret = array();

		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_addons` WHERE `approved`='0'");
		while($obj = $res->fetch_object()) {
			$ret[$obj->id] = AddonManager::getFromId($obj->id);
		}
		return $ret;
	}

	public static function getCountFromBoard($boardID) {
		$database = new DatabaseManager();
		AddonManager::verifyTable($database);
		$resource = $database->query("SELECT COUNT(*) FROM `addon_addons` WHERE board='" . $boardID . "'  AND deleted=0");

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}
		$count = $resource->fetch_row()[0];
		$resource->close();

		return $count;
	}

	public static function clearSearchCache() {

	}

	public static function updateName($addon, $name) {
		if($addon->getName() !== $name) {
			$database = new DatabaseManager();
			AddonManager::verifyTable($database);
			$resource = $database->query("UPDATE `addon_addons` SET `name`='" . $database->sanitize($name) . "' WHERE `id`='" . $database->sanitize($addon->getId()) . "';");

			$res = [
				"message" => "Updated add-on name",
				"addon" => $addon,
				"name" => $name
			];

			return $res;
		}
	}

	public static function updateDescription($addon, $desc) {
		if($addon->getDescription() !== $desc) {
			$database = new DatabaseManager();
			AddonManager::verifyTable($database);
			$resource = $database->query("UPDATE `addon_addons` SET `description`='" . $database->sanitize($desc) . "' WHERE `id`='" . $database->sanitize($addon->getId()) . "';");

			$res = [
				"message" => "Updated description",
				"addon" => $addon,
				"desc" => $desc
			];

			return $res;
		}
	}

	//returns an array of just the ids in order
	//we should really be doing that more instead of caching entire objects in multiple places
	public static function getNewAddons($count = 10) {
		$count += 0;

		$database = new DatabaseManager();
		AddonManager::verifyTable($database);
		$resource = $database->query("SELECT * FROM `addon_addons` WHERE `deleted`=0 AND `approved`=1 ORDER BY `uploadDate` DESC LIMIT " . $database->sanitize($count));

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}
		$newestAddonIDs = [];

		while($row = $resource->fetch_object()) {
			$newestAddonIDs[] = AddonManager::getFromID($row->id, $row)->getID();
		}
		$resource->close();

		return $newestAddonIDs;
	}

	public static function getRecentAddons($time = null) {
		if($time == null) {
			$time = 60*24*7;
		}
		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_addons` WHERE `uploadDate` > now() - INTERVAL " . $db->sanitize($time) . " MINUTE AND `approved`=1 ORDER BY `uploadDate` DESC");
		echo($db->error());
		$arr = array();
		while($obj = $res->fetch_object()) {
			$arr[] = AddonManager::getFromId($obj->id);
		}
		return $arr;
	}

	public static function getRecentUpdates($time = null) {
		if($time == null) {
			$time = 60*24*7;
		}
		$db = new DatabaseManager();
		$res = $db->query("SELECT * FROM `addon_updates` WHERE `submitted` > now() - INTERVAL " . $db->sanitize($time) . " MINUTE AND `approved`=1 ORDER BY `submitted` DESC");
		echo($db->error());
		$arr = array();
		while($obj = $res->fetch_object()) {
			$arr[] = new AddonUpdateObject($obj);
		}
		return $arr;
	}

	public static function getUpdates($addon) {
		$database = new DatabaseManager();
		AddonManager::verifyTable($database);
		$resource = $database->query("SELECT * FROM `addon_updates` WHERE `aid`='" . $database->sanitize($addon->getId()) . "' ORDER BY `submitted` DESC");

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}
		$updates = [];

		while($row = $resource->fetch_object()) {
			$updates[] = new AddonUpdateObject($row);
		}
		$resource->close();

		return $updates;
	}

	public static function getPendingUpdates() {
		$database = new DatabaseManager();
		AddonManager::verifyTable($database);
		$resource = $database->query("SELECT * FROM `addon_updates` WHERE `approved` IS NULL ORDER BY `submitted` DESC");

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}
		$updates = [];

		while($row = $resource->fetch_object()) {
			$updates[] = new AddonUpdateObject($row);
		}
		$resource->close();

		return $updates;
	}

	public static function approveUpdate($update) {
		$database = new DatabaseManager();
		AddonManager::verifyTable($database);

		$id = $update->getId();
		if($update->status !== null) {
			throw new \Exception("Attempted to approve already approved update");
		}

		$update->status = true;

		$database->query("UPDATE `addon_updates` SET `approved` = b'1' WHERE `id` = '" . $database->sanitize($id) . "'");
		$database->query("UPDATE `addon_addons` SET `version` = '" . $database->sanitize($update->version) . "' WHERE `id` = '" . $database->sanitize($update->aid) . "'");

		AddonFileHandler::injectGlassFile($update->aid, $update->getFile());
		AddonFileHandler::injectVersionInfo($update->aid, 1, $update->getFile());
		AWSFileManager::uploadNewAddon($update->aid, 1, $update->getAddon()->getFilename(), $update->getFile());

		$params = new \stdClass();
		$addon = new \stdClass();
		$addon->type = "addon";
		$addon->id = $update->getAddon()->getId();
		$params->vars[] = $addon;
		NotificationManager::createNotification($manager, 'Your update to $1 was approved', $params);
		@unlink($update->getFile());
	}

	public static function submitRating($aid, $blid, $rating) {
		if($rating < 1) {
			$rating = 1;
		}

		if($rating > 5) {
			$rating = 5;
		}

		$rating = ceil($rating);

		$db = new DatabaseManager();
		AddonManager::verifyTable($db);


    $res = $db->query($sq = "SELECT COUNT(*) FROM `addon_ratings` WHERE `blid`='" . $db->sanitize($blid) . "' AND `aid`='" . $db->sanitize($aid) . "'");
    $ret = $res->fetch_row();
    if(!isset($ret[0]) || $ret[0] == 0) {
      $res = $db->query($sq = "INSERT INTO `addon_ratings` (`blid`, `aid`, `rating`) VALUES (
      '" . $db->sanitize($blid) . "',
      '" . $db->sanitize($aid) . "',
      '" . $db->sanitize($rating) . "')");
    } else {
      $db->update("addon_ratings", ["blid"=>$blid, "aid"=>$aid], ["rating"=>$rating]);
    }

		//recalculate total
		$res = $db->query("SELECT * FROM `addon_ratings` WHERE `aid`='" . $db->sanitize($aid) . "'");
		$ratings = array();
		while($obj = $res->fetch_object()) {
			$ratings[] = $obj->rating;
		}

		$avg = array_sum($ratings)/sizeof($ratings);

		$db->update("addon_addons", ["id"=>$aid], ["rating"=>$avg]);

		echo($db->error());

		return $avg;
	}

	public static function deleteAddon($addon) {
		if(!is_object($addon)) {
			$addon = AddonManager::getFromId($addon);
		}

		if($addon === false) {
			return false;
		}

		$db = new DatabaseManager();
		$res = $db->query("UPDATE `addon_addons` SET `deleted`=1 WHERE `id`='" . $db->sanitize($addon->getId()) . "'");
		if($db->error() == null) {
			return true;
		} else {
			return false;
		}
	}

	public static function verifyTable($database) {
		/*TO DO:
			- screenshots
			- approval info should probably be in a different table,
			or actually maybe not I dunno
			- do we really need stable vs testing vs dev?
			- bargain/danger should probably be boards
			- figure out how data is split between addon and file
			- I don't know much about how the file system works, but
			having 'name', 'file', 'filename', and a separate 'addon_files'
			table doesn't seem ideal.
			- Maybe we should just keep track of total downloads instead
			of 3 different columns
			- I think users should just credit people in their descriptions
			instead of having a dedicated authorInfo json object
		*/
		require_once(realpath(dirname(__FILE__) . '/UserManager.php'));
		require_once(realpath(dirname(__FILE__) . '/BoardManager.php'));
		UserManager::verifyTable($database);
		BoardManager::verifyTable($database);

		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_addons` (
			`id` INT NOT NULL AUTO_INCREMENT,
			`board` INT,
			`blid` INT NOT NULL,
			`name` VARCHAR(30) NOT NULL,
			`filename` TEXT NOT NULL,
			`description` TEXT NOT NULL,
			`version` TEXT NOT NULL,

			`reviewInfo` TEXT NOT NULL,
			`repositoryInfo` TEXT NULL DEFAULT NULL,
			`deleted` TINYINT NOT NULL DEFAULT 0,
			`approved` TINYINT NOT NULL DEFAULT 0,
			`betaVersion` TEXT DEFAULT NULL,
			`rating` int(11) NOT NULL DEFAULT 0,
			`uploadDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`type` TEXT NOT NULL,

			`summary` VARCHAR(255) NOT NULL,
			FOREIGN KEY (`board`)
				REFERENCES addon_boards(`id`)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
			PRIMARY KEY (`id`))")) {
			throw new \Exception("Failed to create table addon_addons: " . $database->error());
		}

		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_updates` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
		  `aid` int(11) NOT NULL,
		  `version` text NOT NULL,
		  `tempfile` text NOT NULL,
		  `changelog` text NOT NULL,
		  `submitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `upstream` bit(1) NOT NULL DEFAULT b'0',
		  `restart` bit(1) NOT NULL DEFAULT b'0',
		  `approved` bit(1) DEFAULT NULL,
			FOREIGN KEY (`aid`)
				REFERENCES addon_addons(`id`)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `id` (`id`))")) {
			throw new \Exception("Failed to create table addon_updates: " . $database->error());
		}

		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_ratings` (
		  `aid` int(11) NOT NULL,
			`blid` int(11) NOT NULL,
			`rating` int(11) NOT NULL,
			FOREIGN KEY (`aid`)
				REFERENCES addon_addons(`id`)
				ON UPDATE CASCADE
				ON DELETE CASCADE)")) {
			throw new \Exception("Failed to create table addon_updates: " . $database->error());
		}
	}
}
?>
