<?php
//direct port, needs works
require_once dirname(__DIR__) . '/DatabaseManager.php';
require_once dirname(__DIR__) . '/UserManager.php';

class ApiSessionManager {
	protected $remoteVerified = false; //auth.blockland.us
	protected $verified = false; //local database

	protected $blid;
	protected $name;
	protected $sid;
	protected $ip;

	protected $version;

	function __construct($sid) {
		global $_SESSION, $_SERVER;
		if($sid !== "") {
			session_id($sid);
		}
		session_start();

		if(@$_SESSION['verified']) {
			$this->verified = true;
		} else {
			$this->verified = false;
		}

		$this->blid = @$_SESSION['blid'];
		$this->name = @$_SESSION['username'];
		$this->remoteVerified = @$_SESSION['remoteVerified'];
		$this->ip = @$_SERVER['REMOTE_ADDR'];

		$this->version = @$_SESSION['version'];

		$db = new DatabaseManager();
		$db->query("UPDATE  `blocklandGlass`.`ingame_sessions` SET  `lastactive` = NOW( ) WHERE  `ingame_sessions`.`sessionid` =  '" . session_id() . "';");
	}

	function __destruct() {
		global $_SESSION;
		$_SESSION['remoteVerified'] = $this->remoteVerified;
		$_SESSION['blid'] = $this->blid;
		$_SESSION['username'] = $this->name;
		$_SESSION['verified'] = $this->verified;


		$_SESSION['version'] = $this->version;
	}

	public function getBLID() {
		return $this->blid;
	}

	public function getUsername() {
		return $this->name;
	}

	public function getSiteAccount() {
		return UserManager::getFromBlid($this->blid);
	}

	public function setVersion($ver) {
		$this->version = $ver;
	}

	public function getVersion() {
		return $this->version;
	}

	public function isVerified() {
		if(!$this->verified) {
			$db = new DatabaseManager();
			$result = $db->query("SELECT * FROM `users` WHERE blid=" . $this->blid);
			if(!is_object($result)) {
				throw new Exception("Account doesn't exist! (" . $this->blid . ")");
				//return false;
			} else {
				$obj = $result->fetch_object();
				if($obj->verified) {
					$this->verified = true;
					return true;
				} else {
					return false;
				}
			}
		} else {
			return true;
		}
	}

	public function attemptRemoteVerification($name) {
		$this->name = $name;
		return $this->isRemoteVerified();
	}

	public function isRemoteVerified() {
		if(!$this->remoteVerified) {
			$url = 'http://auth.blockland.us/authQuery.php';
			$data = array('NAME' => $this->name, 'IP' => $this->ip);
			$options = array(
			        'http' => array(
			        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			        'method'  => 'POST',
			        'content' => http_build_query($data),
			    )
			);

			$context  = stream_context_create($options);
			$result = file_get_contents($url, false, $context);

			if(strpos($result, "NO") === 0) {
				return false;
			} else if(strpos($result, "YES") === 0) {
				$words = explode(" ", $result);
				$this->onAccountRemoteVerified($words[1]);
				return true;
			} else if(strpos($result, "ERROR") === 0) {
				return false;
			} else {
				throw new Exception("invalid auth response");
			}
		} else {
			return true;
		}
	}

	public function onVerificationSuccess() {
		$db = new DatabaseManager();

		$db->query("UPDATE `blocklandGlass`.`users` SET `verified`=true WHERE blid='" . $db->sanitize($this->getBlid()) . "'");
	}

	protected function onAccountRemoteVerified($blid) {
		//echo "remote success " . $blid;
		$this->remoteVerified = true;
		$this->blid = $blid;

		//officially start session
		$db = new DatabaseManager();
		$db->query("INSERT INTO  `blocklandGlass`.`ingame_sessions` (`blid`, `sessionid`, `start`, `lastactive`, `version`)
			VALUES ('" . $this->getBlid() . "', '" . session_id() . "', NOW( ) , CURRENT_TIMESTAMP, '" . $db->sanitize($this->getVersion()) . "');");

	}
}
?>
