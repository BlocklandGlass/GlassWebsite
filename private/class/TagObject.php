<?php
//require_once(realpath(dirname(__FILE__) . '/UserHandler.php'));
require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));

class TagObject {
	private static $cacheTime = 600;
	private static $credentialsCacheTime = 60;

	public static function getFromID($id) {
		$tagObject = apc_fetch('tagObject_' . $id);

		if($tagObject === false) {
			$database = new DatabaseManager();
			TagObject::verifyTable($database);
			$resource = $database->query("SELECT * FROM `addon_tags` WHERE `id` = '" . $database->sanitize($id) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}

			if($resource->num_rows == 0) {
				$tagObject = false;
			} else {
				$tagObject = new TagObject($resource->fetch_object());
			}
			$resource->close();
			apc_store('tagObject_' . $id, $tagObject, TagObject::$cacheTime);
		}
		return $tagObject;
	}

	private static function verifyTable($database) {
		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_tags` (
      `id` int(11) NOT NULL,
      `name` varchar(16) NOT NULL,
      `base_color` varchar(6) NOT NULL,
      `icon` text NOT NULL,
      UNIQUE KEY `id` (`id`))")) {
			throw new Exception("Error creating users table: " . $database->error());
		}
	}
}
?>
