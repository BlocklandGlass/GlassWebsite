<?php
class ScreenshotObject {
	public $id;
	public $blid;
	public $name;
	public $filename;
	public $description;
	public $url;
	public $thumburl;

	public function __construct($resource) {
		$this->id = intval($resource->id);
		$this->blid = intval($resource->blid);
		$this->name = $resource->name;
		$this->filename = $resource->filename;
		$this->description = $resource->description;
		$this->url = "https://s3.amazonaws.com/" . urlencode(AWSFileManager::getBucket()) . "/screenshots/" . $this->id;
		$this->thumburl = "https://s3.amazonaws.com/" . urlencode(AWSFileManager::getBucket()) . "/screenshots/thumb/" . $this->id;
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

	public function getUrl() {
		return $this->url;
	}

	public function getThumbUrl() {
		return $this->url;
	}
}
?>
