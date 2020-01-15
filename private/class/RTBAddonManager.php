<?php
namespace Glass;

require_once dirname(__FILE__) . '/DatabaseManager.php';

class RTBAddonManager {
  public static function doImport() {
    $db = new DatabaseManager();
    RTBAddonManager::verifyTable($db);

    $dir = realpath(dirname(__DIR__) . '/../local/rtb/');
    $files = scandir($dir);
    $data = array();
    foreach($files as $file) {
      if(strpos($file, ".zip") === false)
        continue;

      //echo($file . "<br />");

      $dat = new \stdClass();
      $dat->filename = $file;

      $zip = new \ZipArchive();
      $res = $zip->open($dir . '/' . $file);
      if($res === TRUE) {
        $rtbInfo = $zip->getFromName("rtbInfo.txt");
        //echo "<pre>$rtbInfo</pre><hr />";
        $lines = explode("\n", $rtbInfo);
        foreach($lines as $l) {
          $words = explode(": ", $l);
          if(sizeof($words) == 2) {
            $key = $words[0];
            $dat->$key = trim($words[1]);
          }
        }

        $description = $zip->getFromName("description.txt");

        $lines = explode("\n", $description);
        foreach($lines as $l) {
          $words = explode(": ", $l);
          if(sizeof($words) == 2) {
            $key = strtolower(trim($words[0]));
            $dat->$key = trim($words[1]);
          } else {
            break;
          }
        }

        $desc = join(array_splice($lines, 2), "\n");
        $desc = str_replace("\r\n", "\n", $desc);
      }

      if(!isset($dat->author)) {
        echo("\nMissing for " . $dat->id);
        echo("\n\n" . $description . "\n\n");
      }

      $res = $db->query("SELECT * FROM `rtb_addons` WHERE `id`=" . $db->sanitize($dat->id));
      $data[] = $dat;
      if($res->num_rows > 0) {
        $db->query($sql = "UPDATE `rtb_addons` SET " .
        "`icon`='" . $db->sanitize($dat->icon) . "', " .
        "`type`='" . $db->sanitize($dat->type) . "', " .
        "`title`='" . $db->sanitize($dat->title) . "', " .
        "`filename`='" . $db->sanitize($dat->filename) . "', " .
        "`author`='" . $db->sanitize($dat->author) . "', " .
        "`description`='" . $db->sanitize($desc) . "' " .
        " WHERE `id`='" . $db->sanitize($dat->id) . "'");

        //echo("Updated " . $dat->id . "\n");
      } else {
        $db->query($sql = "INSERT INTO `rtb_addons` (`id`, `icon`, `type`, `title`, `filename`, `glass_id`, `author`, `description`) VALUES (" .
        "'" . $db->sanitize($dat->id) . "'," .
        "'" . $db->sanitize($dat->icon) . "'," .
        "'" . $db->sanitize($dat->type) . "'," .
        "'" . $db->sanitize($dat->title) . "'," .
        "'" . $db->sanitize($dat->filename) . "'," .
        "0," .
        "'" . $db->sanitize($dat->author) . "'," .
        "'" . $db->sanitize($desc) . "')");

        //echo("Added " . $dat->id . "\n");
      }
      echo($db->error());
    }
    //var_dump($data);
  }

  public static function getBoards() {
    $db = new DatabaseManager();
    $res = $db->query("SELECT DISTINCT(type) AS board FROM `rtb_addons` ORDER BY `type` ASC");

    if($res === false) {
      return false;
    }

    $boards = array();
    while($obj = $res->fetch_object()) {
      $boards[] = $obj->board;
    }
    return $boards;
  }

  public static function getFromType($type, $start = 0, $max = 15) {
    if($start == $max) {
      $start = 0;
    }

    $db = new DatabaseManager();
    $res = $db->query("SELECT `title`,`id`,`description`,`author`,`glass_id` FROM `rtb_addons` WHERE `type`='" . $type . "' ORDER BY `title` ASC LIMIT " . $db->sanitize($max) . " OFFSET " . $db->sanitize($start));

    $ret = array();
    while($obj = $res->fetch_object()) {
      $ret[] = $obj;
    }
    return $ret;
  }

  public static function getAddons($page, $limit = 10) {
    $start = ($page-1)*$limit;
    $db = new DatabaseManager();
    $res = $db->query("SELECT * FROM `rtb_addons` ORDER BY `title` ASC LIMIT $start, $limit");

    echo $db->error();

    $ret = array();
    while($obj = $res->fetch_object()) {
      $ret[] = $obj;
    }
    return $ret;
  }

  public static function getCount() {
    $db = new DatabaseManager();
    $res = $db->query("SELECT COUNT(*) FROM `rtb_addons`");

    if(!$res)
      return 0;

    $obj = $res->fetch_object();
    $val = "COUNT(*)";
    return $obj->$val;
  }

