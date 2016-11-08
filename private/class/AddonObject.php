<?php
require_once(realpath(dirname(__FILE__) . "/DependencyManager.php"));
require_once(realpath(dirname(__FILE__) . "/StatManager.php"));

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
	public $rating;
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
		//$this->webDownloads = intval($resource->webDownloads);
		//$this->ingameDownloads = intval($resource->ingameDownloads);
		//$this->updateDownloads = intval($resource->updateDownloads);
		//$this->downloads = $this->webDownloads + $this->ingameDownloads + $this->updateDownloads;
		//print_r($resource);
		$this->id = intval($resource->id);
		$this->board = intval($resource->board);
		$this->blid = intval($resource->blid);
		$this->name = $resource->name;
		$this->description = $resource->description;
		$this->approved = intval($resource->approved);
		//$this->rating = floatval($resource->rating);
		$this->version = $resource->version;
		$this->authorInfo = json_decode($resource->authorInfo);
		//$this->file = intval($resource->file);

		$this->filename = $resource->filename;
		$this->deleted = intval($resource->deleted);
		$this->reviewInfo = json_decode($resource->reviewInfo);

		$this->betaVersion = $resource->betaVersion;

		$this->rating = $resource->rating;

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

	public function getName() {
		return $this->name;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getApproved() {
		return $this->approved == 1;
	}

	public function getRating() {
		return $this->rating;
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

	//public function getRating() {
	//	return $this->rating;
	//}

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

	public function getAuthorInfo() {
		return $this->authorInfo;
	}

	public function getManagerBLID() {
		$authorInfo = $this->getAuthorInfo();
		if(is_array($authorInfo)) {
			foreach($authorInfo as $author) {
				if($author->main) {
					return $author->blid;
				}
			}
		} else {
			return false;
		}
	}

	public function getFileName() {
		return $this->filename;
	}

	public function getDeleted() {
		return $this->deleted;
	}

	//public function getFile() {
	//	return $this->file;
	//}
    //
	//public function getDependencies() {
	//	return $this->dependencies;
	//}
    //
	//public function getWebDownloads() {
	//	return $this->downloads_web;
	//}
    //
	//public function getIngameDownloads() {
	//	return $this->downloads_ingame;
	//}
    //
	//public function getUpdateDownloads() {
	//	return $this->downloads_update;
	//}

	public function getReviewInfo() {
		return $this->reviewInfo;
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
}

?>
