<?php
require_once(realpath(dirname(__FILE__) . '/AddonManager.php'));
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
}
?>
