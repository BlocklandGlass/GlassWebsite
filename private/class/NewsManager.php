<?php
namespace Glass;

require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));

class NewsManager {
  public static function publishNews($text) {
    $db = new DatabaseManager();
    NewsManager::verifyTable($db);

    $db->query("INSERT INTO `news` (`text`, `sticky`) VALUES ('" . $db->sanitize($text) . "', b'0');");
  }

  public static function getNews($offset = false, $count = false) {
    $db = new DatabaseManager();
    NewsManager::verifyTable($db);
    $sql = "SELECT * FROM `news`";
    $sql .= "ORDER BY `id` DESC";
    if($offset !== false && $count !== false) {
      $sql .= " LIMIT " . $db->sanitize($offset) . "," . $db->sanitize($count);
    }

    $res = $db->query($sql);

    $arr = [];

    while($obj = $res->fetch_object()) {
      $arr[] = $obj;
    }

    usort($arr, function($a, $b) {
      if($a->sticky) {
        if(!$b->sticky) {
          return -1;
        }
      }

      if(strtotime($a->date) > strtotime($b->date)) {
        return -1;
      } else {
        return 1;
      }
    });

    return $arr;
  }

	public static function verifyTable($database) {
    if(!$database->query("CREATE TABLE IF NOT EXISTS `news` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `text` MEDIUMTEXT NOT NULL,
      `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `sticky` BIT(1) NOT NULL,
      PRIMARY KEY (`id`))")) {
	    throw new \Exception("Failed to create news table: " . $database->error());
    }
  }
}
?>
