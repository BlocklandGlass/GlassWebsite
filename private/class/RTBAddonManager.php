<?php
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

      $dat = new stdClass();
      $dat->filename = $file;

      $zip = new ZipArchive();
      $res = $zip->open($dir . '/' . $file);
      if($res === TRUE) {
        $rtbInfo = $zip->getFromName("rtbInfo.txt");
        //echo "<pre>$rtbInfo</pre><hr />";
        $lines = explode("\n", $rtbInfo);
        foreach($lines as $l) {
          $words = explode(": ", $l);
          if(sizeof($words) == 2)
            $dat->$words[0] = trim($words[1]);
        }
      }

      $data[] = $dat;
      $db->query("INSERT INTO `rtb_addons` (`id`, `icon`, `type`, `title`, `filename`, `glass_id`) VALUES (" .
      "'" . $db->sanitize($dat->id) . "'," .
      "'" . $db->sanitize($dat->icon) . "'," .
      "'" . $db->sanitize($dat->type) . "'," .
      "'" . $db->sanitize($dat->title) . "'," .
      "'" . $db->sanitize($dat->filename) . "', '')");

      echo($db->error());
    }
    //var_dump($data);
  }

  public static function getBoards() {
    $db = new DatabaseManager();
    $res = $db->query("SELECT DISTINCT(type) AS board FROM `rtb_addons` ORDER BY `type` ASC");

    $boards = array();
    while($obj = $res->fetch_object()) {
      $boards[] = $obj->board;
    }
    return $boards;
  }

  public static function getFromType($type) {
    $db = new DatabaseManager();
    $res = $db->query("SELECT `title`,`id` FROM `rtb_addons` WHERE `type`='" . $type . "' ORDER BY `title` ASC");

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

    $obj = $res->fetch_object();
    $val = "COUNT(*)";
    return $obj->$val;
  }

  public static function getAddonFromId($id) {
    $db = new DatabaseManager();
    $res = $db->query("SELECT * FROM `rtb_addons` WHERE `id`='" . $id . "' LIMIT 0, 1");

    return $res->fetch_object();
  }

  public static function getBoardCount($type) {
    $db = new DatabaseManager();
    $res = $db->query("SELECT COUNT(*) FROM `rtb_addons` WHERE `type`='" . $db->sanitize($type) . "' ORDER BY `type` ASC");
    $obj = $res->fetch_object();
    $val = "COUNT(*)";
    return $obj->$val;
  }

  public static function verifyTable($database) {
    if(!$database->query("CREATE TABLE IF NOT EXISTS `rtb_addons` (
      `id` int(11) NOT NULL,
      `icon` text NOT NULL,
      `type` text NOT NULL,
      `title` text NOT NULL,
      `glass_id` int(11) NOT NULL,
      `filename` text NOT NULL)")) {
      throw new Exception("Error creating rtb_addons table: " . $database->error());
    }
  }
}

?>
