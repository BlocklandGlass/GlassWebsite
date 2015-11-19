<?php
require_once("../../private/class/DatabaseManager.php");
require_once("../../private/class/UserManager.php");
require_once("../../private/class/AddonManager.php");
require_once("../../private/class/BoardManager.php");
require_once("../../private/class/TagManager.php");
require_once("../../private/class/GroupManager.php");
require_once("../../private/class/StatManager.php");
require_once("../../private/class/DependencyManager.php");
require_once("../../private/class/CommentManager.php");
require_once("../../private/class/BuildManager.php");
require_once("../../private/class/RatingManager.php");

class TestManager {
	public static function clearDatabase() {
		apc_clear_cache();
		$database = new DatabaseManager();
		$resource = $database->query("SELECT DATABASE()");
		$name = $resource->fetch_row()[0];
		$resource->close();

		//make sure we don't accidentally load dummy data on live database
		//to do: make sure this actually works
		if(strpos($name, "test" === false)) {
			throw new Exception("Database may not be safe to run tests on");
		}

		//addon_addons, addon_boards, addon_tags, addon_tagmap, group_groups, group_usermap, addon_comments, addon_ratings
		if(!$database->query("SET FOREIGN_KEY_CHECKS=0")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("DROP TABLE IF EXISTS addon_tagmap, addon_tags, addon_dependency,
			addon_addons, addon_boards, addon_comments, addon_ratings, addon_stats,
			users, build_builds, build_dependency, build_stats, tag_stats, group_groups, group_usermap, statistics")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("SET FOREIGN_KEY_CHECKS=1")) {
			throw new Exception("Database error: " . $database->error());
		}
		apc_clear_cache();
	}

	public static function loadBasicDummyData() {
		TestManager::clearDatabase();
		$database = new DatabaseManager();
		UserManager::verifyTable($database);
		AddonManager::verifyTable($database);
		BoardManager::verifyTable($database);
		TagManager::verifyTable($database);
		GroupManager::verifyTable($database);
		DependencyManager::verifyTable($database);
		CommentManager::verifyTable($database);
		RatingManager::verifyTable($database);
		BuildManager::verifyTable($database);
		StatManager::verifyTable($database);

		if(!$database->query("INSERT INTO `addon_boards` (name, video, description) VALUES ('General Content', 'general_content_bg', 'Bricks, Events, Sounds, Prints, Environments, and much more!')")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("INSERT INTO `addon_boards` (name, video, description) VALUES ('Minigames', 'minigames_bg', 'Weapons, Vehicles, Gamemodes, and all your gaming needs!')")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("INSERT INTO `addon_boards` (name, video, description) VALUES ('Client Mods', 'client_mods_bg', 'Mods that run on your client.')")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("INSERT INTO `addon_boards` (name, video, description) VALUES ('Bargain Bin', 'bargain_bin_bg', 'A home for \'special\' content.')")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("INSERT INTO `users` (username, blid, password, email, salt, verified) VALUES ('testuser', '4833', '1d8436e97ef95a7a6151f47b909167c77cfe1985ee5500efa8d46cfe825abc59', 'email@email.com', '273eb4', '1')")) {
			throw new Exception("Database error: " . $database->error());
		}

		//the default json types likely need to be reworked
		if(!$database->query("INSERT INTO `addon_addons` (board, blid, name, filename, description, approved, versionInfo, authorInfo, reviewInfo) VALUES ('1', '4833', 'crapy adon', 'sciprt_hax.zip', 'bad addone pls delete', '1', '{}', '[]', '[]')")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("INSERT INTO `addon_tags` (name, base_color, icon) VALUES ('dum tag', 'ff6600', 'brokenimage')")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("INSERT INTO `addon_tagmap` (aid, tid) VALUES ('1', '1')")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("INSERT INTO `group_groups` (leader, name, description, color, icon) VALUES ('4833', 'legion of dumies', 'a group for people who just want to be in a group', '00ff00', 'brokenimage')")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("INSERT INTO `group_usermap` (gid, blid, administrator) VALUES ('1', '4833', '1')")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("INSERT INTO `addon_comments` (blid, aid, comment) VALUES ('4833', '1', 'glorious addon comrade')")) {
			throw new Exception("Database error: " . $database->error());
		}

		if(!$database->query("INSERT INTO `addon_ratings` (blid, aid, rating) VALUES ('4833', '1', '1')")) {
			throw new Exception("Database error: " . $database->error());
		}
	}
}
?>
