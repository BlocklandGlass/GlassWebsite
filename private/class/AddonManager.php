<?php
/**
 * \Glass\AddonManager class definition
 */
namespace Glass;

require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));
require_once(realpath(dirname(__FILE__) . '/AddonObject.php'));
require_once(realpath(dirname(__FILE__) . '/AddonUpdateObject.php'));
require_once(realpath(dirname(__FILE__) . '/AddonFileHandler.php'));
require_once(realpath(dirname(__FILE__) . '/NotificationManager.php'));

use Glass\AWSFileManager;

/**
 * A manager class for AddonObjects
 */
class AddonManager {
	private static $indexCacheTime = 3600;
	private static $objectCacheTime = 3600;
	private static $searchCacheTime = 600;

	public static $maxFileSize = 50000000; //50 mb

	public static $SORTNAMEASC = 0;
	public static $SORTNAMEDESC = 1;
	public static $SORTDOWNLOADASC = 2;
	public static $SORTDOWNLOADDESC = 3;

	/**
 	 * Inserts update information in to the database
	 *
	 * @param int $addon The addon id being updated
	 * @param string $version Version being updated to
	 * @param string $file Location of the updated file
	 * @param string $changelog Update changelog textarea
	 * @param bool $restart Whether the update requires a Blockland restart
	 *
	 * @return void
   */
	public static function submitUpdate($addon, $version, $file, $changelog, $restart) {
		if(!is_object($addon)) {
			$addon = AddonManager::getFromID($addon);
		}

		//remove pre-existing updates, merge changelogs

		$ups = AddonManager::getUpdates($addon);
		foreach($ups as $up) {
			if($up->isPending()) {
				return array(
					"message" => "An update is already pending. Wait for approval by the add-on moderation team or cancel the currently pending update."
				);
			}
		}

    $db = new DatabaseManager();

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

	/**
	 * Cancels pending updates through database deletion
	 *
	 * @param int $id The update id to cancel
	 */
	public static function cancelUpdate($id) {
		$db = new DatabaseManager();
		$id = $db->sanitize($id);
		$db->query("DELETE FROM `addon_updates` WHERE `id`='$id'");
	}

	/**
	 * Marks update as "rejected"
	 *
	 * @param int $id The update id to reject
	 */
	public static function rejectUpdate($id) {
        $update = AddonManager::getUpdate($id);
        $aid = $update->getAddon()->getId();
        AddonManager::sendRejectedUpdateEmail($aid);

		$db = new DatabaseManager();
		$id = $db->sanitize($id);
		$db->query("UPDATE `addon_updates` SET `approved`=b'0' WHERE `id`='$id'");
	}

	/**
	 * Handles the upload of an add-on, including files, screenshot generation, and database updates
	 *
	 * @param UserObject $user Add-on author
	 * @param int $boardId The board selected for the add-on
	 * @param string $name The add-on's name
	 * @param string $file The add-on's file location
	 * @param string $filename The intended filename when downloaded
	 * @param string $description
	 * @param string $summary
	 * @param string $version
	 */
	public static function uploadNewAddon($user, $boardId, $name, $file, $filename, $description, $summary, $version) {
		$database = new DatabaseManager();
		AddonManager::verifyTable($database);

		//================================
    // Validation
    //================================

		$rsc = $database->query("SELECT * FROM `addon_addons` WHERE `name` = '" . $database->sanitize($name) . "' AND `approved` != '-1' AND `deleted` != '1' LIMIT 1");

		if($rsc->num_rows > 0) {
			$response = [
				"message" => "An add-on by this name already exists!"
			];
			$rsc->close();
			return $response;
		}
		$rsc->close();

		$rsc = $database->query("SELECT * FROM `addon_addons` WHERE `filename` = '" . $database->sanitize($filename) . "' AND `deleted` != '1'");
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
			"redirect" => "/addons/upload/screenshots.php?id=" . $id . "&up"
		];
		return $response;
	}

	/**
	 * Marks add-on as approved
	 *
	 * @param int $id The add-on id (aid)
	 * @param int $board Board id to place the add-on in
	 * @param int $approver The blid of the approver
	 *
	 * @return void
	 */
	public static function approveAddon($id, $board, $approver) {
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
    AddonManager::sendAcceptedAddonEmail($id);

		$database = new DatabaseManager();
		$database->query("UPDATE `addon_addons` SET `approved`='1', `board`='" . $database->sanitize($board) . "' WHERE `id`='" . $database->sanitize($id) . "'");

    StatManager::addStatsToAddon($id);
	}

