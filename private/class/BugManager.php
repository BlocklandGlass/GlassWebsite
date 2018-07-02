<?php
namespace Glass;

require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));

class BugManager {
  public static function newBug($aid, $user, $title, $body) {
		$db = new DatabaseManager();
    BugManager::verifyTable($db);

    $aid   = $db->sanitize($aid);
    $user  = $db->sanitize($user);
    $title = $db->sanitize($title);
    $body  = $db->sanitize($body);

    $title = trim($title);
    $body  = trim($body);

    //$spamming = BugManager::checkSpam($aid, $user);

    $res = $db->query("INSERT INTO `addon_bugs` (`aid`, `blid`, `title`, `body`) VALUES ('$aid', '$user', '$title', '$body')");

    if(!$res)
      return false;

    return $db->fetchMysqli()->insert_id;
  }

  public static function newBugReply($bugId, $user, $body) {
		$db = new DatabaseManager();
    BugManager::verifyTable($db);

    $bugId = $db->sanitize($bugId);
    $user  = $db->sanitize($user);
    $body  = $db->sanitize($body);

    $res = $db->query("INSERT INTO `addon_bug_comments` (`bugId`, `blid`, `body`) VALUES ('$bugId', '$user', '$body')");

    return ($res ?? false) != false;
  }

  public static function bugVote($bugId, $user, $vote) {
		$db = new DatabaseManager();
    BugManager::verifyTable($db);

    $bugId   = $db->sanitize($bugId);
    $user    = $db->sanitize($user);
    $vote    = $vote == true;
    $voteStr = $vote ? '1' : '0';

    $res = $db->query("SELECT * FROM `addon_bug_votes` WHERE `blid`='$user' AND `bugId`='$bugId'");

    $obj = $res->fetch_object();
    if($obj) {
      //voted already
      if($obj->vote != $vote) {
        $id = $obj->id;
        $db->query("UPDATE `addon_bug_votes` SET `vote`='$voteStr' WHERE `id`='$id'");
      }
      return;
    } else {
      $db->query("INSERT INTO `addon_bug_votes` (`bugId`, `blid`, `vote`) VALUES ('$bugId', '$user', '$voteStr');");
    }
  }

  public static function getVotes($bugId) {
		$db = new DatabaseManager();
    BugManager::verifyTable($db);

    $bugId   = $db->sanitize($bugId);

    $res = $db->query("SELECT `vote` FROM `addon_bug_votes` WHERE `bugId`='$bugId'");

    if(!$res)
      return false;

    $sum = 0;
    while($obj = $res->fetch_object()) {
      $sum += $obj->vote ? 1 : -1;
    }
    return $sum;
  }

  public static function getVote($bugId, $user) {
		$db = new DatabaseManager();
    BugManager::verifyTable($db);

    $bugId   = $db->sanitize($bugId);
    $user    = $db->sanitize($user);

    $res = $db->query("SELECT `vote` FROM `addon_bug_votes` WHERE `bugId`='$bugId' AND `blid`='$user'");

    if(!$res)
      return 0;

    $obj = $res->fetch_object();

    if(!$obj)
      return 0;

    return $obj->vote ? 1 : -1;
  }

  public static function markDuplicate($original, $duplicate) {

  }

  public static function closeBugReport($bugId, $tog = false) {
		$db = new DatabaseManager();
    BugManager::verifyTable($db);

    $bugId   = $db->sanitize($bugId);

    $db->query("UPDATE `addon_bugs` SET `open`='" . ($tog ? 1 : 0) . "' WHERE `id`='$bugId'");
  }

  public static function getAddonBugs($aid) {
		$db = new DatabaseManager();
    BugManager::verifyTable($db);

    $aid   = $db->sanitize($aid);

    $res = $db->query("SELECT * FROM `addon_bugs` WHERE `aid`='$aid'");

    if(!$res)
      return false;

    $bugs = [];
    while($obj = $res->fetch_object()) {
      $bugs[] = $obj;
    }

    return $bugs;
  }

  public static function getAddonBugsOpen($aid) {
		$db = new DatabaseManager();
    BugManager::verifyTable($db);

    $aid   = $db->sanitize($aid);

    $res = $db->query("SELECT * FROM `addon_bugs` WHERE `aid`='$aid' AND open='1'");

    if(!$res)
      return false;

    $bugs = [];
    while($obj = $res->fetch_object()) {
      $bugs[] = $obj;
    }

    return $bugs;
  }

  public static function getFromId($id) {
		$db = new DatabaseManager();
    BugManager::verifyTable($db);

    $id   = $db->sanitize($id);

    $res = $db->query("SELECT * FROM `addon_bugs` WHERE `id`='$id'");
    if(!$res) return false;
    return $res->fetch_object();
  }

  public static function getCommentsFromId($id) {
		$db = new DatabaseManager();
    BugManager::verifyTable($db);

    $id  = $db->sanitize($id);

    $res = $db->query("SELECT * FROM `addon_bug_comments` WHERE `bugId`='$id' ORDER BY `timestamp` ASC");
    if(!$res) return false;

    $comments = [];
    while($obj = $res->fetch_object()) {
      $comments[] = $obj;
    }

    return $comments;

  }

  public static function verifyTable($database) {
    require_once(realpath(dirname(__FILE__) . '/UserManager.php'));
		require_once(realpath(dirname(__FILE__) . '/AddonManager.php'));
		UserManager::verifyTable($database);
		AddonManager::verifyTable($database);

		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_bugs` (
			`id` INT NOT NULL AUTO_INCREMENT,
			`aid` INT NOT NULL,
      `blid` INT NOT NULL,
			`title` TEXT NOT NULL,
			`body` TEXT NOT NULL,
			`timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

      `open` BOOLEAN NOT NULL DEFAULT 1,
      `duplicate` INT NULL DEFAULT NULL,

			KEY (`timestamp`),
			FOREIGN KEY (`aid`)
				REFERENCES addon_addons(`id`)
				ON UPDATE CASCADE
				ON DELETE CASCADE,

			PRIMARY KEY (`id`))")) {
			throw new \Exception("Unable to create table addon_bugs: " . $database->error());
		}

    if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_bug_comments` (
			`id` INT NOT NULL AUTO_INCREMENT,
      `blid` INT NOT NULL,
			`bugId` INT NOT NULL,
			`body` TEXT NOT NULL,
			`timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			KEY (`timestamp`),
			FOREIGN KEY (`bugId`)
				REFERENCES addon_bugs(`id`)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
			PRIMARY KEY (`id`))")) {
			throw new \Exception("Unable to create table addon_bug_comments: " . $database->error());
		}

    if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_bug_votes` (
			`id` INT NOT NULL AUTO_INCREMENT,
      `blid` INT NOT NULL,
			`bugId` INT NOT NULL,
			`vote` BOOLEAN NOT NULL,
			`timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			KEY (`timestamp`),
			FOREIGN KEY (`bugId`)
				REFERENCES addon_bugs(`id`)
				ON UPDATE CASCADE
				ON DELETE CASCADE,
			PRIMARY KEY (`id`))")) {
			throw new \Exception("Unable to create table addon_bug_comments: " . $database->error());
		}
  }
}

?>
