<?php
class UserObject {
	//public fields will automatically be put into json
	public $username;
	public $blid;
	public $banned;
	public $admin;

	private $verified;
	private $email;
	//private $profile;

	public function __construct($resource) {
		$this->username = $resource->username;
		$this->blid = intval($resource->blid);
		$this->banned = intval($resource->banned);
		$this->admin = intval($resource->admin);
		$this->verified = intval($resource->verified);
		$this->email = $resource->email;
		//$this->profile = $resource->profile;
	}

	public function getName() {
		return $this->getUserName();
	}

	public function getUserName() {
		return $this->username;
	}

	public function getID() {
		return $this->getBLID();
	}

	public function getBLID() {
		return $this->blid;
	}

	public function getBanned() {
		return $this->banned;
	}

	public function getAdmin() {
		return $this->admin;
	}

	public function getVerified() {
		return $this->verified;
	}

	public function getEmail() {
		return $this->email;
	}

	//this should be done in the UserManager class
	//the *Object classes are just for data storage
	//make sure this also checks for whether that blid is already verified with a different email
	public function setVerified($bool) {
		$database = new DatabaseManager();
		$database->query("UPDATE `users` SET `verified`='" . $database->sanitize($bool) . "' WHERE `email`='" . $database->sanitize($this->getEmail()) . "'");
		apc_store('userObject_' . $this->blid, $this, 600);
	}

	public function setUsername($name) {
		if($this->verified) {
			$database = new DatabaseManager();
			$database->query("UPDATE `users` SET `username`='" . $database->sanitize($name) . "' WHERE `email`='" . $database->sanitize($this->getEmail()) . "'");
			apc_store('userObject_' . $this->blid, $this, 600);
		}
	}
}
?>