	/**
	 * Rejects a pending add-on
	 *
	 * @param int $id The add-on id (aid)
	 * @param int $reason The reason for rejection (unused)
	 * @param int $rejecter The blid of the user who rejected the add-on
	 *
	 * @return void
	 */
	public static function rejectAddon($id, $reason, $rejecter) {
		$revInf = new \stdClass();
		$revInf->rejected = true;
		$revInf->rejectReason = $reason;

		var_dump($revInf);

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
    AddonManager::sendRejectedAddonEmail($id);

		$database = new DatabaseManager();
		//$database->query("UPDATE `addon_addons` SET `approved`='-1', `reviewInfo`='" . $database->sanitize(json_encode($revInf)) . "' WHERE `id`='" . $database->sanitize($id) . "'");
		$database->query("UPDATE `addon_addons` SET `approved`='-1' WHERE `id`='" . $database->sanitize($id) . "'");
	}

  public static function sendAcceptedAddonEmail($id) {
    $addon = AddonManager::getFromId($id);

    if($addon->getManagerBLID() == false)
      return false;

    $user = UserManager::getFromId($addon->getManagerBLID());

		$body = "Greetings " . $user->getUsername() . ",";
    $body .= "\r\n\r\n";
    $body .= "Your submitted add-on  \"" . $addon->getName() . "\" has been approved by a member of our add-on moderation team.";
    $body .= "\r\n\r\n";
    $body .= "Add-on page: https://blocklandglass.com/addons/addon.php?id=" . $id;
    $body .= "\r\n\r\n";
    $body .= "Regards,";
    $body .= "\r\n";
    $body .= "The BLG Team";

		UserManager::email($user, ("Add-On Approved: " . $addon->getName()), $body);
  }

  public static function sendAcceptedUpdateEmail($id) {
    $addon = AddonManager::getFromId($id);

    if($addon->getManagerBLID() == false)
      return false;

    $user = UserManager::getFromId($addon->getManagerBLID());

		$body = "Greetings " . $user->getUsername() . ",";
    $body .= "\r\n\r\n";
    $body .= "Your submitted add-on update for \"" . $addon->getName() . "\" has been approved by a member of our add-on moderation team.";
    $body .= "\r\n\r\n";
    $body .= "Add-on page: https://blocklandglass.com/addons/addon.php?id=" . $id;
    $body .= "\r\n\r\n";
    $body .= "Regards,";
    $body .= "\r\n";
    $body .= "The BLG Team";

		UserManager::email($user, ("Update Approved: " . $addon->getName()), $body);
  }

  public static function sendRejectedAddonEmail($id) {
    $addon = AddonManager::getFromId($id);

    if($addon->getManagerBLID() == false)
      return false;

    $user = UserManager::getFromId($addon->getManagerBLID());

		$body = "Greetings " . $user->getUsername() . ",";
    $body .= "\r\n\r\n";
    if(!$addon->getApproved()) {
      $body .= "Your submitted add-on \"" . $addon->getName() . "\" has been rejected by a member of our add-on moderation team.";
    } else {
      $body .= "Your previously approved add-on \"" . $addon->getName() . "\" has been changed to rejected by a member of our add-on moderation team.";
    }
    $body .= "\r\n\r\n";
    $body .= "You may be able to find more information about this rejection by visiting your add-on's page below (you must be signed in to view this page):";
    $body .= "\r\n\r\n";
    $body .= "Add-on page: https://blocklandglass.com/addons/addon.php?id=" . $id;
    $body .= "\r\n\r\n";
    $body .= "Regards,";
    $body .= "\r\n";
    $body .= "The BLG Team";

		UserManager::email($user, ("Add-On Rejected: " . $addon->getName()), $body);
  }

