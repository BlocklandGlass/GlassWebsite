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
}
?>