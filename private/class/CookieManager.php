<?php
/** Contains definition of static class CookieManager */
namespace Glass;

use \Glass\UserManager;
require_once(realpath(dirname(__DIR__) . '/lib/UserAgentParser.php'));

/** Uses cookies (instead of non-expiring PHP sessions) to
 * provide session persistence */
class CookieManager {
  public static $EXPIRY_TIME = 30 * 24 * 60 ; // in minutes, 30 days

  /**
   * Generates a random 32 byte (256 bit) key
   * @return string Random key
   */
  public static function generateKey() {
    //  1.15e77 possible
    return bin2hex(openssl_random_pseudo_bytes(32));
  }

  /**
   * Generates a unique family identifier (4 byte/32 bit hex)
   * @return string Unique family identifier
   */
  public static function generateFamilyIdentifier() {
    $database = new DatabaseManager();

    // this doesn't need to be obscure, just unique as it's
    // only for record keeping. can be extended later
    // but I doubt we'll hit 4 billion sessions

    $found_unique = false;
    while(!$found_unique) {
      $ident = bin2hex(openssl_random_pseudo_bytes(4));
      $res = $database->query("SELECT EXISTS(SELECT 1 FROM `user_cookies` WHERE `family`=UNHEX('$ident') LIMIT 1)");

      $found_unique = $res->fetch_row()[0] == 0;
    }

    return $ident;
  }

  /**
   * Adds the blid/key pair to the database
   * @param  int    $blid        BLID associated with the key
   * @param  string $key         Key
   * @param  int    $predecessor The key used to validate this one
   * @return int    ID
   */
  public static function activateKey(int $blid, string $key, int $predecessor = NULL) {
    $database = new DatabaseManager();
    CookieManager::createTable($database);
    /*if($predecessor) {
      if(!CookieManager::isUsed($predecessor)) {
        //CookieManager::revokeKey($predecessor);
        throw new \Exception("Attempted to activate a successor to an unused key");
      }
    }*/

    $hash = hash('sha256', $key);

    $blid        = $database->sanitize($blid);
    $hash        = $database->sanitize($hash);
    $ttl         = $database->sanitize(CookieManager::$EXPIRY_TIME);

    $family = $predecessor == NULL ? "UNHEX('" . CookieManager::generateFamilyIdentifier() . "')" : "(SELECT `family` FROM (SELECT * FROM `user_cookies` WHERE `id`='$predecessor') as temp)";
    $predecessor = $predecessor == NULL ? "NULL" : "'" . $database->sanitize($predecessor) . "'";


    $res = $database->query("INSERT INTO `user_cookies` (`blid`, `hash`, `created`, `expiry`, `predecessor`, `family`) VALUES ('$blid', UNHEX('$hash'), NOW(), NOW() + INTERVAL $ttl MINUTE, $predecessor, $family)");

    if(!$res) {
      throw new \Exception("Failed to activate cookie:" . $database->error());
    } else {
      return $database->fetchMysqli()->insert_id;
    }
  }

