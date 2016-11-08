<?php
require_once(realpath(dirname(__FILE__) . "/StatManager.php"));

class StatObject {
	public $id;
	public $date;
	public $users;
	public $addons;
	public $downloads;
	public $groups;
	public $comments;
	public $builds;
	public $topAddonID;
	public $topAddonDownloads;
	public $topBuildID;
	public $topBuildDownloads;

	public function __construct($resource) {
		$this->id = intval($resource->id);
		$this->date = $resource->date;
		$this->users = intval($resource->users);
		$this->addons = intval($resource->addons);
		$this->downloads = intval($resource->downloads);
		$this->groups = intval($resource->groups);
		$this->comments = intval($resource->comments);
		$this->builds = intval($resource->builds);

		$this->topAddonID = [];
		$this->topAddonDownloads = [];
		$this->topBuildID = [];
		$this->topBuildDownloads = [];

		for($i=0; $i<StatManager::$addonCount; $i++) {
			$this->topAddonID[$i] = intval($resource->{'addon' . $i});
			$this->topAddonDownloads[$i] = intval($resource->{'addonDownloads' . $i});
		}

		for($i=0; $i<StatManager::$buildCount; $i++) {
			$this->topBuildID[$i] = intval($resource->{'build' . $i});
			$this->topBuildDownloads[$i] = intval($resource->{'buildDownloads' . $i});
		}
	}

	public function getID() {
		return $this->id;
	}

	//honestly these fields should just be accessed directly
}
?>