  public static function sendRejectedUpdateEmail($id) {
    $addon = AddonManager::getFromId($id);

    if($addon->getManagerBLID() == false)
      return false;

    $user = UserManager::getFromId($addon->getManagerBLID());

		$body = "Greetings " . $user->getUsername() . ",";
    $body .= "\r\n\r\n";
    $body .= "Your submitted add-on update for \"" . $addon->getName() . "\" has been rejected by a member of our add-on moderation team.";
    $body .= "\r\n\r\n";
    $body .= "It is very rare for an add-on update to be rejected without good reason.";
    $body .= "\r\n";
    $body .= "Expect to be contacted soon with more information.";
    $body .= "\r\n\r\n";
    $body .= "Add-on page: https://blocklandglass.com/addons/addon.php?id=" . $id;
    $body .= "\r\n\r\n";
    $body .= "Regards,";
    $body .= "\r\n";
    $body .= "The BLG Team";

		UserManager::email($user, ("Update Rejected: " . $addon->getName()), $body);
  }

	/**
	 * Returns an AddonObject by addon id
	 *
	 * @param int $id Add-on id to query
	 * @param stdClass $resource An object to create the AddonObject from, typically MySQL results
	 *
	 * @return AddonObject
	 */
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
	 * Moves add-on to a different board
	 *
	 * @param int $aid Add-on id
	 * @param int $bid Destination board id
	 *
	 * @return void
	 */
	public static function moveBoard($aid, $bid) {
		$db = new DatabaseManager();
		AddonManager::verifyTable($db);

		$db->query("UPDATE `addon_addons` SET `board`='" . $db->sanitize($bid) . "' WHERE `id`='" . $db->sanitize($aid) . "'");
	}

	/**
	 * Searches add-ons with paramaters defined in $search
	 *
	 * Search options:
	 * ```
	 * $name - (STRING) string to search for in addon name
	 * $blid - (INT) BLID of addon uploader
	 * $board - (INT) id of board to search in
	 * $offset - (INT) offset for results
	 * $limit - (INT) maximum number of results to return, defaults to 10
	 * $sort - (INT) a number representing the sorting method, defaults to ORDER BY name ASC
	 * ```
	 *
	 * @param array $search Seach query
	 *
	 * @return int[]
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


	/**
	 * Returns a list of add-ons from an author with blid $blid
	 *
	 * @param int $blid Author BLID
	 * @param array $param Other search parameters (see the `Glass\AddonManager\search` function)
	 *
	 * @return int[] List of add-on IDs
	 */
	public static function getFromBLID($blid, $param) {
		if($param !== null && !is_array($param)) {
			throw new \Exception("Using old AddonManager::getFromBlid!");
		}

		$arr = $param ?? array();
		$search = array_merge($arr, ["blid"=>$blid]);
		return AddonManager::searchAddons($search);
	}

	/**
	 * Get a list of all AddonObject's, indexed by addon id
	 *
	 * @return int[] List of all AddonObject's indexed by id
	 */
	public static function getAll() {
		$ret = array();

		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_addons`");
		while($obj = $res->fetch_object()) {
			$ret[$obj->id] = AddonManager::getFromId($obj->id);
		}
		return $ret;
	}

	/**
	 * A list of all unapproved add-ons
	 *
	 * @return int[] List of all unapproved AddonObject's indexed by id
	 */
	public static function getUnapproved() {
		$ret = array();

		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_addons` WHERE `approved`='0' AND deleted='0'");
		while($obj = $res->fetch_object()) {
			$ret[$obj->id] = AddonManager::getFromId($obj->id);
		}
		return $ret;
	}

	/**
	 * Gets the number of public add-ons (approved and not deleted)
	 * @return int Number of add-ons
	 */
	public static function getCount()
	{
			$database = new DatabaseManager();
			AddonManager::verifyTable($database);
			$resource = $database->query("SELECT COUNT(*) FROM `addon_addons` WHERE deleted=0 AND approved=1");

			if(!$resource) {
				throw new \Exception("Database error: " . $database->error());
			}
			$count = $resource->fetch_row()[0];
			$resource->close();

			return $count;
	}

	/**
	 * Get the number of add-ons in a board
	 *
	 * @param int $boardID The board id
	 * @return int
	 */
	public static function getCountFromBoard($boardID) {
		$database = new DatabaseManager();
		AddonManager::verifyTable($database);
		$resource = $database->query("SELECT COUNT(*) FROM `addon_addons` WHERE board='" . $boardID . "'  AND deleted=0 AND approved=1");

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}
		$count = $resource->fetch_row()[0];
		$resource->close();

