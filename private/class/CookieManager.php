<?php
/** Contains definition of static class CookieManager */
namespace Glass;

use \Glass\UserManager;

/** Uses cookies (instead of non-expiring PHP sessions) to
 * provide session persistence */
class CookieManager {
  public static $EXPIRY_TIME = 30 * 24 * 60 ; // in minutes, 30 days

  /**
   * Generates a random key
   * @return string Random key
   */
  public static function generateKey()  {
    return bin2hex(openssl_random_pseudo_bytes(32));
  }

  /**
   * Generates hashed identifier givien a blid and salt
   * @param  int    $blid BLID to salt and hash
   * @param  string $salt String to salt hash with
   * @return string       Salted hash
   */
  public static function generateIdentifier(int $blid, string $salt) {
    return hash('sha256', $salt . $blid . $salt);
  }

  /**
   * Adds the blid/key pair to the databaswe
   * @param  int    $blid BLID associated with the key
   * @param  string $key  Key
   * @return void
   */
  public static function activateKey(int $blid, string $key, int $predecessor = NULL) {
    $database = new DatabaseManager();

    // necessary?
    if($predecessor) {
      if(!CookieManager::isUsed($predecessor)) {
        CookieManager::revokeKey($predecessor);
      }
    }

    $blid        = $database->sanitize($blid);
    $key         = $database->sanitize($key);
    $predecessor = $predecessor == NULL ? "NULL" : "'" . $database->sanitize($predecessor) . "'";
    $ttl         = $database->sanitize(CookieManager::$EXPIRY_TIME);

    $res = $database->query("INSERT INTO `user_cookies` (`blid`, `key`, `created`, `expiry`, `predecessor`) VALUES ('$blid', '$key', NOW(), NOW() + INTERVAL $ttl MINUTE, $predecessor)");

    if(!$res) {
      throw new \Exception("Failed to activate cookie");
    } else {
      return $database->fetchMysqli()->insert_id;
    }
  }

  public static function getChildren(int $rootid) {
    $database = new DatabaseManager();

    $rootid = $database->sanitize($rootid);

    $res = $database->query("
        select  id,
                predecessor
        from    (select * from user_cookies
                 order by predecessor, id) products_sorted,
                (select @pv := '$rootid') initialisation
        where   find_in_set(predecessor, @pv)
        and     length(@pv := concat(@pv, ',', id))
    ");

    echo($database->error());

    if($res) {
      $ids = [];
      while($obj = $res->fetch_object()) {
        $ids[] = $obj->id;
      }
      return $ids;
    } else {
      throw new \Exception("Failed to retrieve key chain");
    }
  }

  /**
   * Deactivates a key and any children it may have. Useful for fast-moving
   * revoke or working with stale data
   * @param  int    $id [description]
   * @return [type]     [description]
   */
  public static function revokeKeyAndChildren(int $id) {

  }

  /**
   * Deactivates a key. Either through normal key usage or through
   * manual revoke
   * @param  int    $id [description]
   * @return [type]     [description]
   */
  public static function revokeKey(int $id) {
    $database = new DatabaseManager();
    $id = $database->sanitize($id);

    $res = $database->query("UPDATE `user_cookies` SET revoked=b'1' WHERE id='$id'");

    if(!$res) {
      throw new \Exception("Failed to revoke key");
    }
  }

  /**
   * Marks a key as used.
   * @param  int    $id        [description]
   * @param  string $ip        [description]
   * @param  [type] $time_used [description]
   * @return [type]            [description]
   */
  public static function useKey(int $id, string $ip, int $time_used = NULL) {
    $database = new DatabaseManager();
    $id = $database->sanitize($id);
    $ip = $database->sanitize(inet_pton($ip));
    $time_used = $time_used == NULL ? "NOW()" : "'" . $database->sanitize(date("Y-m-d H:i:s", $time_used)) . "'";

    $res = $database->query("UPDATE `user_cookies` SET used=$time_used, ip='$ip' WHERE id='$id'");

    if(!$res) {
      throw new \Exception("Failed to use key! " . $database->error());
    }
  }

  public static function isUsed(int $id) {
    $database = new DatabaseManager();
    $id = $database->sanitize($id);

    $res = $database->query("SELECT `used` FROM `user_cookies` WHERE id='$id'");
    if($res) {
      $obj = $res->fetch_object();
      if($obj) {
        return $obj->used != NULL;
      } else {
        throw new \Exception("Invalid id cookie '$id'");
      }
    } else {
      throw new \Exception("Unable to check if cookie was used");
    }
  }

  public static function isValid(string $ident, string $key) {
    $database = new DatabaseManager();
    $key = $database->sanitize($key);

    $res = $database->query("SELECT `id`,`blid`,`used`,`expiry`,`revoked` FROM `user_cookies` WHERE `key`='$key'");

    if($res) {
      $obj = $res->fetch_object();
      if($obj) {

        if($obj->revoked || strtotime($obj->expiry) < time()) {
          return false;
        }

        $ident_check = UserManager::getCookieIdentifier($obj->blid);
        if($ident_check === $ident) {

          return [
            "id" => $obj->id,
            "blid" => $obj->blid
          ];

        } else {
          return false;
        }

      } else {
        return false;
      }
    } else {
      throw new \Exception("Unable to check cookie validity: " . $database->error());
    }
  }

  public static function getCurrentCookie() {
    $cookie = $_COOKIE['authentication'] ?? false;
    if($cookie) {
      if(strpos($cookie, ":")) {
        return $cookie;
      }
    }

    return false;
  }

  public static function giveCookie(int $blid, int $predecessor = NULL) {
    $ident = UserManager::getCookieIdentifier($blid);

    if(!$ident)
      return false;

    $key = CookieManager::generateKey();

    CookieManager::activateKey($blid, $key, $predecessor);

    setcookie('authentication', "$ident:$key", time() + (CookieManager::$EXPIRY_TIME * 60));

    return true;
  }

  public static function clearCookie() {
    setcookie('authentication', '', time());
  }

  public static function getAllChains(int $blid) {
    $database = new DatabaseManager();
    $blid = $database->sanitize($blid);

    $res = $database->query("SELECT `id` FROM `user_cookies` WHERE `blid`='$blid' AND `predecessor` IS NULL");

    if($res) {
      $results = [];
      while($data = $res->fetch_row()) {
        $root_id = $data[0];
        $children = CookieManager::getChildren($root_id);
        array_unshift($children, $root_id);
        $results[$root_id] = $children;
      }

      return $results;
    } else {
      return false;
    }
  }

  public static function createTable() {
    $database = new DatabaseManager();

    if(!$database->query("CREATE TABLE IF NOT EXISTS `user_cookies` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `predecessor` INT DEFAULT NULL,

      `blid` INT NOT NULL,
      `key` VARCHAR(256) NOT NULL,

      `revoked` bit(1) NOT NULL DEFAULT b'0',

      `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `expiry` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `used` TIMESTAMP NULL DEFAULT NULL,

      `ip` BINARY(16) DEFAULT NULL,
      FOREIGN KEY (`blid`)
        REFERENCES users(`blid`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
      FOREIGN KEY (`predecessor`)
        REFERENCES user_cookies(`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
      PRIMARY KEY (`id`))")) {
      throw new \Exception("Failed to create table user_cookies: " . $database->error());
    }
  }
}
?>
