<?php
require dirname(__DIR__) . '/../../../private/autoload.php';

require_once dirname(__FILE__) . "/BlocklandAuth.php";

use Glass\UserManager;
use Glass\UserLog;
use Glass\DigestAccessAuthentication;

class ClientConnection {
  private $blid;
  private $name;
  private $ip;

  private $accountData;
  private $blAuthed = false;

  private $identifier;

  private $server;
  private $daa;

  private $expire;

  public static $cacheTime = 15 * 60;


  public static function loadFromIdentifier($ident) {
    $obj = apcu_fetch("clientConnection_" . $ident, $success);

    if($success && is_object($obj)) {

	   if($obj->blAuthed && $obj->expire < time()) {
        $name = $obj->name;
        $blid = $obj->blid;
        error_log("Glass Auth Expired! ($name, $blid)");
        return false;
      }

      return $obj;
    } else {
      return false;
    }
  }

  function __construct($array) {
    $this->blid = $array[0];
    $this->name = $array[1];
    $this->ip = $array[2];

    if(sizeof($array) > 3) {
      $this->accountData = json_decode($array[3]);
      $this->blAuthed = $array[4];
      $this->identifier = $array[5];
    } else {
      //don't set account data until run through BlocklandAuth
      $unique = false;
      while(!$unique) { //avoiding the extremely rare case of a random id being non-unique
        $ident = base64_encode(rand());
        if(apcu_fetch("clientConnection_" . $ident) === false) {
          $unique = true;
        }
      }
      $this->identifier = $ident;
    }
  }

  function __destruct() {
    apcu_store("clientConnection_" . $this->identifier, $this);
  }

  function setDigestAccessAuth($bool) {
    if($bool) {
      if(!is_object($this->daa)) {
        $this->daa = new DigestAccessAuthentication('api.blocklandglass.com');
        $this->digest = $this->daa->generate();
        $this->identifier = $this->daa->getOpaque();
      }
    } else {
      $this->daa = null;
    }
  }

  function isDAA() {
    return is_object($this->daa);
  }

  function getDigestData() {
    return $this->digest;
  }

  function getDigest() {
    return $this->daa;
  }

  function getIdentifier() {
    return $this->identifier;
  }

  function isAuthed() {
    return $this->blAuthed;
  }

  function checkIp($ip) {
    return $this->ip == $ip;
  }

  function attemptBlocklandAuth() {
    $res = BlocklandAuth::checkAuth(utf8_encode($this->name), $this->ip, $this->blid);

    if($res == false) {
      // debug
      error_log("Blockland Auth failed for " . utf8_encode($this->name) . " - " . $this->ip . " - " . $this->blid);
    }

    return $res;
  }

  function attemptServerAuth() {
    // ordinary blockland auth doesn't work for servers
    // but we can check the IP/Username pair to the master server

    $name = "";
    if(substr($this->name, -1) === "s") {
      $name = $this->name . "'";
    } else {
      $name = $this->name . "'s";
    }

    $data = file_get_contents("http://master2.blockland.us");
    $rows = explode("\n", $data);

    // filter for IP and name
    $rows = array_filter($rows, function($val) use ($name) {
      $field = explode("\t", trim($val));
      if(sizeof($field) < 9)
        return false;

      return ($field[0] == $this->ip) && (strpos($field[4], $name) === 0);
    });

    return (sizeof($rows) > 0);
  }

  function hasGlassAccount() {
    $user = UserManager::getFromBLID($this->blid);
    if($user !== false) {
      return true;
    } else {
      return false;
    }
  }

  function getUnverifiedAccounts() {
    $userArray = UserManager::getAllAccountsFromBLID($this->blid);
    $newArray = array();
    foreach($userArray as $user) {
      if(!$user->getVerified()) {
        $newArray[] = $user;
      }
    }

    return $newArray;
  }

  function setAuthed($bool) {
    $this->blAuthed = $bool;
    if($bool) {
      UserLog::addEntry($this->blid, $this->name);
      $this->expire = time() + ClientConnection::$cacheTime;
    }
  }

  function setServer($bool) {
    $this->server = $bool;
  }

  function isServer() {
    return $this->server;
  }

  function getBlid() {
    return $this->blid;
  }

  function getUsername() {
    return $this->name;
  }
}

?>
