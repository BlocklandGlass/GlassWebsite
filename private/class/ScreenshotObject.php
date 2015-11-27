<?php
class ScreenshotObject {
	public $id;
	public $blid;
	public $name;
	public $filename;
	public $description;

	public function __construct($resource) {
		$this->id = intval($resource->id);
		$this->blid = intval($resource->blid);
		$this->name = $resource->name;
		$this->filename = $resource->filename;
		$this->description = $resource->description;
	}

	public function getID() {
		return $this->id;
	}

	public function getAuthor() {
		return $this->getBLID();
	}

	public function getBLID() {
		return $this->blid;
	}

	public function getTitle() {
		return $this->getName();
	}

	public function getName() {
		return $this->name;
	}

	public function getDescription() {
		return $this->description;
	}
}
?>
