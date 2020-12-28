<?php
namespace Glass;

class ScreenshotObject {
	public $id;
	public $blid;
	public $name;
	public $filename;
	public $description;
	public $url;
	public $thumburl;
	public $x;
	public $y;
	public $ext;

	public function __construct($resource) {
		$this->id = intval($resource->id);
		$this->blid = intval($resource->blid);
		$this->name = $resource->name;
		$this->filename = $resource->filename;
		$this->description = $resource->description;
		$this->url = urlencode(AWSFileManager::getBucket()) . "/screenshots/" . $this->id;
		$this->thumburl = urlencode(AWSFileManager::getBucket()) . "/screenshots/thumb/" . $this->id;
		$this->x = $resource->x;
		$this->y = $resource->y;
		$this->ext = @$resource->ext;
	}

	public function getID() {
		return $this->id;
	}

	public function getX() {
		return $this->x;
	}

	public function getY() {
		return $this->y;
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

	public function getUrl() {
		return $this->url;
	}

	public function getThumbUrl() {
		return $this->thumburl;
	}
}
?>
