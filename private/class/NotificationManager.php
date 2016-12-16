<?php
namespace Glass;

require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));
require_once(realpath(dirname(__FILE__) . '/UserManager.php'));

class NotificationManager {
	private static $objectCacheTime = 3600; //1 hour
	private static $userCacheTime = 3600;

	private static $noteServerHost = "blocklandglass.com";
	private static $noteServerPort = "27001";

	public static function createNotification($user, $text, $params) {
		if(isset($param) && !is_object($param)) {
			throw new Exception("Object expected form \$param");
		}

		if(is_object($user)) {
			$blid = $user->getBLID();
		} else {
			$blid = $user;
		}

		$database = new DatabaseManager();
		NotificationManager::verifyTable($database);
		$resource = $database->query("INSERT INTO `blocklandglass2`.`user_notifications` (`id`, `blid`, `date`, `text`, `params`, `seen`) VALUES " .
			"(NULL, '" . $database->sanitize($blid) . "', NOW(), '" . $database->sanitize($text) . "', '" . $database->sanitize(json_encode($params)) . "', '0');");
	}

	public static function sendPushNotification($blid, $title, $body, $image, $action, $duration, $sticky = false) {
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_connect($socket, gethostbyname(NotificationManager::$noteServerHost), NotificationManager::$noteServerPort);
		socket_strerror(socket_last_error($socket));

		$data = new stdClass();
		$data->type = "notification";

		$data->target = $blid;
		$data->title = $title;
		$data->text = $body;
		$data->image = $image;
		$data->callback = $action;
		$data->duration = $duration;

		socket_write($socket, json_encode($data));
		socket_close($socket);
	}

	public static function getFromID($id, $resource = false) {

		if($resource !== false) {
			$notificationObject = new NotificationObject($resource);
		} else {
			$database = new DatabaseManager();
			NotificationManager::verifyTable($database);
			$resource = $database->query("SELECT * FROM `user_notifications` WHERE id='" . $database->sanitize($id) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}

			if($resource->num_rows == 0) {
				$notificationObject = false;
			}
			$notificationObject = new NotificationObject($resource->fetch_object());
			$resource->close();
		}

		return $notificationObject;
	}

	public static function getFromBLID($blid, $offset, $limit) {

		$database = new DatabaseManager();
		NotificationManager::verifyTable($database);
		$resource = $database->query("SELECT * FROM `user_notifications` WHERE
			`blid` = '" . $database->sanitize($blid) . "'
			ORDER BY `date` DESC
			LIMIT " . $database->sanitize($offset) . ", " . $database->sanitize($limit));

		if(!$resource) {
			throw new Exception("Database error: " . $database->error());
		}
		$userNotes = [];

		while($row = $resource->fetch_object()) {
			$userNotes[] = $row->id;
		}
		$resource->close();

		return $userNotes;
	}

	public static function verifyTable($database) {
		UserManager::verifyTable($database);

		if(!$database->query("CREATE TABLE IF NOT EXISTS `user_notifications` (
			`id` INT NOT NULL AUTO_INCREMENT,
			`blid` INT NOT NULL,
			`date` timestamp NOT NULL,
			`text` text NOT NULL,
			`params` text NOT NULL,
			`seen` TINYINT NOT NULL DEFAULT 0,
			FOREIGN KEY (`blid`)
				REFERENCES users(`blid`)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
			PRIMARY KEY (`id`))")) {
			throw new Exception("Error creating table: " . $database->error());
		}
	}
}
?>