  /**
   * Revokes a family
   * @param  int    $ident Family ident to revoke
   * @return void
   */
  public static function revokeFamily(string $ident) {
      $database = new DatabaseManager();
      $ident = $database->sanitize($ident);

      $res = $database->query("UPDATE `user_cookies`
                               SET revoked=b'1'
                               WHERE family=UNHEX('$ident')");

      if(!$res) {
        throw new \Exception("Failed to revoke key");
      }
  }

  /**
   * Revokes a family from the id of one of its' members
   * @param  int    $id Family member to revoke
   * @return void
   */
  public static function revokeFamilyById(int $id) {
    $database = new DatabaseManager();
    $id = $database->sanitize($id);

    $res = $database->query("UPDATE `user_cookies`
                             SET revoked=b'1'
                             WHERE family=(
                               SELECT `family`
                               FROM (SELECT * from `user_cookies`) as temp
                               WHERE id='$id'
                             )");

    if(!$res) {
      throw new \Exception("Failed to revoke key");
    }
  }

  public static function ownsFamily(int $blid, string $ident) {
    $database = new DatabaseManager();

    $ident = $database->sanitize($ident);
    $blid = $database->sanitize($blid);

    $res = $database->query("SELECT EXISTS (
                               SELECT 1 FROM `user_cookies`
                               WHERE `family`=UNHEX('$ident')
                               AND `blid`='$blid'
                               LIMIT 1
                             )");
    if($res) {
      return $res->fetch_row()[0] == 1;
    } else {
      return false;
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

    $ua_info = parse_user_agent();

    $browser = $database->sanitize($ua_info['browser']);
    $platform = $database->sanitize($ua_info['platform']);

    $res1 = $database->query("UPDATE `user_cookies`
                             SET used=$time_used
                             WHERE id='$id'");

    if(!$res1) {
     throw new \Exception("Failed to use key! " . $database->error());
    }

    $res2 = $database->query("INSERT INTO `user_cookies_usage` (`id`, `ip`, `browser`, `platform`)
                             VALUES ('$id', '$ip', '$browser', '$platform')");

    if(!$res2) {
      throw new \Exception("Failed to use key! " . $database->error());
    }
  }

  /**
   * [isUsed description]
   * @param  int     $id [description]
   * @return boolean     [description]
   */
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

  public static function isExpired(int $id) {
    $database = new DatabaseManager();
    $id = $database->sanitize($id);

    $res = $database->query("SELECT `expiry` FROM `user_cookies` WHERE id='$id'");
    if($res) {
      $obj = $res->fetch_row();
      if($obj) {
        return strtotime($obj[0]) < time();
      } else {
        throw new \Exception("Invalid id cookie '$id'");
      }
    } else {
      throw new \Exception("Unable to check if cookie is expired");
    }
  }

  public static function isRevoked(int $id) {
    $database = new DatabaseManager();
    $id = $database->sanitize($id);

    $res = $database->query("SELECT `revoked` FROM `user_cookies` WHERE id='$id'");
    if($res) {
      $obj = $res->fetch_row();
      if($obj) {
        return $obj['0'] == 1;
      } else {
        throw new \Exception("Invalid id cookie '$id'");
      }
    } else {
      throw new \Exception("Unable to check if cookie is expired");
    }
  }

  /**
   * Checks if the give blid and key pair are valid for authentication
   * @param  int     $blid Account BLID
   * @param  string  $key  Authentication key
   * @return mixed         False on failure, array of 'id' and 'blid' if success
   */
  public static function isValid(int $blid, string $key) {
    $database = new DatabaseManager();
    CookieManager::createTable($database);

    $blid = $database->sanitize($blid);

    $hash_given = hash('sha256', $key);
    $res = $database->query("SELECT `id`,`blid`, `hash`
                             FROM `user_cookies`
                             WHERE `blid`='$blid'
                               AND `revoked`=b'0'
                               AND `used` IS NULL
                               AND `expiry` > NOW()");

    if($res) {

      $valid = false;
      while($obj = $res->fetch_object()) {

        // timing safe comparison
        if(hash_equals(bin2hex($obj->hash), $hash_given)) {
          $valid = [
            "id" => $obj->id,
            "blid" => $obj->blid
          ];
        }

      }
      return $valid;
    } else {
      throw new \Exception("Unable to check cookie validity: " . $database->error());
    }
  }

  /**
   * Replica of CookieManager::isValid, except without validity check
   * @param  int     $blid Account BLID
   * @param  string  $key  Authentication key
   * @return mixed         False on failure, array of 'id' and 'blid' if success
   */
  public static function getId(int $blid, string $key) {
    $database = new DatabaseManager();
    CookieManager::createTable($database);

    $blid = $database->sanitize($blid);

    $hash_given = hash('sha256', $key);
    $res = $database->query("SELECT `id`,`blid`, `hash`
                             FROM `user_cookies`
                             WHERE `blid`='$blid'");

    if($res) {

      $valid = false;
      while($obj = $res->fetch_object()) {

        // timing safe comparison
        if(hash_equals(bin2hex($obj->hash), $hash_given)) {
          $valid = [
            "id" => $obj->id,
            "blid" => $obj->blid
          ];
        }

      }
      return $valid;
    } else {
      throw new \Exception("Unable to check cookie validity: " . $database->error());
    }
  }

  /**
   * Returns the value of the current requester's cookie
   * @return string Cookie string
   */
  public static function getCurrentCookie() {
    $cookie = $_COOKIE['authentication'] ?? false;
    if($cookie) {
      if(strpos($cookie, ":")) {
        return $cookie;
      }
    }

    return false;
  }

  /**
   * Generates, actives, and gives the current requester a cookie
   * @param  int $blid        Account for cookie
   * @param  int $predecessor Prior cookie in family
   * @return bool             Success
   */
  public static function giveCookie(int $blid, int $predecessor = NULL) {
    try {
      $key = CookieManager::generateKey();
      $id = CookieManager::activateKey($blid, $key, $predecessor);

      if($predecessor == NULL) {
        //use the first cookie of the chain to record session
        CookieManager::useKey($id, $_SERVER['REMOTE_ADDR']);
        CookieManager::giveCookie($blid, $id);
      } else {
        setcookie('authentication', "$blid:$key", time() + (CookieManager::$EXPIRY_TIME * 60));
      }
    } catch(\Exception $e) {
      echo $e;
      error_log("Error giving cookie: " . $e);
      return false;
    }

    return true;
  }

  /**
   * Clears and expires the current requester's cookie
   * @return void
   */
  public static function clearCookie() {
    setcookie('authentication', '', time());
  }

  /**
   * Gets all cookies associated with a blid
   * @param  int    $blid   BLID to query
   * @param  array  $fields Fields to return in results. default are `id` and `family`, available:
   *                        `predecessor`, `created`, `expiry`, `used`, `ip`, `revoked`
   * @return mixed<boolean,
   *               array>   Associative array of cookies sorted by family id. 3D array.
   *                        $result[family_hex][number][field]
   */
  public static function getAllChains(int $blid, array $fields = NULL) {
    $database = new DatabaseManager();
    $blid = $database->sanitize($blid);

    if($fields == NULL)
      $fields = [];

    $available_fields = ['predecessor', 'created', 'expiry', 'used', 'ip', 'revoked', 'platform', 'browser'];
    $field_table_map = [
      'predecessor' => 'user_cookies',
      'created'     => 'user_cookies',
      'expiry'      => 'user_cookies',
      'used'        => 'user_cookies',
      'revoked'     => 'user_cookies',
      'ip'          => 'user_cookies_usage',
      'platform'    => 'user_cookies_usage',
      'browser'     => 'user_cookies_usage'
    ];

    $fields_query = "";
    foreach(array_intersect($available_fields, $fields) as $field) {
      $fields_query .= ", `" . $field_table_map[$field] . "`.`$field` as `$field`";
    }

    $res = $database->query("SELECT `user_cookies`.`id`, lower(hex(`family`)) as family $fields_query
                             FROM `user_cookies`
                             LEFT JOIN `user_cookies_usage` ON user_cookies.id=user_cookies_usage.id
                             WHERE `blid`='$blid'
                             ORDER BY `id` ASC"); // ordering by ID is same as date but cheaper

    if($res) {
      $results = [];
      while($data = $res->fetch_assoc()) {
        if(isset($data['ip']))
          $data['ip'] = inet_ntop($data['ip']);
        $results[$data['family']][] = $data;
      }

      return $results;
    } else {
      echo $database->error();
      return false;
    }
  }

  /**
   * Subset of getAllChains results, only containing live chains
   * @param  int    $blid   BLID to query
   * @param  array  $fields Fields to return in results. default are `id` and `family`, available:
   *                        `predecessor`, `created`, `expiry`, `used`, `ip`, `revoked`
   * @return mixed          Associative array of cookies sorted by family id. 3D array.
   *                        $result[family_hex][number][field]
   */
  public static function getActiveChains(int $blid, array $fields = NULL) {
    $database = new DatabaseManager();
    $blid = $database->sanitize($blid);

    if($fields == NULL)
      $fields = [];

    $available_fields = ['predecessor', 'created', 'expiry', 'used', 'ip', 'revoked', 'platform', 'browser'];
    $field_table_map = [
      'predecessor' => 'user_cookies',
      'created'     => 'user_cookies',
      'expiry'      => 'user_cookies',
      'used'        => 'user_cookies',
      'revoked'     => 'user_cookies',
      'ip'          => 'user_cookies_usage',
      'platform'    => 'user_cookies_usage',
      'browser'     => 'user_cookies_usage'
    ];

    $fields_query = "";
    foreach(array_intersect($available_fields, $fields) as $field) {
      $fields_query .= ", `" . $field_table_map[$field] . "`.`$field` as `$field`";
    }

    $res = $database->query("SELECT `user_cookies`.`id`, lower(hex(`family`)) as family $fields_query
                             FROM `user_cookies`
                             LEFT JOIN `user_cookies_usage` ON user_cookies.id=user_cookies_usage.id
                             WHERE `blid`='$blid'
                               AND `family` in (
                                 SELECT `family`
                                 FROM `user_cookies`
                                 WHERE `blid`='$blid'
                                   AND `revoked`=b'0'
                                   AND `used` IS NULL
                                   AND `expiry` > NOW()
                               )
                             ORDER BY `id` DESC");

     if($res) {
       $results = [];
       while($data = $res->fetch_assoc()) {
         if(isset($data['ip']))
           $data['ip'] = inet_ntop($data['ip']);
         $results[$data['family']][] = $data;
       }

       return $results;
     } else {
       echo $database->error();
       return false;
     }
  }

  /**
   * Gets a list of used cookies
   * @param  int    $blid    BLID associated with cookies
   * @param  array  $fields  Fields to query
   * @param  int    $limit   Number to fetch (default infinite)
   * @param  int    $minutes Minutes to query (default infinite)
   * @return array           Array of usage history
   */
  public static function getUsageHistory(int $blid, array $fields = NULL, int $limit = NULL, int $minutes = NULL) {
    $database = new DatabaseManager();
    $blid = $database->sanitize($blid);

    if($fields == NULL)
      $fields = [];

    $available_fields = ['predecessor', 'created', 'expiry', 'used', 'ip', 'revoked', 'platform', 'browser'];
    $field_table_map = [
      'predecessor' => 'user_cookies',
      'created'     => 'user_cookies',
      'expiry'      => 'user_cookies',
      'used'        => 'user_cookies',
      'revoked'     => 'user_cookies',
      'ip'          => 'user_cookies_usage',
      'platform'    => 'user_cookies_usage',
      'browser'     => 'user_cookies_usage'
    ];

    $fields_query = "";
    foreach(array_intersect($available_fields, $fields) as $field) {
      $fields_query .= ", `" . $field_table_map[$field] . "`.`$field` as `$field`";
    }

    $where_query = "";
    if($minutes !== NULL) {
      $minutes = $database->sanitize($minutes);
      $where_query = " AND `used` > NOW() - INTERVAL $minutes MINUTE ";
    }

    $limit_query = "";
    if($limit !== NULL || $limit > 0) {
      $limit = $database->sanitize($limit);
      $limit_query = " LIMIT $limit ";
    }

    $res = $database->query("SELECT `user_cookies`.`id`, lower(hex(`family`)) as family $fields_query
                             FROM `user_cookies`
                             LEFT JOIN `user_cookies_usage` ON user_cookies.id=user_cookies_usage.id
                             WHERE `blid`='$blid'
                               AND `used` is not NULL
                               $where_query
                             ORDER BY `used` DESC
                             $limit_query");

    if($res) {
     $results = [];
     while($data = $res->fetch_assoc()) {
       if(isset($data['ip']))
         $data['ip'] = inet_ntop($data['ip']);
       $results[] = $data;
     }

     return $results;
    } else {
     echo $database->error();
     return false;
    }
  }

  /**
   * Creates the user_cookies and related tables
   * @return void
   */
  public static function createTable() {
    $database = new DatabaseManager();

    if(!$database->query("CREATE TABLE IF NOT EXISTS `user_cookies` (
      `id` INT NOT NULL AUTO_INCREMENT,

      `family` BINARY(4) NOT NULL,
      `predecessor` INT DEFAULT NULL,

      `blid` INT NOT NULL,
      `hash` BINARY(32) NOT NULL,

      `revoked` bit(1) NOT NULL DEFAULT b'0',

      `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `expiry` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `used` TIMESTAMP NULL DEFAULT NULL,
      /*FOREIGN KEY (`blid`)
        REFERENCES users(`blid`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
      FOREIGN KEY (`predecessor`)
        REFERENCES user_cookies(`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,*/
      PRIMARY KEY `id` (`id`, `revoked`))
      PARTITION BY HASH(revoked)")) {
      throw new \Exception("Failed to create table user_cookies: " . $database->error());
    }

    if(!$database->query("CREATE TABLE IF NOT EXISTS `user_cookies_usage` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `platform` VARCHAR(32) NULL,
      `browser` VARCHAR(32) NULL,
      `ip` VARBINARY(16) DEFAULT NULL,
      PRIMARY KEY (`id`))")) {
      throw new \Exception("Failed to create table user_cookies: " . $database->error());
    }
  }
}
?>
