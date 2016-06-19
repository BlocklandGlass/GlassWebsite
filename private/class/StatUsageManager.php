<?php
require_once(realpath(dirname(__FILE__) . "/DatabaseManager.php"));
class StatUsageManager {
  public static function addEntry($blid, $aid, $hash, $version, $beta = false, $date = null) {
    if($date == null) {
      $date = time();
    }

    $db = new DatabaseManager();
    $res = $db->query($sq = "SELECT COUNT(*) FROM `stats_usage` WHERE `blid`='" . $db->sanitize($blid) . "' AND `aid`='" . $db->sanitize($aid) . "' AND `hash`='" . $db->sanitize($hash) . "' ");
    $ret = $res->fetch_row();
    if(!isset($ret[0]) || $ret[0] == 0) {
      $res = $db->query($sq = "INSERT INTO `stats_usage` (`blid`, `aid`, `hash`, `version`, `beta`, `reported`) VALUES (
      '" . $db->sanitize($blid) . "',
      '" . $db->sanitize($aid) . "',
      '" . $db->sanitize($hash) . "',
      '" . $db->sanitize($version) . "',
      '" . ($beta ? 1 : 0) . "',
      '" . $db->sanitize(date("Y-m-d H:i:s", $date)) . "')");
    } else {
      $db->update("stats_usage", ["blid"=>$blid, "aid"=>$aid, "hash"=>$hash], ["version"=>$version, "beta"=>($beta ? 1 : 0), "reported"=>date("Y-m-d H:i:s")]);
    }

    if(($error = $db->error())) {
      return array("status"=>"error", "error"=>$error);
    } else {
      return true;
    }
  }

  public static function checkExpired() {
    $db = new DatabaseManager();
    StatUsageManager::verifyTable($db);
    $db->query("SELECT * FROM `stats_usage` WHERE `reported` < now() - INTERVAL 30 DAY");
  }

  public static function verifyTable($database) {
    if(!$database->query("CREATE TABLE IF NOT EXISTS `stats_usage` (
      `blid` int(11) NOT NULL,
      `aid` int(11) NOT NULL,
      `hash` text NOT NULL,
      `version` text NOT NULL,
      `beta` int(1) NOT NULL,
      `reported` timestamp NOT NULL,
      FOREIGN KEY (`aid`)
        REFERENCES addon_addons(`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE)")) {
      throw new Exception("Failed to create table stats_usage: " . $database->error());
    }
  }

  public static function getDistribution($aid) {
    $db = new DatabaseManager();
    $res = $db->query("SELECT * FROM `stats_usage` WHERE `aid`='" . $db->sanitize($aid) ."' AND `reported` > now() - INTERVAL 30 DAY");

    $ret = array();
    while($obj = $res->fetch_object()) {
      if(isset($ret[$obj->version])) {
        $ret[$obj->version]++;
      } else {
        $ret[$obj->version] = 1;
      }
    }

    return $ret;
  }
}
?>
