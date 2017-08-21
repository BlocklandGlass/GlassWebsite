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
    $obj = apc_fetch("clientConnection_" . $ident, $success);

    if($obj->blAuthed && $obj->expire < time()) {
      $name = $obj->name;
      $blid = $obj->blid;
      error_log("Glass Auth Expired! ($name, $blid)");
      return false;
    }

    if($success && is_object($obj)) {
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
        if(apc_fetch("clientConnection_" . $ident) === false) {
          $unique = true;
        }
      }
      $this->identifier = $ident;
    }
  }

  function __destruct() {
    apc_store("clientConnection_" . $this->identifier, $this);
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
    return BlocklandAuth::checkAuth(utf8_encode($this->name), $this->ip, $this->blid);
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

  function getBlid() {
    return $this->blid;
  }

  function getUsername() {
    return $this->name;
  }
}

?>
