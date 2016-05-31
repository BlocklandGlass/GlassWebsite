<?php
require_once(realpath(dirname(__FILE__) . "/DatabaseManager.php"));
class StatUsageManager {
  public static function addEntry($blid, $aid, $hash, $version, $beta = false, $date = null) {
    if($date == null) {
      $date = time();
    }

    $db = new DatabaseManager();
    $res = $db->query("SELECT COUNT(*) FROM `stats_usage` WHERE `blid`='" . $db->sanitize($blid) . "' AND `aid`='" . $db->sanitize($aid) . "' AND `hash`='" . $db->sanitize($version) . "' ");

    if(!isset($res->fetch_object()->total) || $res->fetch_object()->total == 0) {
      $db->query("INSERT INTO `stats_usage` (blid, aid, hash, version, beta, reported) VALUES (
      '" . $db->sanitize($blid) . "',
      '" . $db->sanitize($aid) . "',
      '" . $db->sanitize($hash) . "',
      '" . $db->sanitize($version) . "',
      '" . ($beta ? 1 : 0) . "',
      '" . $db->sanitize($date) . "')");
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
}
?>
