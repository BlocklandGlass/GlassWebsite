<?php
class BoardObject {
	public $id;
	public $name;
	public $icon;
	public $description;

	public function __construct($resource) {
		$this->id = intval($resource->id);
		$this->name = $resource->name;
		$this->icon = $resource->icon;
		$this->description = $resource->description;
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

	function getDescription() {
		return $this->description;
	}

	function getCount() {
		require_once(dirname(__FILE__) . "/AddonManager.php");
		return AddonManager::getCountFromBoard($this->id);
	}
}
?>
