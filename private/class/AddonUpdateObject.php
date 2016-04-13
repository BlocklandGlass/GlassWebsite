<?php
require_once(realpath(dirname(__FILE__) . '/AddonManager.php'));
require_once(realpath(dirname(__DIR__) . '/lib/class.Diff.php'));
class AddonUpdateObject {
	//public fields are ones automatically converted to json
	public $id;
	public $submitted;
	public $status;
	public $changelog;
	public $version;
	public $aid;

  public function __construct($row) {
		$this->id = $row->id;
		$this->aid = $row->aid;
		$this->submitted = $row->submitted;
		$this->status = $row->approved;
		$this->changelog = $row->changelog;
		$this->version = $row->version;
		$this->file = $row->tempfile;
  }

	public function getAddon() {
		return AddonManager::getFromId($this->aid);
	}

	public function getId() {
		return $this->id;
	}

	public function getFile() {
		return $this->file;
	}


	public function getDiff() {
    $fileNew = realpath($this->getFile());
    $fileOld = realpath(dirname(__DIR__) . '/../addons/files/local/' . $this->aid . '.zip');

    $zipNew = new ZipArchive();
		$zipOld = new ZipArchive();
    $resNew = $zipNew->open($fileNew);
    $resOld = $zipOld->open($fileOld);
    if($resNew === TRUE && $resOld === TRUE) {
			$newFiles = [];
      for ($i = 0; $i < $zipNew->numFiles; $i++) {
        $newFiles[] = $zipNew->getNameIndex($i);
      }

			$oldFiles = [];
      for ($i = 0; $i < $zipOld->numFiles; $i++) {
        $oldFiles[] = $zipOld->getNameIndex($i);
      }

			$added = array_diff($newFiles, $oldFiles);
			$removed = array_diff($oldFiles, $newFiles, ["glass.json", "version.json"]);
			$commonFiles = array_intersect($newFiles, $oldFiles);
			$diff = [];
			foreach($commonFiles as $fi) {
				$diff[$fi] = Diff::toTable(Diff::compare($zipOld->getFromName($fi), $zipNew->getFromName($fi)));
			}
			return ["added" => $added, "removed" => $removed, "changes" => $diff];
    } else {
      return false;
    }

	}
}
?>
