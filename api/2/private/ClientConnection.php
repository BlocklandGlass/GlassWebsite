<?php
require_once dirname(__FILE__) . "/BlocklandAuth.php";
require_once dirname(dirname(__DIR__)) . "/../private/class/UserManager.php";
require_once dirname(dirname(__DIR__)) . "/../private/class/UserLog.php";

class ClientConnection {
  private $blid;
  private $name;
  private $ip;

  private $accountData;
  private $blAuthed = false;

  private $identifier;


  public static function loadFromIdentifier($ident) {
    $data = apc_fetch("clientConnection_" . $ident);

    if($data !== false) {
      return new ClientConnection(json_decode($data));
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
    $data = array($this->blid, $this->name, $this->ip, json_encode($this->accountData), $this->blAuthed, $this->identifier);
    apc_store("clientConnection_" . $this->identifier, json_encode($data));
  }


  function getIdentifier() {
    return $this->identifier;
  }

  function isAuthed() {
    return $this->blAuthed;
  }

  function attemptBlocklandAuth() {
    return true;
    return BlocklandAuth::checkAuth($this->name, $this->ip, $this->blid);
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
    }
  }

  function getBlid() {
    return $this->blid;
  }
}

?>
