<?php
namespace Glass;

require_once dirname(__FILE__) . '/DatabaseManager.php';
require_once dirname(__FILE__) . '/RatingObject.php';

class RatingManager {
	private static $addonCacheTime = 180;
	private static $userCacheTime = 180;
	private static $objectCacheTime = 3600;

	public static function getFromID($id, $resource = false) {

		if($resource !== false) {
			$ratingObject = new RatingObject($resource);
		} else {
			$database = new DatabaseManager();
			RatingManager::verifyTable($database);
			$resource = $database->query("SELECT * FROM `addon_ratings` WHERE `id` = '" . $database->sanitize($id) . "'");

			if(!$resource) {
				throw new \Exception("Database error: " . $database->error());
			}

			if($resource->num_rows == 0) {
				$ratingObject = false;
			}
			$ratingObject = new RatingObject($resource->fetch_object());
			$resource->close();
		}
		return $ratingObject;
	}

	public static function getRatingsFromBLID($blid) {

		$database = new DatabaseManager();
		RatingManager::verifyTable($database);
		$resource = $database->query("SELECT * FROM `addon_ratings` WHERE `blid` = '" . $database->sanitize($blid) . "'");

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}
		$userRatings = [];

		while($row = $resource->fetch_object()) {
			$userRatings[] = RatingManager::getFromID($row->id, $row)->getID();
		}
		$resource->close();

		return $userRatings;
	}

	public static function getRatingsFromAddon($aid) {

		$database = new DatabaseManager();
		RatingManager::verifyTable($database);
		$resource = $database->query("SELECT * FROM `addon_ratings` WHERE `aid` = '" . $database->sanitize($aid) . "'");

		if(!$resource) {
			throw new \Exception("Database error: " . $database->error());
		}
		$addonRatings = [];

		while($row = $resource->fetch_object()) {
			$addonRatings[] = RatingManager::getFromID($row->id, $row)->getID();
		}
		$resource->close();

		return $addonRatings;
	}

	public static function verifyTable($database) {
		require_once(realpath(dirname(__FILE__) . '/UserManager.php'));
		require_once(realpath(dirname(__FILE__) . '/AddonManager.php'));
		UserManager::verifyTable($database);
		AddonManager::verifyTable($database);

		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_ratings` (
			`id` INT AUTO_INCREMENT,
			`blid` INT NOT NULL,
			`aid` INT NOT NULL,
			`rating` TINYINT NOT NULL,
			FOREIGN KEY (`blid`)
				REFERENCES users(`blid`)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
			FOREIGN KEY (`aid`)
				REFERENCES addon_addons(`id`)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
			PRIMARY KEY (`id`))")) {
			throw new \Exception("Unable to create table addon_ratings: " . $database->error());
		}
	}
}
?>
