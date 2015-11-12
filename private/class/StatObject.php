<?php
//require_once(realpath(dirname(__FILE__) . "/StatManager.php"));

class StatObject {
	public $id;
	public $date;
	public $users;
	public $addons;
	public $downloads;
	public $groups;
	public $comments;
	public $builds;
	public $tags;
	public $topAddonID;
	public $topAddonDownloads;
	public $topTagID;
	public $topTagDownloads;
	public $topBuildID;
	public $topBuildDownloads;

	public function __construct($resource) {
		$this->id = $resource->id;
		$this->date = $resource->date;
		$this->users = $resource->users;
		$this->addons = $resource->addons;
		$this->downloads = $resource->downloads;
		$this->groups = $resource->groups;
		$this->comments = $resource->comments;
		$this->builds = $resource->builds;
		$this->tags = $resource->tags;

		//I would like to be able to do this in a loop but it seems I would need to use fetch_assoc or fetch_row
		$this->topAddonID = [
			0 => $resource->addon0,
			1 => $resource->addon1,
			2 => $resource->addon2,
			3 => $resource->addon3,
			4 => $resource->addon4,
			5 => $resource->addon5,
			6 => $resource->addon6,
			7 => $resource->addon7,
			8 => $resource->addon8,
			9 => $resource->addon9
		];
		$this->topAddonDownloads = [
			0 => $resource->addonDownloads0,
			1 => $resource->addonDownloads1,
			2 => $resource->addonDownloads2,
			3 => $resource->addonDownloads3,
			4 => $resource->addonDownloads4,
			5 => $resource->addonDownloads5,
			6 => $resource->addonDownloads6,
			7 => $resource->addonDownloads7,
			8 => $resource->addonDownloads8,
			9 => $resource->addonDownloads9
		];
		$this->topTagID = [
			0 => $resource->tag0,
			1 => $resource->tag1,
			2 => $resource->tag2,
			3 => $resource->tag3,
			4 => $resource->tag4
		];
		$this->topTagDownloads = [
			0 => $resource->tagDownloads0,
			1 => $resource->tagDownloads1,
			2 => $resource->tagDownloads2,
			3 => $resource->tagDownloads3,
			4 => $resource->tagDownloads4
		];
		$this->topBuildID = [
			0 => $resource->build0,
			1 => $resource->build1,
			2 => $resource->build2,
		];
		$this->topBuildDownloads = [
			0 => $resource->buildDownloads0,
			1 => $resource->buildDownloads1,
			2 => $resource->buildDownloads2,
		];
	}

	public function getID() {
		return $this->id;
	}
}
?>
