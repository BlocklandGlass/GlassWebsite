<?php
require_once(dirname(__FILE__) . "/AddonManager.php");

//I bascially went around in circles trying to decide how to organize this
//and ended up with basically the original version
//here it is purely a data storage class
class BoardObject {
	public $id;
	public $name;
	public $icon;
	public $subCategory;
	public $count;

	public function __construct($resource) {
		$this->id = intval($resource->id);
		$this->name = $resource->name;
		$this->icon = $resource->icon;
		$this->subCategory = $resource->subCategory;
		$this->count = AddonManager::getCountFromBoard($this->id);
	}

	function getID() {
		return $this->id;
	}

	function getName() {
		return $this->name;
	}

	function getIcon() {
		return $this->icon;
	}

	function getSubCategory() {
		return $this->subCategory;
	}

	function getCount() {
		return $this->count;
	}
}
?>
