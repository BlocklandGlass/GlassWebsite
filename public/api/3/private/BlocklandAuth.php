<?php
require dirname(__DIR__) . '/../../../private/autoload.php';

use Glass\BlocklandAuthenticate;

class BlocklandAuth {
  public static function checkAuth($username, $ip, $blid) {
    $res = BlocklandAuth::auth($username, $ip);
    if($res !== false) {
      return $res == $blid;
    } else {
      return false;
    }
  }

  public static function auth($name, $ip) {
    try {
  		$result = BlocklandAuthenticate::BlocklandAuthenticate($name);
  		return $result;
    } catch(Exception $e) {
      error_log("BlocklandAuth auth error: " . $e);
      return false;
    }
  }
}
?>
