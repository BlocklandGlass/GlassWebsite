<?php
namespace Glass;

require_once(realpath(dirname(__FILE__) . "/DependencyManager.php"));
require_once(realpath(dirname(__FILE__) . "/StatManager.php"));
require_once(realpath(dirname(__FILE__) . "/TML.php"));

//this should be the only class to interact with `addon_files` ?
//Actually it should probably be a purely data class to follow suit with others

class AddonObject {
	//public fields are ones automatically converted to json
	public $id;
	public $board;
	public $blid;
	public $name;
	public $description;
	//public $downloads;
	public $approved;
	public $version;
	public $authorInfo;
	//public $file;
	//public $dependencies;
	public $uploadDate;
	public $url;

	private $filename;
	private $deleted;
	//private $webDownloads;
	//private $ingameDownloads;
	//private $updateDownloads;
	private $reviewInfo;
	private $betaVersion;

	public function __construct($resource) {

		$this->id = intval($resource->id);
		$this->board = intval($resource->board);
		$this->blid = intval($resource->blid);
		$this->name = $resource->name;
		$this->description = $resource->description;
		$this->approved = intval($resource->approved);
		$this->version = $resource->version;

		$this->filename = $resource->filename;
		$this->deleted = intval($resource->deleted);

		$this->betaVersion = $resource->betaVersion;

		$this->uploadDate = $resource->uploadDate;
		$this->url = "https://s3.amazonaws.com/" . urlencode(AWSFileManager::getBucket()) . "/addons/" . $this->id;
	}

	public function getID() {
		return $this->id;
	}

	public function getBoard() {
		return $this->board;
	}

	public function getBLID() {
		return $this->blid;
	}

	public function getAuthor() {
		return UserManager::getFromBlid($this->blid);
	}

	public function getName() {
		return $this->name;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getApproved() {
		return $this->approved == 1;
	}

	public function isRejected() {
		return $this->approved == -1;
	}

	public function getRejectReason() {
		if(isset($this->reviewInfo->rejectReason)) {
			return $this->reviewInfo->rejectReason;
		} else {
			return "";
		}
	}

	public function hasBeta() {
		return $this->betaVersion !== null;
	}

	public function getBetaVersion() {
		return $this->betaVersion;
	}

	public function getVersion() {
		if($this->version == "")
			$this->version = "0.0.0";
		return $this->version;
	}

	public function getRestartVersion() {
		$ups = AddonManager::getUpdates($this);
		foreach($ups as $up) {
			if($up->isRestart()) {
				return $up->getVersion();
			}
		}

		return "0";
	}

	public function getManagerBLID() {
		return $this->blid;
	}

	public function getFileName() {
		return $this->filename;
	}

	public function getDeleted() {
		return $this->deleted;
	}

	public function getDependencies() {
		return DependencyManager::getDependenciesFromAddonID($this->id);
	}

	public function getTotalDownloads() {
		return StatManager::getTotalAddonDownloads($this->id);
	}

	public function getDownloads($type) {
		return StatManager::getAddonDownloads($this->id, $type);
	}

	public function getUploadDate() {
		return $this->uploadDate;
	}

	public function getURL() {
		return $this->url;
	}

	public function getScreenshots() {
		return ScreenshotManager::getScreenshotsFromAddon($this->id);
	}

	public function getDescriptionTML() {
		return TML::format($this->description);
	}
}

?>
