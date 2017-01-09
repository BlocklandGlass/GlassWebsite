<?php
require_once(dirname(__FILE__).'/DatabaseManager.php');
//use Glass\UserGroupData;

class UserHandler {
	private $initialized = false;
	private $username;
	private $blid;
	private $id;
	private $groupData;


	//recommend to choose "ID" or "Id" and stick with it
	public function initFromId($id) {
		$db = new DatabaseManager();

		$result = $db->query("SELECT `username`,`blid`,`id`,`groups` FROM `users` WHERE id='" . $db->sanitize($id) . "';");
		if($obj = $result->fetch_object()) {
			$initialized = true;
			$this->setUsername($obj->username);
			$this->setBLID($obj->blid);
			$this->setID($obj->id);
      // TODO I want to rework the group logic, doesn't need it's own class.
			//$this->groupData = new UserGroupData($obj->groups);
			return true;
		} else {
			throw new \Exception("blid not registered");
				return false;
		}
	}

	public function initFromBLID($blid) {
		$db = new DatabaseManager();

		//maybe I'm putting condoms on condoms
		$result = $db->query("SELECT `username`,`blid`,`id`,`groups` FROM `users` WHERE blid='" . $db->sanitize($blid) . "';");
		if($obj = $result->fetch_object()) {
			$initialized = true;
			$this->setUsername($obj->username);
			$this->setBLID($obj->blid);
			$this->setID($obj->id);
			//$this->groupData = new UserGroupData($obj->groups);
			return true;
		} else {
			throw new \Exception("blid not registered");
			return false;
		}
	}

	public function submitReviewerApp() {
    // TODO Should this be a user function or handled seperately?
	//I think all the code involving reviews and approval should be in one location
		$db = new DatabaseManager();

		$db->query("INSERT INTO `user_reviewer_app` (uid, submitted, response) VALUES ('"
		 . $db->sanitize($this->getId()) . "', NOW() , '')");
	}


	public function updateDatabase() {
		$db = new DatabaseManager();

		$db->query("INSERT INTO `users` (username, id, blid, groups) VALUES ('"
		 . $db->sanitize($this->getUsername()) . "', '"
		 . $db->sanitize($this->getID()) . "', '"
		 . $db->sanitize($this->getBLID()) . "', '"
		 . $db->sanitize($this->groupData->toJSON()) . "')"

		 . " ON DUPLICATE KEY "
		 . "UPDATE groups='" . $db->sanitize($this->groupData->toJSON()) . "'");
	}

	public function getGroupData() {
		return $this->groupData;
	}

	public function isInit() {
		return $this->initialized;
	}

	public function getName() {
		return $this->getUsername();
	}

	public function getUsername() {
		return $this->username;
	}

	public function setUsername($new) {
		$this->username = $new;
	}


	public function getBLID() {
		return $this->blid;
	}

	public function setBLID($new) {
		$this->blid = $new;
	}


	public function getID() {
		return $this->id;
	}

	public function setID($new) {
		$this->id = $new;
	}
}
?>
