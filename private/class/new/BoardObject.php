<?php
require_once dirname(__FILE__) . "/DatabaseManager.php";

//a bit of a circular dependency, but require_once should handle that
require_once dirname(__FILE__) . "/BoardManager.php";

class BoardObject {
	private $image;
	private $name;
	private $numberOfAddons;
	private $id;
	private $subCategory;

	function __construct($id) {
		//only BoardManager should be accessing addon_boards
		$indexData = BoardManager::getBoardIndexFromId($id);
		$this->id = $id;
		$this->name = $indexData["name"];
		$this->icon = $indexData["icon"];
		$this->subCategory = $indexData["subCategory"];
		$this->numberOfAddons = $this->getCount();
	}

	function getSubCategory() {
		//I think we should eliminate sub-categories by condensing existing ones and adding tags
		//return "All Boards";
		return $this->subCategory;
	}

	function getAddons($offset, $limit) {
		if(isset($limit)) {
			return AddonManager::getFromBoardId($this->id, false, $limit, $offset);
		} else {
			return AddonManager::getFromBoardId($this->id);
		}
	}

	function getId() {
		return $this->id;
	}

	function getImage() {
		return $this->image;
	}

	function getName() {
		return $this->name;
	}

	function getCount() {
		if(!isset($this->numberOfAddons)) {

			$database = new DatabaseManager();
			$this->verifyTable($database);
			$resource = $database->query("SELECT COUNT(*) FROM `addon_addons` WHERE board='" . $database->sanitize($this->id) . "'  AND deleted=0");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}
			$this->numberOfAddons = $resource->fetch_row()[0];
			$resource->close();

		}
		return $this->numberOfAddons;
	}

	private function verifyTable($database) {
		/*TO DO:
			screenshots
			tags
			approval info should probably be in a different table
			do we really need stable vs testing vs dev?
			bargain/danger should probably be boards
			figure out how data is split between addon and file
		*/
		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_addons` (
			id INT AUTO_INCREMENT,
			board INT NOT NULL,
			author INT NOT NULL,
			name VARCHAR(30) NOT NULL,
			filename TEXT NOT NULL,
			description TEXT NOT NULL DEFAULT '',
			file INT NOT NULL,
			deleted TINYINT NOT NULL DEFAULT 0,
			dependencies TEXT NOT NULL DEFAULT '',
			downloads_web INT NOT NULL DEFAULT 0,
			downloads_ingame INT NOT NULL DEFAULT 0,
			downloads_update INT NOT NULL DEFAULT 0,
			updaterInfo TEXT NOT NULL,
			approvalInfo TEXT NOT NULL,
			PRIMARY KEY (id))")) {
			throw new Exception("Failed to create table addon_addons: " . $database->error());
		}
	}
}
?>
