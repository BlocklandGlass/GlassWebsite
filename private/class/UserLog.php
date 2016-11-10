<?php
require_once(realpath(dirname(__FILE__) . "/DatabaseManager.php"));

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
	  $res = $db->query("SELECT * FROM `user_log` WHERE `blid`='" . $db->sanitize($blid) . "' ORDER BY `lastseen` DESC");
		$ret = array();
		while($obj = $res->fetch_object()) {
			$ret[] = $obj;
		}
		return $ret;
	}

  public static function getCurrentUsername($blid) {
    $db = new DatabaseManager();
    UserLog::verifyTable($db);

    $resouce = $db->query("SELECT * FROM `user_log` WHERE `blid`='" . $db->sanitize($blid) . "' ORDER BY `lastseen` DESC LIMIT 0, 1");

    if($resouce->num_rows > 0) {
      $result = $resouce->fetch_object();
      return $result->username;
    } else {
      return false; //aka, user not verified
    }
  }

  //$ip - check against auth.blockland.us. if blank, ignore
  public static function addEntry($blid, $username, $ip = null) {
    if($ip != null) {
      if(!UserLog::isRemoteVerified($blid, $username, $ip)) {
        return "auth.blockland.us verification failed";
      }
    }

    $db = new DatabaseManager();
    UserLog::verifyTable($db);
    $resource = $db->query("SELECT * FROM `user_log` WHERE `blid`='" . $db->sanitize($blid) . "' AND `username`='" . $db->sanitize($username) . "'");
    if($resource->num_rows == 0) {
      $db->query("INSERT INTO `user_log` (`blid`, `firstseen`, `lastseen`, `username`) VALUES ('" . $db->sanitize($blid) . "', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '" . $db->sanitize($username) . "');");
		} else {
      $db->query("UPDATE `user_log` SET `lastseen` = CURRENT_TIMESTAMP WHERE `blid`='" . $db->sanitize($blid) . "' AND `username`='" . $db->sanitize($username) . "'");
    }

		//update username
		if($user = UserManager::getFromBLID($blid)) {
			if($username != $user->getUsername()) {
				$user->setUsername($username);
			}
		}
  }

  public function isRemoteVerified($blid, $name, $ip) {
		$url = 'http://auth.blockland.us/authQuery.php';
		$data = array('NAME' => $name, 'IP' => $ip);
		$options = array(
		        'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query($data),
		    )
		);

		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);

		if(strpos($result, "NO") === 0) {
			return false;
		} else if(strpos($result, "YES") === 0) {
			$words = explode(" ", $result);
			if($word[1] == $blid) {
        return true;
      } else {
        return false; //right ip, wrong id? forging attempt?
      }
		} else if(strpos($result, "ERROR") === 0) {
			return false;
		} else {
			return false;
		}
  }

	private static function verifyTable($database) {
		//maybe replace verified/banned with 'status'
		if(!$database->query("CREATE TABLE IF NOT EXISTS `user_log` (
      `blid` int(11) NOT NULL,
      `firstseen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `lastseen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `username` varchar(64) NOT NULL
      )")) {
			throw new Exception("Error creating users table: " . $database->error());
		}
	}
}
?>
