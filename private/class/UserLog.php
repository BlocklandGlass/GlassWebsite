<?php
namespace Glass;
use Glass\DatabaseManager;

//this should be merged with the UserManager class
class UserLog {
	//only cache for 60 seconds
	//in this case the only cache hits will be for people who mistype their password
	private static $cacheTime = 60;

	public static function getRecentlyActive($min = 10) {
		$db = new DatabaseManager();
		UserLog::verifyTable($db);
		$res = $db->query("SELECT * FROM `user_log` WHERE `lastseen` > now() - INTERVAL 10 MINUTE");
		$ret = array();
		while($obj = $res->fetch_object()) {
			$ret[] = $obj;
		}
		return $ret;
	}

	public static function getUniqueCount() {
		$db = new DatabaseManager();
		$res = $db->query("select count(distinct blid) as total from `user_log`");
		return $res->fetch_object()->total;
	}

	public static function getHistory($blid) {
		$db = new DatabaseManager();
	  $res = $db->query("SELECT * FROM `user_log_changes` WHERE `blid`='" . $db->sanitize($blid) . "' ORDER BY `date` DESC");
		$ret = array();
		while($obj = $res->fetch_object()) {
			$ret[] = $obj;
		}
		return $ret;
	}

  public static function getCurrentUsername($blid) {
    $db = new DatabaseManager();
    UserLog::verifyTable($db);

    $resouce = $db->query("SELECT * FROM `user_log` WHERE `blid`='" . $db->sanitize($blid) . "'");

    if($resouce->num_rows > 0) {
      $result = $resouce->fetch_object();
      return $result->username;
    } else {
      return false;
    }
  }
	
  public static function addEntry($blid, $username, $ip = null) {
    if($ip != null) {
      if(!UserLog::isRemoteVerified($blid, $username, $ip)) {
        return "auth.blockland.us verification failed";
      }
    }

    $db = new DatabaseManager();
    UserLog::verifyTable($db);

		$_username = $db->sanitize($username);
		$_blid     = $db->sanitize($blid);

		$resource = $db->query("SELECT * FROM `user_log` WHERE `blid`='$_blid'");

		if($resource->num_rows == 0) {

      $db->query("INSERT INTO `user_log` (`blid`, `firstseen`, `lastseen`, `username`) VALUES ('$_blid', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '$_username');");
			$db->query("INSERT INTO `user_log_changes` (`blid`, `username`) VALUES ('$_blid', '$_username')");

		} else {

			$obj = $resource->fetch_object();
      $db->query("UPDATE `user_log` SET `lastseen` = CURRENT_TIMESTAMP, `username`='$_username' WHERE `blid`='$_blid'");

			if($obj->username != $username) {
				$db->query("INSERT INTO `user_log_changes` (`blid`, `username`) VALUES ('$_blid', '$_username')");
			}
		}

		//update username
		if($user = UserManager::getFromBLID($blid)) {
			if($username != $user->getUsername()) {
				$user->setUsername($username);
			}
		}
  }

  public function isRemoteVerified($blid, $name, $ip) {
		$res = BlocklandAuthenticate($name, $ip);
		if($res !== false) {
			return $res == $blid;
		} else {
			return false;
		}
  }

	public function alterDatabase() {
    $db = new DatabaseManager();
    UserLog::verifyTable($db);

		$res = $db->query("SELECT distinct(blid) FROM `user_log`");

		echo $db->error();

		$blids = [];
		while($obj = $res->fetch_object()) {
			$blids[] = $obj->blid;
		}

		echo(sizeof($blids) . " distinct blid's\n\n");

		foreach($blids as $blid) {
			$blid = $db->sanitize($blid);
			$result = $db->query("SELECT * FROM `user_log` WHERE `blid`='$blid' ORDER BY `lastseen` DESC");
			$obj = $result->fetch_object();
			$lastseen = $obj->lastseen;
			$db->query("DELETE FROM `user_log` WHERE `blid`='$blid' AND `lastseen` != '$lastseen'");
			echo $db->error();
		}

		$db->query("ALTER TABLE `user_log` ADD UNIQUE (blid)");
		echo $db->error();
	}

	private static function verifyTable($database) {
		//log of all users seen
		if(!$database->query("CREATE TABLE IF NOT EXISTS `user_log` (
      `blid` int(11) NOT NULL,
      `firstseen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `lastseen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `username` varchar(64) NOT NULL,
			UNIQUE KEY (`blid`)
      )")) {
			throw new \Exception("Error creating users table: " . $database->error());
		}

		//log of username changes
		if(!$database->query("CREATE TABLE IF NOT EXISTS `user_log_changes` (
      `blid` int(11) NOT NULL,
      `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `username` varchar(64) NOT NULL
      )")) {
			throw new \Exception("Error creating users table: " . $database->error());
		}
	}
}

function BlocklandAuthenticate($username, $ip = false) {
  if($ip === false) {
    $ip = $_SERVER['REMOTE_ADDR'];
  }

	$username = mb_convert_encoding(urldecode($username), "ISO-8859-1");
	$username = str_replace("%", "%25", $username);
	$encodeChars = array(" ", "@", "$", "&", "?", "=", "+", ":", ",", "/");
	$encodeValues = array("%20", "%40", "%24", "%26", "%3F", "%3D", "%2B", "3A","%2C", "%2F");
	$username = str_replace($encodeChars, $encodeValues, $username);

	$postData = "NAME=${username}&IP=${ip}";

	$opts = array('http' => array('method' => 'POST', 'header' => "Connection: keep-alive\r\nUser-Agent: Blockland-r1986\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: ". strlen($postData) . "\r\n", 'content' => $postData));

	$context  = stream_context_create($opts);
	$result = file_get_contents('http://auth.blockland.us/authQuery.php', false, $context);
	$parsedResult = explode(' ', trim($result));

	if($parsedResult[0] == "NO")
		return false;

	else if(!is_numeric($parsedResult[1]))
	{
		print($result);
		return false;
	}

	else
		return intval($parsedResult[1]);
}
?>
