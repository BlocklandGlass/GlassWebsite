<?php
class BuildObject {
	public $id;
	public $blid;
	public $name;
	public $bricks;
	public $description;

	private $filename;

	public function __construct($resource) {
		$this->id = intval($resource->id);
		$this->blid = intval($resource->blid);
		$this->name = $resource->name;
		$this->bricks = intval($resource->bricks);
		$this->description = $resource->description;

		//use realpath?
		//depends on filesystem api I suppose
		$this->filename = $resource->filename;
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

	public function getName() {
		return $this->name;
	}

	public function getBrickCount() {
		return $this->getBricks();
	}

	public function getBricks() {
		return $this->bricks;
	}

	public function getDescription() {
		return $this->description;
	}
}
?>
