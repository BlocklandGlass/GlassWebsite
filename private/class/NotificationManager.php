<?php
require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));

class NotificationManager {
	//we can cache this indefinitely since it is not likely to change
	private static $objectCacheTime = 86400; //24 hours

  public static function getFromId($id) {
		$statObject = apc_fetch('notificationObject_' . $id, $success);
    if($success === false) {
      $database = new DatabaseManager();
      NotificationManager::verifyTable($database);
      $resource = $database->query("SELECT * FROM `user_notifications` WHERE id='" . $db->sanitize($id) . "'");

      if(!$resource) {
        throw new Exception("Database error: " . $database->error());
      }

      if($resource->num_rows == 0) {
        $statObject = false;
      }
      $noteObject = new NotificationObject($resource->fetch_object());
      $resource->close();

      apc_store('notificationObject_' . $id, $notificationObject, NotificationManager::$objectCacheTime);
		}
		return $notificationObject;
  }

	public static function verifyTable($database) {
		if(!$database->query("CREATE TABLE IF NOT EXISTS `user_notifications` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `uid` int(11) NOT NULL,
      `date` timestamp NOT NULL,
      `text` text NOT NULL,
      `params` text NOT NULL,
      PRIMARY KEY (`id`)
      )")) {
			throw new Exception("Error creating table: " . $database->error());
		}
	}
}
?>
