<?php
namespace Glass;

//in charge of collecting long term stats, and also compiling/summarizing them
// so, at the end of the day the last 24 hours are compiled,
// at the end of the week, the last 7 days,
// etc

require_once dirname(__FILE__) . '/AddonManager.php';
require_once dirname(__FILE__) . '/BuildManager.php';
require_once dirname(__FILE__) . '/DatabaseManager.php';

class CronStatManager {
  function compare($stat1, $stat2) { //older, newer
    $result = new \stdClass();
    $result->duration = strtotime($stat2->time) - strtotime($stat1->time);


    //Addons
    $addons = new \stdClass();
    $addons->count = $stat2->addons->count - $stat1->addons->count;
    $result->addons = $addons;
    $result->addons->cumulative_downloads = array();
    foreach($stat2->addons->cumulative_downloads as $aid=>$downloadDat) {
      $dow = new \stdClass();
      if(isset($stat1->addons->cumulative_downloads->$aid)) {
        /*$dow->web = $stat2->addons->cumulative_downloads->$aid->web - $stat1->addons->cumulative_downloads->$aid->web;
        $dow->web = $stat2->addons->cumulative_downloads->$aid->ingame - $stat1->addons->cumulative_downloads->$aid->ingame;
        $dow->web = $stat2->addons->cumulative_downloads->$aid->update - $stat1->addons->cumulative_downloads->$aid->update;*/
      } else {
        /*$dow->web = $stat2->addons->cumulative_downloads->$aid->web;
        $dow->web = $stat2->addons->cumulative_downloads->$aid->ingame;
        $dow->web = $stat2->addons->cumulative_downloads->$aid->update;*/
      }

      $result->addons->cumulative_downloads[$aid] = $dow;
    }

    //Builds

    //Master
    $result->master = new \stdClass();
    $result->master->servers = $stat2->master->servers - $stat1->master->servers;
    $result->master->users = $stat2->master->users - $stat1->master->users;

    //$result->stat1 = $stat1;
    //$result->stat2 = $stat2;
    return $result;
  }

  function getEntry($time, $duration) {
    $database = new DatabaseManager();
    $res = $database->query("SELECT * FROM `cron_statistics` WHERE `duration`='" . $database->sanitize($duration) . "' AND `time`='" . $database->sanitize($time) . "'");
    if($res === false || $res->num_rows == 0) {
      return false;
    } else {
      $obj = json_decode($res->fetch_object()->data);
      return $obj;
    }
  }

  function collectHourStat($store = false) {
    $stats = new \stdClass();
    $stats->time = gmdate("Y-m-d H:00:00", time());
    $stats->duration = "hour";

    $database = new DatabaseManager();

    //Addons!
    $addons = new \stdClass();
    $addonArray = AddonManager::getAll();
    $addons->count = sizeof($addonArray);
    $addons->cumulative_downloads = array();
    $addons->usage = array();
    $addons->usage_total = array();
    foreach($addonArray as $addon) {
      $downloadData = new \stdClass();
      // TODO we need to go back. I dont want total downloads, I want individual
      //$downloadData->web =
      //$downloadData->ingame =
      //$downloadData->update =
      $addons->cumulative_downloads[$addon->getId()] = $downloadData;
      $res = $database->query("SELECT `version` FROM `stats_usage` WHERE `aid`='" . $addon->getId() . "' AND `reported` > now() - INTERVAL 1 HOUR");
      $ret = $res->fetch_object();
      $usage = array();
      $total = 0;
      while($obj = $res->fetch_object()) {
        $total++;
        if(!isset($usage[$obj->version])) {
          $usage[$obj->version] = 1;
        } else {
          $usage[$obj->version]++;
        }
      }
      $addons->usage[$addon->getId()] = $usage;
      $addons->usage_total[$addon->getId()] = $total;
    }
    $stats->addons = $addons;

    //Builds
    $builds = new \stdClass();
    $buildArray = BuildManager::getAll();
    $builds->count = sizeof($buildArray);
    $builds->cumulative_downloads = array();
    foreach($buildArray as $build) {
      // TODO this isn't done either...
      //$builds->cumulative_downloads[$build->getId()] = $build->getDownloads();
    }
    $stats->builds = $builds;


    //Master Server
    $stats->master = new \stdClass();
    $master = CronStatManager::getMasterServerStats();
    $stats->master->users = $master[0];
    $stats->master->servers = $master[1];

    if($store) {
      CronStatManager::verifyTable($database);
      $database->query("INSERT INTO `cron_statistics`  (`time` , `duration` , `data`) VALUES ('" . $stats->time . "',  'hour',  '" . $database->sanitize(json_encode($stats)) . "')");
    }

    return $stats;
  }


  function getMasterServerStats() {
    $url = 'http://master2.blockland.us/';
		$result = file_get_contents($url, false);
    if($result === false) {
      return [0, 0];
    }
    $entries = explode("\n", $result);
    $users = 0;
    $servers = 0;
    foreach($entries as $entry) {
      $field = explode("\t", $entry);
      if($field[0] == "FIELDS") {
        continue;
      }
      if(isset($field[5])) {
        $users += $field[5];
        $servers += 1;
      }
    }

    return array($users, $servers);
  }

  private static function verifyTable($database) {
    //maybe replace verified/banned with 'status'
    if(!$database->query("CREATE TABLE IF NOT EXISTS `cron_statistics` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `time` datetime NOT NULL,
        `duration` text NOT NULL,
        `data` text NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `id` (`id`)
      )")) {
      throw new \Exception("Error creating users table: " . $database->error());
    }
  }

  public function saveActivityFeed() {

  }

  public function getRecentBlocklandStats($hours = 12) {
    $ret = array();
    for($i = 1; $i <= $hours; $i++) {
      $entry = $this->getEntry(gmdate("Y-m-d H:00:00", time()-(($hours-$i)*3600)), "hour");
      if($entry != false) {
        $ret[gmdate("Y-m-d H:00:00", time()-(($hours-$i)*3600))] = $entry->master;
      }
    }
    return $ret;
  }

  public function getRecentAddonUsage($id, $hours = 12) {
    $ret = array();
    $entries = array();
    $versions = array();
    for($i = 1; $i <= $hours; $i++) {
      $entry = $this->getEntry(gmdate("Y-m-d H:00:00", time()-(($hours-$i)*3600)), "hour");

      if($entry != false) {
        if(isset($entry->addons->usage->$id)) {
          $entries[gmdate("Y-m-d H:00:00", time()-(($hours-$i)*3600))] = $entry;
          $usage = $entry->addons->usage->$id;
          foreach(get_object_vars($usage) as $v=>$va) {
            if(!in_array($v, $versions)) {
              array_push($versions, $v);
            }
          }
        }
      }
    }

    var_dump($versions);

    foreach($entries as $time=>$entry) {
      $ad = array();
      $usage = $entry->addons->usage->$id;

      foreach($versions as $v) {
        if(isset($usage->$v)) {
          $ad[$v] = $usage->$v;
        } else {
          $ad[$v] = 0;
        }
      }
      $ret[$time] = $ad;
    }
    return $ret;
  }
}
?>
