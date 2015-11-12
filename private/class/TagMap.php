<?php
//not actually used
//should probably delete this

class TagMap {
	private $id;

	public $addonID;
	public $tagID;

	public function __construct($resource) {
		$this->id = $resource->id;
		$this->addonID = $resource->aid;
		$this->tagID = $resource->tid;
	}

	public function getID() {
		return $this->id;
	}

	public function getAddonID() {
		return $this->addonID;
	}

	public function getTagID() {
		return $this->TagID;
	}
}
?>