  /**
   * The number of approved reclaims
   * @return int
   */
  public static function getReclaimedCount() {
    $db = new DatabaseManager();
    $res = $db->query("SELECT COUNT(*) FROM `rtb_addons` WHERE `glass_id` != '' AND `approved`=1");

    if(!$res)
      return 0;

    $obj = $res->fetch_object();
    $val = "COUNT(*)";
    return $obj->$val;
  }

  public static function getTypeCount($name) {
    $db = new DatabaseManager();
    $res = $db->query("SELECT COUNT(*) FROM `rtb_addons` WHERE `type`='" . $db->sanitize($name) . "'");

    if(!$res)
      return 0;

    $obj = $res->fetch_object();
    $val = "COUNT(*)";
    return $obj->$val;
  }

  public static function getAddonFromId($id) {
    $db = new DatabaseManager();
    $res = $db->query("SELECT * FROM `rtb_addons` WHERE `id`='" . $id . "' LIMIT 0, 1");

    return $res->fetch_object();
  }

  public static function getPendingReclaims() {
    $db = new DatabaseManager();
    $res = $db->query("SELECT * FROM `rtb_addons` WHERE `approved`='0'");

    if($res == false || $res == null)
      return [];

    $ret = array();
    while($obj = $res->fetch_object()) {
      $ret[] = $obj;
    }

    return $ret;
  }

  public static function getReclaims() {
    $db = new DatabaseManager();
    $res = $db->query("SELECT * FROM `rtb_addons` WHERE `approved`='1'");

    $ret = array();
    while($obj = $res->fetch_object()) {
      $ret[] = $obj;
    }

    return $ret;
  }

  public static function getBoardCount($type) {
    $db = new DatabaseManager();
    $res = $db->query("SELECT COUNT(*) FROM `rtb_addons` WHERE `type`='" . $db->sanitize($type) . "' ORDER BY `type` ASC");
    $obj = $res->fetch_object();
    $val = "COUNT(*)";
    return $obj->$val;
  }

  public static function requestReclaim($id, $aid) {
    $db = new DatabaseManager();
    if(RTBAddonManager::getReclaim($id) === false) {
      $db->update("rtb_addons", ["id"=>$id], ["glass_id"=>$aid, "approved"=>0]);
      return true;
    }
    return false;
  }

  public static function getReclaim($id) {
    $db = new DatabaseManager();
    $res = $db->query("SELECT `glass_id` FROM `rtb_addons` WHERE `id`='" . $db->sanitize($id) . "'");
    if($obj = $res->fetch_object()) {
      if($obj->glass_id != 0) {
        return $obj->glass_id;
      }
    }
    return false;
  }

  public static function acceptReclaim($id) {
    $db = new DatabaseManager();
    $db->update("rtb_addons", ["id"=>$id], ["approved"=>1]);
  }

  public static function rejectReclaim($id) {
    $db = new DatabaseManager();
    $db->update("rtb_addons", ["id"=>$id], ["approved"=>null]);
  }

  public static function searchByName($name) {
    $db = new DatabaseManager();
    RTBAddonManager::verifyTable($db);
    $res = $db->query("SELECT * from `rtb_addons` WHERE `title` LIKE '%" . $db->sanitize($name) . "%' LIMIT 0, 15");

    $ret = [];
    while($obj = $res->fetch_object()) {
      $ret[] = $obj;
    }

    return $ret;
  }

  public static function incrementDownloads($id, $type, $amount = 1) {
    $db = new DatabaseManager();
    RTBAddonManager::verifyTable($db);
    if($type == "ingame") {
      $var = "downloads_ingame";
    } else {
      $var = "downloads_web";
    }
    $res = $db->query("UPDATE `rtb_addons` SET `{$var}`=({$var}+1) WHERE `id` = '" . $db->sanitize($id) . "'");
  }

  public static function verifyTable($database) {
    if(!$database->query("CREATE TABLE IF NOT EXISTS `rtb_addons` (
      `id` int(11) NOT NULL,
      `icon` text NOT NULL,
      `type` text NOT NULL,
      `title` text NOT NULL,
      `glass_id` int(11) NOT NULL,
      `filename` text NOT NULL,

      `author` varchar(255) NOT NULL,
      `description` text NOT NULL,

      `downloads_web` int(11) NOT NULL DEFAULT 0,
      `downloads_ingame` int(11) NOT NULL DEFAULT 0,

      `approved` INT(1) NULL DEFAULT NULL,

      PRIMARY KEY (`id`))")) {
      throw new \Exception("Error creating rtb_addons table: " . $database->error());
    }
  }
}

?>