		return $count;
	}



	/**
	 * Gets the number of approved add-on updates
	 * @return int Number of add-ons
	 */
	public static function getUpdateCount()
	{
			$database = new DatabaseManager();
			AddonManager::verifyTable($database);
			$resource = $database->query("SELECT COUNT(*) FROM `addon_updates` WHERE approved=1");

			if(!$resource) {
				throw new \Exception("Database error: " . $database->error());
			}
			$count = $resource->fetch_row()[0];
			$resource->close();

			return $count;
	}

	/**
	 * Gets the number of add-on creators
	 * @return int Number of add-ons
	 */
	public static function getCreatorCount()
	{
			$database = new DatabaseManager();
			AddonManager::verifyTable($database);
			$resource = $database->query("SELECT COUNT(distinct blid) FROM `addon_addons` WHERE approved=1");

			if(!$resource) {
				throw new \Exception("Database error: " . $database->error());
			}
			$count = $resource->fetch_row()[0];
			$resource->close();

			return $count;
	}

	/**
	* Clears the search cache (unimplements)
	*
	* @depreciated
	*/
	public static function clearSearchCache() {

	}

	/**
	 * Update an add-on's name
	 *
	 * @param AddonObject $addon The AddonObject to rename
	 * @param string $name The name to change to
	 *
	 * @return string[] Results of name update
	 */
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

	/**
	 * Update an add-on's description
	 *
	 * @param AddonObject $addon The AddonObject to modify
	 * @param string $description The description to change to
	 *
	 * @return string[] Results of the update
	 */
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

	/**
	 * Update an add-on's summary
	 *
	 * @param AddonObject $addon The AddonObject to modify
	 * @param string $summary The summary to change to
	 *
	 * @return string[] Results of the update
	 */
	public static function updateSummary($addon, $summary) {
    if(strlen($summary) > 150) {
      $summary = substr($summary, 0, 150);
    }

		if($addon->getSummary() !== $summary) {
			$database = new DatabaseManager();
			AddonManager::verifyTable($database);
			$resource = $database->query("UPDATE `addon_addons` SET `summary`='" . $database->sanitize($summary) . "' WHERE `id`='" . $database->sanitize($addon->getId()) . "';");

			$res = [
				"message" => "Updated summary",
				"addon" => $addon,
				"summary" => $summary
			];

			return $res;
		}
	}

	/**
	 * Update an add-on's information
	 *
	 * @param AddonObject $addon The AddonObject to modify
	 * @param string[] $dataArray Key/value pairs to update (corresponding to MySQL columns)
	 *
	 * @return void
	 */
	public static function updateInfo($id, $dataArray) {
		if(sizeof($dataArray) == 0) return;

		$db = new DatabaseManager();
		$id = $db->sanitize($id);

		$sql = "UPDATE `addon_addons` SET ";
		$didFirst = false;
		foreach($dataArray as $key=>$val) {
			$key = $db->sanitize($key);
			$val = $db->sanitize($val);

			if($didFirst) {
				$sql .= " , ";
			} else {
				$didFirst = true;
			}

			$sql .= "`$key`='$val' ";
		}
		$sql .= " WHERE `id`='$id'";

		$db->query($sql);
	}

	/**
	 * Returns `$count` of the latest add-ons
	 *
	 * @param int $count Number of add-ons to return
	 *
	 * @return int[] List of addon ids, sorted newest to oldest
	 */
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

	/**
	 * Returns add-ons made after `$time`
	 *
	 * @param int $time Number of minutes ago to query (default 1 week)
	 *
	 * @return AddonObject[]
	 */
	public static function getRecentAddons($time = 10080) {
		$db = new DatabaseManager();
		$res = $db->query("SELECT `id` FROM `addon_addons` WHERE `uploadDate` > now() - INTERVAL " . $db->sanitize($time) . " MINUTE AND `approved`=1 ORDER BY `uploadDate` DESC");
		echo($db->error());
		$arr = array();
		while($obj = $res->fetch_object()) {
			$arr[] = AddonManager::getFromId($obj->id);
		}
		return $arr;
	}

	/**
	 * Returns add-ons updates after `$time`
	 *
	 * @param int $time Number of minutes ago to query
	 *
	 * @return AddonUpdateObject[]
	 */
	public static function getRecentUpdates($time = 10080) {
		$db = new DatabaseManager();
		$res = $db->query("SELECT * FROM `addon_updates` WHERE `submitted` > now() - INTERVAL " . $db->sanitize($time) . " MINUTE AND `approved`=1 ORDER BY `submitted` DESC");
		echo($db->error());
		$arr = array();
		while($obj = $res->fetch_object()) {
			$arr[] = new AddonUpdateObject($obj);
		}
		return $arr;
	}

	/**
	 * Returns updates corresponding to an addon
	 *
	 * @param AddonObject $addon Addon object to get updates for
	 *
	 * @return AddonUpdateObject[]
	 */
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

	/**
	 * Returns update corresponding to an addon by ID
	 *
	 * @param AddonObject $addon Addon object to get updates for
	 *
	 * @return AddonUpdateObject[]
	 */
	public static function getUpdate($id) {
		$database = new DatabaseManager();
		AddonManager::verifyTable($database);
		$resource = $database->query("SELECT * FROM `addon_updates` WHERE `id`='" . $database->sanitize($id) . "' ORDER BY `submitted` DESC");

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}

		$row = $resource->fetch_object();
        $update = new AddonUpdateObject($row);

		$resource->close();

		return $update;
	}

	/**
	 * Returns all pending updates
	 *
	 * @return AddonUpdateObject[]
	 */
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

	/**
	 * Approve a pending update
	 *
	 * @param AddonUpdateObject $update Update to approve
	 *
	 * @return void
	 */
	public static function approveUpdate($update) {
		$database = new DatabaseManager();
		AddonManager::verifyTable($database);

		$id = $update->getId();
		if($update->status !== null) {
			throw new \Exception("Attempted to approve already approved update");
		}

        $aid = $update->getAddon()->getId();
        AddonManager::sendAcceptedUpdateEmail($aid);

		$update->status = true;

		$database->query("UPDATE `addon_updates` SET `approved` = b'1' WHERE `id` = '" . $database->sanitize($id) . "'");
		$database->query("UPDATE `addon_addons` SET `version` = '" . $database->sanitize($update->version) . "' WHERE `id` = '" . $database->sanitize($update->aid) . "'");

		AddonFileHandler::injectGlassFile($update->aid, $update->getFile());
		AddonFileHandler::injectVersionInfo($update->aid, 1, $update->getFile());
		AWSFileManager::uploadNewAddon($update->aid, $update->getAddon()->getFilename(), $update->getFile());

		$params = new \stdClass();
		$addon = new \stdClass();
		$addon->type = "addon";
		$addon->id = $update->getAddon()->getId();
		$params->vars[] = $addon;
		NotificationManager::createNotification($manager, 'Your update to $1 was approved', $params);

		$filepath = dirname(__DIR__) . '/../filebin/aws_sync/' . $update->getAddon()->getId();

		if(!is_dir(dirname($filepath))) {
			mkdir(dirname($filepath), 0777, true);
		}

		copy($update->getFile(), $filepath);
		@unlink($update->getFile());

	}

	/**
	 * Soft-delete an add-on
	 *
	 * @param int $addon Add-on id to delete
	 *
	 * @return bool
	 */
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

	/**
	 * Get the local file path for an add-on. Downloads the file from AWS if it is not kept locally
	 *
	 * @param int $addon Add-on id to get path for
	 *
	 * @return string Add-on path
	 */
	public static function getLocalFile($addon) {
		if(!is_object($addon)) {
			$addon = AddonManager::getFromId($addon);
		}

		if($addon === false) {
			return false;
		}

		$file = dirname(__DIR__) . '/../filebin/aws_sync/' . $addon->getId();

		if(!is_dir(dirname($file))) {
			mkdir(dirname($file), 0777, true);
		}

		if(!is_file($file)) {
			$path = realpath(dirname($file));

			$fh = fopen($path . '/' . $addon->getId(), 'w');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://" . AWSFileManager::getBucket() . "/addons/" . $addon->getId());
			curl_setopt($ch, CURLOPT_FILE, $fh);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // this will follow redirects
			curl_exec($ch);
			curl_close($ch);
			fclose($fh);
		}

		return realpath($file);
	}

	/**
	 * Creates database tables
	 *
	 * @param DatabaseManager
	 *
	 * @return void
	 */
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
			`uploadDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

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
	}
}
?>
