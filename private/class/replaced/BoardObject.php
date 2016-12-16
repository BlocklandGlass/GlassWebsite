<?php
require_once dirname(__FILE__) . "/DatabaseManager.php";

class BoardObject {
	private $image;
	private $name;
	private $numberOfAddons;
	private $id;
	private $subCategory;

	function __construct($id) {
		$this->id = $id;
		$db = new DatabaseManager();
		$res = $db->query("SELECT * FROM `addon_boards` WHERE id='" . $db->sanitize($id) . "'");
		if(is_object($res) && is_object($obj = $res->fetch_object())) {
			$this->image = $obj->icon;
			$this->name = $obj->name;
			$this->subCategory = $obj->subcategory;
		} else {
			throw new \Exception('Invalid board id');
		}
	}

	function getSubCategory() {
		return $this->subCategory;
	}

	function getAddons($offset, $limit) {
		if(isset($limit)) {
			return AddonManager::getFromBoardId($this->id, false, $limit, $offset);
		} else {
			return AddonManager::getFromBoardId($this->id);
		}
	}

	function getId() {
		return $this->id;
	}

	function getImage() {
		return $this->image;
	}

	function getName() {
		return $this->name;
	}

	function getCount() {
		if(!isset($numberOfAddons)) {
			$db = new DatabaseManager();
			$res = $db->query("SELECT COUNT(*) FROM `addon_addons` WHERE board='" . $db->sanitize($this->id) . "'  AND deleted=0");
			$this->numberOfAddons = $res->fetch_row()[0];
		}

		return $this->numberOfAddons;
	}
}
?>
