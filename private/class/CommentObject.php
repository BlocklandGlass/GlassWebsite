<?php
class CommentObject {
	//these are public because this makes them instantly convertible to json
	//this is purely a data storage class anyway
	public $id;
	public $blid;
	public $aid;
	public $comment;
	public $timestamp;
	public $lastedit;

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

	public function getAID() {
		return $this->aid;
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
