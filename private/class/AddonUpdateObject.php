<?php
/**
 * Contains class definition AddonUpdateObject
 */

namespace Glass;

use Glass\AddonManager;
use Glass\AWSFileManager;
require_once(realpath(dirname(__DIR__) . '/lib/class.Diff.php'));

/**
 * Data container for an add-on update
 */
class AddonUpdateObject {

	/** @var int Update id */
	public $id;

	/** @var string Date submitted (MySQL datetime) */
	public $submitted;

	/** @var int Update status (0 = pending, 1 = approved, -1 = rejected) */
	public $status;

	/** @var string Change-log (TML) */
	public $changelog;

	/** @var string SemVer version */
	public $version;

	/** @var int Add-on id */
	public $aid;


	/**
	 * Constructs AddonUpdateObject from an objectified MySQL result
	 * @param stdClass $row Objectified MySQL result
	 */
  public function __construct($row) {
		$this->id = $row->id;
		$this->aid = $row->aid;
		$this->submitted = $row->submitted;
		$this->status = $row->approved;
		$this->changelog = $row->changelog;
		$this->version = $row->version;
		$this->file = $row->tempfile;
		$this->restart = $row->restart;
  }

	/**
	 * Gets the time the update was submitted
	 * @return string Time submitted (MySQL datetime)
	 */
	public function getTimeSubmitted() {
		return $this->submitted;
	}

	/**
	 * Gets the AddonObject associated with the update
	 * @return AddonObject
	 */
	public function getAddon() {
		return AddonManager::getFromId($this->aid);
	}

	/**
	 * Gets the update's id
	 * @return [type] [description]
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * The local file location
	 * @return string Full file path
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * Gets the file relative to the filebin directory
	 * @return string File path relative to filebin
	 */
	public function getFileBin() {
		$idx = strpos(realpath($this->file), "filebin/");
		$bin = substr($this->file, $idx+8);
		return $bin;
	}

	/**
	 * The version updating to
	 * @return string SemVer version
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * Gets the changelog
	 * @return string TML changelog
	 */
	public function getChangeLog() {
		return $this->changelog;
	}

	/**
	 * Checks if the update is pending
	 * @return boolean
	 */
	public function isPending() {
		return $this->status == null;
	}

	/**
	 * Checks if the update is approved
	 * @return boolean
	 */
	public function isApproved() {
		return $this->status == 1;
	}

	/**
	 * Checks if the update requires a restart
	 * @return boolean
	 */
	public function isRestart() {
		return $this->restart;
	}

	/**
	 * Gets a list of the new files in this version
	 * @return string[] List of new files
	 */
	public function getNewFiles() {
		$fileNew = $this->getFile();
    $fileOld = AddonManager::getLocalFile($this->aid);

		$zipNew = new \ZipArchive();
		$zipOld = new \ZipArchive();
    $resNew = $zipNew->open($fileNew);
    $resOld = $zipOld->open($fileOld);

    if($resNew === TRUE && $resOld === TRUE) {
			$newFiles = array();
      for ($i = 0; $i < $zipNew->numFiles; $i++) {
        $newFiles[] = $zipNew->getNameIndex($i);
      }

			$oldFiles = [];
      for ($i = 0; $i < $zipOld->numFiles; $i++) {
        $oldFiles[] = $zipOld->getNameIndex($i);
      }

			$added = array_diff($newFiles, $oldFiles);
			return $added;
    } else {
      return [
				"status" => "error",
				"new" => $resNew,
				"old" => $resOld
			];
    }
	}

	/**
	 * Gets a list of the removed files in this version
	 * @return string[] List of removed files
	 */
	public function getRemovedFiles() {
		$fileNew = $this->getFile();
    $fileOld = AddonManager::getLocalFile($this->aid);

		$zipNew = new \ZipArchive();
		$zipOld = new \ZipArchive();
    $resNew = $zipNew->open($fileNew);
    $resOld = $zipOld->open($fileOld);

    if($resNew === TRUE && $resOld === TRUE) {
			$newFiles = array();
      for ($i = 0; $i < $zipNew->numFiles; $i++) {
        $newFiles[] = $zipNew->getNameIndex($i);
      }

			$oldFiles = [];
      for ($i = 0; $i < $zipOld->numFiles; $i++) {
        $oldFiles[] = $zipOld->getNameIndex($i);
      }

			$removed = array_diff($oldFiles, $newFiles);
			$removed = array_diff($removed, ['glass.json', 'version.json', 'namecheck.txt']);
			return $removed;
    } else {
      return [
				"status" => "error",
				"new" => $resNew,
				"old" => $resOld
			];
    }
	}

	/**
	 * Gets the file differences in the update
	 * @return string[] Array of differences (added array, removed array, diff array)
	 * @depreciated
	 */
	public function getDiff() {
    $fileNew = realpath($this->getFile());
    $fileOld = dirname(__DIR__) . '/../addons/files/local/' . $this->aid . '.zip';

		if(!is_file($fileOld)) {
			$path = realpath(dirname(__DIR__) . '/../addons/files/local/');
			$fh = fopen($path . '/' . $this->aid . '.zip', 'w');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://" . AWSFileManager::getBucket() . "/addons/" . $this->aid . "_1");
			curl_setopt($ch, CURLOPT_FILE, $fh);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // this will follow redirects
			curl_exec($ch);
			curl_close($ch);
			fclose($fh);
		}

    $fileOld = realpath(dirname(__DIR__) . '/../addons/files/local/' . $this->aid . '.zip');

    $zipNew = new \ZipArchive();
		$zipOld = new \ZipArchive();
    $resNew = $zipNew->open($fileNew);
    $resOld = $zipOld->open($fileOld);
    if($resNew === TRUE && $resOld === TRUE) {
			$newFiles = array();
      for ($i = 0; $i < $zipNew->numFiles; $i++) {
        $newFiles[] = $zipNew->getNameIndex($i);
      }

			$oldFiles = [];
      for ($i = 0; $i < $zipOld->numFiles; $i++) {
        $oldFiles[] = $zipOld->getNameIndex($i);
      }

			$added = array_diff($newFiles, $oldFiles);
			$removed = array_diff($oldFiles, $newFiles, ["glass.json", "version.json", "namecheck.txt"]);
			$commonFiles = array_intersect($newFiles, $oldFiles);
			$commonFiles = array_diff($commonFiles, ["glass.json", "version.json", "namecheck.txt"]);
			$diff = [];
			foreach($commonFiles as $fi) {
				if(strpos($fi, ".cs") == strlen($fi)-3) {
					$newStr = $zipNew->getFromName($fi);
					$oldStr = $zipOld->getFromName($fi);
					if(trim($newStr) != trim($oldStr)) {
						$diff[$fi] = Diff::toTable(Diff::compare($oldStr, $newStr));
					}
				}
			}
			$ret = ["added" => $added, "removed" => $removed, "changes" => $diff];
			return $ret;
    } else {
      return false;
    }
	}
}
?>
