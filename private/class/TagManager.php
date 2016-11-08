<?php
require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));
require_once(realpath(dirname(__FILE__) . '/TagObject.php'));
require_once(realpath(dirname(__FILE__) . '/TagMap.php'));
require_once(realpath(dirname(__FILE__) . '/AddonManager.php'));

class TagManager {
	//we can cache this indefinitely since it is not likely to change
	private static $objectCacheTime = 86400; //24 hours

	private static $tagAddonsCacheTime = 3600; //list of addons with tag
	private static $addonTagsCacheTime = 86400; //list of tags on addon

	public static function getFromID($id, $resource = false) {
		$tagObject = apc_fetch('tagObject_' . $id, $success);

		if($success === false) {
			if($resource !== false) {
				$tagObject = new TagObject($resource);
			} else {
				$database = new DatabaseManager();
				TagManager::verifyTable($database);
				$resource = $database->query("SELECT * FROM `addon_tags` WHERE
					`id` = '" . $database->sanitize($id) . "' LIMIT 1");

				if(!$resource) {
					throw new Exception("Database error: " . $database->error());
				}

				if($resource->num_rows == 0) {
					$tagObject = false;
				} else {
					$tagObject = new TagObject($resource->fetch_object());
				}
				$resource->close();
			}
			apc_store('tagObject_' . $id, $tagObject, TagManager::$objectCacheTime);
		}
		//print_r($tagObject);
		//echo("HIT getFromID");
		return $tagObject;
	}

	//returns an array with the ids of the tags on this addon
	public static function getTagsFromAddonID($id) {
		$addonTags = apc_fetch('addonTags_' . $id, $success);

		if($success === false) {
			//echo("GetTagsFromAddonID CACHE MISS");
			$database = new DatabaseManager();
			TagManager::verifyTable($database);
			$resource = $database->query("SELECT `tid` FROM `addon_tagmap` WHERE
				`aid` = '" . $database->sanitize($id) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}
			$addonTags = [];

			while($row = $resource->fetch_object()) {
				//don't get to pass in a resource this time
				$addonTags[] = $row->tid;
			}
			//print_r($addonTags);
			$resource->close();
			apc_store('addonTags_' . $id, $addonTags, TagManager::$addonTagsCacheTime);
		}
		return $addonTags;
	}

	//just returns an array with the ids now
	public static function getAddonsFromTagID($id) {
		$tagAddons = apc_fetch('tagAddons_' . $id, $success);

		if($success === false) {
			$database = new DatabaseManager();
			TagManager::verifyTable($database);
			$resource = $database->query("SELECT `aid` FROM `addon_tagmap` WHERE
				`tid` = '" . $database->sanitize($id) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}
			$tagAddons = [];

			while($row = $resource->fetch_object()) {
				$tagAddons[] = $row->aid;
			}
			$resource->close();
			apc_store('tagAddons_' . $id, $tagAddons, TagManager::$tagAddonsCacheTime); //this cache time is arbitrary
		}
		return $tagAddons;
	}

	//honestly this should not be used
	public static function getAllTags() {
		apc_delete('allTags');
		$tags = apc_fetch('allTags', $success);

		if($success === false) {
			$database = new DatabaseManager();
			TagManager::verifyTable($database);
			$resource = $database->query("SELECT * FROM `addon_tags`");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}
			$tags = [];

			while($row = $resource->fetch_object()) {
				$tags[] = TagManager::getFromID($row->id, $row);
			}
			$resource->close();
			apc_store('allTags', $tags, TagManager::$tagAddonsCacheTime);
		}
		return $tags;
	}

	//modifiers
	//it might be a good idea to require that a tag be created with an addon specified
	//and impose the requirement that tags with no attached addons are deleted
	public static function createTagForAddonID($name, $color, $icon, $aid) {
		$addon = AddonManager::getFromID($aid);

		if($addon === false) {
			return false;
		}
		return TagManager::createTagForAddon($name, $color, $icon, $addon);
	}

	public static function createTagForAddon($name, $color, $icon, $addon) {
		$database = new DatabaseManager();
		TagManager::verifyTable($database);
		$resource = $database->query("SELECT 1 FROM `addon_tags` WHERE
			`name` = '" . $database->sanitize($name) . "' LIMIT 1");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		}

		if($resource->num_rows > 0 ) {
			$resource->close();
			return false;
		}
		$resource->close();

		if(!$database->query("INSERT INTO `addon_tags` (name, base_color, icon) VALUES ('" .
			$database->sanitize($name) . "', '" .
			$database->sanitize($color) . "', '" .
			$database->sanitize($icon) . "')")) {
			throw new Exception("Failed to create new tag: " . $database->error());
		}

		$tag = TagManager::getFromID($database->fetchMysqli()->insert_id);

		if($tag === false) {
			throw new Exception("Newly generated tag not found!");
		}

		if(!TagManager::addTagToAddon($tag, $addon)) {
			throw new Exception("Failed to associate new tag with addon");
		}
		apc_delete('allTags');
		return true;
	}

	public static function addTagIDToAddonID($tid, $aid) {
		//make sure addon exists
		$addon = AddonManager::getFromID($aid);

		if($addon === false) {
			return false;
		}

		//make sure tag exists
		$tag = TagManager::getFromID($tid);

		if($tag === false) {
			return false;
		}
		//call real function
		return TagManager::addTagToAddon($tag, $addon);
	}

	public static function addTagToAddon($tag, $addon) {
		//check if link already exists
		$database = new DatabaseManager();
		TagManager::verifyTable($database);
		$resource = $database->query("SELECT 1 FROM `addon_tagmap` WHERE
			`tid` = '" . $database->sanitize($tag->getID()) . "' AND
			`aid` = '" . $database->sanitize($addon->getID()) . "' LIMIT 1");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		}

		if($resource->num_rows > 0) {
			$resource->close();
			return false;
		}
		$resource->close();

		//now add tag to addon
		if(!$database->query("INSERT INTO `addon_tagmap` (tid, aid) VALUES ('" .
			$database->sanitize($tag->getID()) . "', '" .
			$database->sanitize($addon->getID()) . "')")) {
			throw new Exception("Error adding new tagmap entry: " . $database->error());
		}

		//clear cache
		apc_delete('addonTags_' . $addon->getID());
		apc_delete('tagAddons_' . $tag->getID());
		return true;

		//maybe we need to call a notification function in AddonManager at some point
	}

	public static function removeTagIDFromAddonID($tid, $aid) {
		$addon = AddonManager::getFromID($aid);

		if($addon === false) {
			return false;
		}
		$tag = TagManager::getFromID($tid);

		if($tag === false) {
			return false;
		}
		return TagManager::removeTagFromAddon($tag, $addon);
	}

	public static function removeTagFromAddon($tag, $addon) {
		$database = new DatabaseManager();
		TagManager::verifyTable($database);
		$resource = $database->query("SELECT 1 FROM `addon_tagmap` WHERE
			`tid` = '" . $database->sanitize($tag->getID()) . "' AND
			`aid` = '" . $database->sanitize($addon->getID()) . "' LIMIT 1");

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		}

		if($resource->num_rows == 0) {
			$resource->close();
			return false;
		}
		$resource->close();

		if(!$database->query("DELETE FROM `addon_tagmap` WHERE
			`tid` = '" . $database->sanitize($tag->getID()) . "' AND
			`aid` = '" . $database->sanitize($addon->getID()) . "'")) {
			throw new Exception("Error removing tagmap entry: " . $database->error());
		}

		if(!$tag->getImportant()) {
			//now check to see if there are any other instances of tag
			$resource = $database->query("SELECT 1 FROM `addon_tagmap` WHERE
				`tid` = '" . $database->sanitize($tag->getID()) . "' LIMIT 1");

			if($resource->num_rows == 0) {
				if(!$database->query("DELETE FROM `addon_tags` WHERE
					`id` = '" . $database->sanitize($tag->getID()) . "'")) {
					throw new Exception("Error deleting tag: " . $database->error());
				}
				apc_delete('tagObject_' . $tag->getID());
				apc_delete('allTags');
			}
			$resource->close();
		}
		apc_delete('addonTags_' . $addon->getID());
		apc_delete('tagAddons_' . $tag->getID());
		return true;
	}

	public static function verifyTable($database) {
		AddonManager::verifyTable($database);

		//to do: change addon_tags to something more general so build and stuff can be tagged
		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_tags` (
			`id` INT NOT NULL AUTO_INCREMENT,
			`name` varchar(16) NOT NULL,
			`base_color` varchar(6) NOT NULL,
			`icon` text NOT NULL,
			`important` TINYINT NOT NULL DEFAULT 0,
			KEY (`name`),
			PRIMARY KEY (`id`))")) {
			throw new Exception("Error creating tag table: " . $database->error());
		}

		//this table might not need a primary key
		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_tagmap` (
			`id` INT NOT NULL AUTO_INCREMENT,
			`aid` INT NOT NULL,
			`tid` INT NOT NULL,
			FOREIGN KEY (`aid`)
				REFERENCES addon_addons(id)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
			FOREIGN KEY (`tid`)
				REFERENCES addon_tags(id)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
			PRIMARY KEY (`id`))")) {
			throw new Exception("Error creating tagmap table: " . $database->error());
		}
	}
}
?>
