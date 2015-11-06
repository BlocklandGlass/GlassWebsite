<?php
require_once(dirname(__FILE__) . "/AddonManager.php");

//I bascially went around in circles trying to decide how to organize this
//and ended up with basically the original version
//here it is purely a data storage class
class BoardObject {
	private $id;
	private $name;
	private $icon;
	private $subCategory;
	private $count;

	public function __construct($resource) {
		$this->id = $resource->id;
		$this->name = $resource->name;
		$this->icon = $resource->icon;
		$this->subCategory = $resource->sub;
		$this->count => AddonManager::getCountFromBoard($this->id);
	}

	function getId() {
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
