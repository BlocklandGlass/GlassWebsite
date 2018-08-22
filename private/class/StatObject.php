<?php
namespace Glass;

require_once(realpath(dirname(__FILE__) . "/StatManager.php"));

class StatObject {
	public $id;
	public $date;
	public $users;
	public $addons;
	public $downloads;
	public $groups;
	public $comments;
	public $topAddonID;
	public $topAddonDownloads;

	public function __construct($resource) {
		$this->id = intval($resource->id);
		$this->date = $resource->date;
		$this->users = intval($resource->users);
		$this->addons = intval($resource->addons);
		$this->downloads = intval($resource->downloads);
		$this->groups = intval($resource->groups);
		$this->comments = intval($resource->comments);

		$this->topAddonID = [];
		$this->topAddonDownloads = [];

		for($i=0; $i<StatManager::$addonCount; $i++) {
			$this->topAddonID[$i] = intval($resource->{'addon' . $i});
			$this->topAddonDownloads[$i] = intval($resource->{'addonDownloads' . $i});
		}
	}

	public function getID() {
		return $this->id;
	}

	//honestly these fields should just be accessed directly
}
?>
