<?php
/**
 * Class definition of AddonObjects
 */
namespace Glass;

require_once(realpath(dirname(__FILE__) . "/DependencyManager.php"));
require_once(realpath(dirname(__FILE__) . "/StatManager.php"));
require_once(realpath(dirname(__FILE__) . "/TML.php"));

/**
 * Contains data and accessors of related data for an add-on from the addon_addons table
 */
class AddonObject {
	/** @var int Add-on approval state */
	public $approved;

	/** @var int Author BLID */
	public $blid;

	/** @var int Board ID */
	public $board;

	/** @var string Add-on description */
	public $description;

	/** @var int Add-on id */
	public $id;

	/** @var string Add-on name */
	public $name;

	/** @var string Upload date, foratted by MySQL datetime */
	public $uploadDate;

	/** @var string AWS add-on URL */
	public $url;

	/** @var string SemVer version */
	public $version;


	/** @var string The add-ons intended filename */
	private $filename;

	/** @var bool Soft-deleted */
	private $deleted;

	/** @var string SemVer beta version */
	private $betaVersion;

	/**
	 * Constructs an AddonObject from a MySQL objectified result
	 *
	 * @param stdClass $resource An object (typically MySQL result) containing add-on information to construct the object from
	 */
	public function __construct($resource) {
		if(!isset($resource)) {
			throw new Exception("Invalid AddonObject construction");
		}

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

		$this->summary = $resource->summary;
	}

	/**
	 * Checks if add-on is approved
	 *
	 * @return bool
	 */
	public function getApproved() {
		return $this->approved == 1;
	}

	/**
	 * Get the author (uploader's) UserObject
	 *
	 * @return UserObject
	 */
	public function getAuthor() {
		return UserManager::getFromBlid($this->blid);
	}

	/**
	 * Returns the beta version
	 *
	 * @return string SemVer version
	 */
	public function getBetaVersion() {
		return $this->betaVersion;
	}

  /**
	 * Returns the BLID of the uploader
	 *
	 * @return int Author blid
	 */
	public function getBLID() {
		return $this->blid;
	}

	/**
	 * Returns the board id of the add-on
	 *
	 * @return int Board id
	 */
	public function getBoard() {
		return $this->board;
	}

	/**
	 * Checks if the add-on has been deleted
	 *
	 * @return bool
	 */
	public function getDeleted() {
		return $this->deleted;
	}

	/**
	 * Gets the dependancies of an add-on
	 *
	 * @return int[] Add-on ids of add-ons dependent on this
	 */
	public function getDependencies() {
		return DependencyManager::getDependenciesFromAddonID($this->id);
	}

  /**
	 * Gets the add-on description formatted in TML (Torque Markup Language)
	 *
	 * @return string
	 */
	public function getDescriptionTML() {
		return TML::format($this->description);
	}

  /**
	 * Returns the number of downloads of a particular type
	 *
	 * @param string $type The download type (web, update, ingame)
	 *
	 * @return
	 */
	public function getDownloads($type) {
		return StatManager::getAddonDownloads($this->id, $type);
	}

	/**
	 * Returns the add-on's description
	 * @return string Description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Get the add-on's intended file name
	 * @return string File name
	 */
	public function getFileName() {
		return $this->filename;
	}

	/**
	 * Return the add-on's glass add-on id
	 * @return int Add-on id
	 */
	public function getID() {
		return $this->id;
	}

	/**
	 * Gets the author's blid (Duplicate of getBLID)
	 * @return int BLID
	 */
	public function getManagerBLID() {
		return $this->blid;
	}

	/**
	 * Gets the add-on's name
	 * @return string Name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Gets the add-ons reason for rejection
	 *
	 * @return string Rejection reason
	 *
	 * @depreciated
	 */
	public function getRejectReason() {
		if(isset($this->reviewInfo->rejectReason)) {
			return $this->reviewInfo->rejectReason;
		} else {
			return "";
		}
	}

	/**
	 * Gets the latest version that requires an update restart
	 * @return string SemVer version
	 */
	public function getRestartVersion() {
		$ups = AddonManager::getUpdates($this);
		foreach($ups as $up) {
			if($up->isRestart()) {
				return $up->getVersion();
			}
		}

		return "0";
	}


	/**
	 * Gets the screenshot id's associated with the add-on
	 *
	 * @return int[] Screenshot id's associated with add-on
	 *
	 * @uses ScreenshotManager::getScreenshotFromAddon
	 */
	public function getScreenshots() {
		return ScreenshotManager::getScreenshotsFromAddon($this->id);
	}

	/**
	 * Gets a short (typically one-line) summary of an add-on
	 * @return string Add-on summary
	 */
	public function getSummary() {
		return $this->summary;
	}

	/**
	 * Gets the total downloads of the add-on (sum of all download types)
	 * @return int Downloads
	 */
	public function getTotalDownloads() {
		return StatManager::getTotalAddonDownloads($this->id);
	}

	/**
	 * Returns a list of updates for this add-on
	 *
	 * @return AddonUpdateObject[]
	 */
	public function getUpdates() {
		return AddonManager::getUpdates($this);
	}

	/**
	 * Returns the date uploaded
	 * @return string Date string (MySQL formatted)
	 */
	public function getUploadDate() {
		return $this->uploadDate;
	}

	/**
	 * Returns the AWS S3 download link
	 * @return string AWS URl
	 */
	public function getURL() {
		return $this->url;
	}

	/**
	 * Returns the add-on's version
	 * @return string SemVer version
	 */
	public function getVersion() {
		if($this->version == "")
		$this->version = "0.0.0";
		return $this->version;
	}

	/**
	 * Checks if the add-on has a beta version
	 * @return boolean
	 */
	public function hasBeta() {
		return $this->betaVersion !== null;
	}

	/**
	 * Checks if the add-on has been rejected
	 * @return boolean
	 */
	public function isRejected() {
		return $this->approved == -1;
	}
}

?>
