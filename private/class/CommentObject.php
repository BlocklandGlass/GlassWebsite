<?php
class CommentObject {
	private static $cacheTime = 180;

	//these are public because this makes them instantly convertible to json
	public $id;
	public $blid;
	public $aid;
	public $comment;
	public $timestamp;
	public $lastedit;

	public static function getCacheTime() {
		return CommentObject::$cacheTime;
	}

	public function __construct($resource) {
		$this->id = intval($resource->id);
		$this->blid = intval($resource->blid);
		$this->aid = intval($resource->aid);
		$this->comment = $resource->comment;
		$this->timestamp = $resource->timestamp;
		$this->lastedit = $resource->lastedit;
	}

	public function getID() {
		return $this->id;
	}

	public function getBLID() {
		return $this->blid;
	}

	public function getComment() {
		return $this->comment;
	}

	public function getTimeStamp() {
		return $this->timestamp;
	}

	public function getLastEdit() {
		return $this->lastedit;
	}
}
?>
