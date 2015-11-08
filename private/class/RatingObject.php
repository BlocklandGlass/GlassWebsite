<?php
class RatingObject {
	//these are public because this makes them instantly convertible to json
	//this is purely a data storage class anyway
	public $id;
	public $blid;
	public $aid;
	public $rating;

	public function __construct($resource) {
		$this->id = intval($resource->id);
		$this->blid = intval($resource->blid);
		$this->aid = intval($resource->aid);
		$this->rating = intval($resource->rating);
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

	public function getRating() {
		return $this->rating;
	}
}
?>