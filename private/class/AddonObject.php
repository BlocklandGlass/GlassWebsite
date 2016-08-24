<?php
require_once(realpath(dirname(__FILE__) . "/DependencyManager.php"));
require_once(realpath(dirname(__FILE__) . "/TagManager.php"));
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
	//public $tags
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

	public function getTags() {
		return TagManager::getTagsFromAddonID($this->id);
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
/*
require_once dirname(__FILE__) . '/DatabaseManager.php';
require_once dirname(__FILE__) . '/BoardManager.php';
//require_once dirname(__FILE__) . '/UserHandler.php';
require_once dirname(__FILE__) . '/AddonManager.php';
//require_once dirname(__FILE__) . '/FileObject.php';

//TODO This is one of the classes that needs some major cleaning
//TODO A lot of the add-on management functions need to be cleaned and organized

class AddonObject {
	private static $cacheTime = 3600;

	private $id;
	private $name;
	private $fileName;

	private $authorDat;
	private $author; //UserHandler

	private $files; //array {stable, unstable, dev}
	private $fileObjs = array();

	private $downloads; //array {ingame, online, update}

	private $repoInfo;

	private $isBargain;
	private $isDanger;

	private $description;

	private $ratingData;
	private $updaterData;
	private $approvalData;

	private $boardId;
	private $board;

	private $screenshots;

	private $init = false;

	private $deleted;

	public static function getCacheTime() {
		return AddonObject::$cacheTime();
	}

	public function isInit() {
		return $this->init;
	}

	public function __construct($resource) {
		$this->genInit($resource);
	}

	public function initFromId($id) {
		$db = new DatabaseManager();
		$res = $db->query("SELECT * FROM `addon_addons` WHERE id='" . $id . "'");
		if(is_object($res) && is_object($obj = $res->fetch_object())) {
			return $this->genInit($obj);
		} else {
			return false;
		}
	}

	public function initFromDB($dbResult) {
		genInit($dbResult->fetch_object());
	}

	protected function genInit($obj) {
		$this->id = $obj->id;
		$this->name = $obj->name;
		$this->fileName = $obj->filename;

		$this->file = array($obj->file_stable, $obj->file_testing, $obj->file_dev);
		$this->downloads = array($obj->downloads_web, $obj->downloads_ingame, $obj->downloads_update);

		$this->repoInfo = $obj->updaterInfo;

		$this->isBargain = $obj->bargain;
		$this->isDanger = $obj->danger;

		$this->authorDat = $obj->author;

		$this->description = $obj->description;
		$this->ratingData = $obj->ratingInfo;

		$this->updaterData = $obj->updaterInfo;
		$this->approvalData = $obj->approvalInfo;

		$this->boardId = $obj->board;

		$this->deleted = $obj->deleted;

		$this->screenshots = $obj->screenshots;

		$this->dependancies = $obj->dependancies;

		$this->init = true;
	}

	public function getDependancies() {
		$obj = json_decode($this->dependancies);
		if($obj == null) {
			$obj = new stdClass();
			$obj->addons = array();
		}
		return $obj;
	}

	public function getLatestVersion($branch) {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		$db = new DatabaseManager();

		$res = $db->query("SELECT * FROM `addon_updates` WHERE aid='" . $this->getId() . "' AND branch='" . $branch . "' ORDER BY  `time` DESC");
		if($obj = $res->fetch_object()) {
			return $obj->version;
		} else {
			return false;
		}
	}

	public function getUpdates() {
		$db = new DatabaseManager();

		$ret = array();
		$res = $db->query("SELECT * FROM `addon_updates` WHERE aid='" . $this->getId() . "' ORDER BY  `time` DESC");
		while($obj = $res->fetch_object()) {
			$ret[] = $obj;
		}
		return $ret;
	}

	public function getUpdaterInfo() {
		return json_decode($this->updaterData);
	}

	public function getApprovalInfo() {
		return json_decode($this->approvalData);
	}

	public function setApprovalInfo($obj) {
		$this->approvalData = json_encode($obj);

		$db = new DatabaseManager();
		$db->query("UPDATE `addon_addons` SET `approvalInfo`='" . $db->sanitize(json_encode($obj)) . "' WHERE `id`=" . $this->getId());
	}

	public function getLatestBranch() {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		$files = $this->file;
		if($files[0] != 0) {
			return 1;
		}

		if($files[1] != 0) {
			return 2;
		}

		if($files[2] != 0) {
			return 3;
		}
	}

	public function getBranches() {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}
		$ret = array();
		$files = $this->file;
		if($files[0] != 0) {
			$ret[] = 1;
		}

		if($files[1] != 0) {
			$ret[] = 2;
		}

		if($files[2] != 0) {
			$ret[] = 3;
		}
		return $ret;
	}

	public function isDeleted() {
		return $this->deleted;
	}

	public function getRatingData() {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		return json_decode($this->ratingData);
	}

	public function getFile($branch) {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		if(!isset($this->fileObjs[$branch-1])) {
			$this->fileObjs[$branch-1] = new FileObject($this->getFileId($branch));
		}

		return $this->fileObjs[$branch-1];
		//return file object, dont construct until needed!
	}

	public function getFileId($branch) {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		return $this->file[$branch-1];
	}

	public function getId() {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		return $this->id;
	}

	public function getName() {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		return $this->name;
	}

	public function getAuthor() {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		if(is_int($this->authorDat+0)) {
			//LEGACY SUPPORT
			if(!is_object($this->author)) {
				$author = new UserHandler();
				$author->initFromBLID($this->authorDat);
				$this->author = $author;
			}

			$ad = array();
			$auth = $ad[] = new stdClass();
			$auth->id = $author->getId();
			$auth->role = "main";
			$auth->owner = true;
			$this->authorDat = json_encode($ad);
			// TODO do something here to send this back to the database
		}

		$this->authors = json_decode($this->authorDat);
		// [ { "id": 6, "owner": true, "role": "main" }, { "id": 7, "role": "modeler" } ]
		foreach($this->authors as $author) {
			if($author->owner == true) {
				$ao = new UserHandler();
				$ao->initFromId($author->id);
				return $ao;
			}
		}
	}

	public function getAuthors() {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		$this->authors = json_decode($this->authorDat);

		if(!is_array($this->authors)) {
			//LEGACY SUPPORT
			if(!is_object($this->author)) {
				$author = new UserHandler();
				$author->initFromBLID($this->authorDat);
				$this->author = $author;
			}

			$ad = array();
			$auth = $ad[] = new stdClass();
			$auth->id = $author->getId();
			$auth->role = "main";
			$auth->owner = true;
			$this->authorDat = json_encode($ad);
			$this->authors = $ad;
			// TODO do something here to send this back to the database
		}

		$this->authors = json_decode($this->authorDat);
		return $this->authors;
	}

	public function getFilename() {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		return $this->fileName;
	}

	public function isBargain() {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		return $this->isBargain;
	}

	public function isDangerous() {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		return $this->isDanger;
	}

	public function setBargain($bool) {
		$db = new DatabaseManager();
		$db->query("UPDATE `addon_addons` SET bargain = '" . $bool . "' WHERE id=" . $this->getId());
		if($bool) {
			(new AddonBargainNotification(array($this->getId(), 1, array())))->toDatabase();
		} else {
			(new AddonBargainRemoveNotification(array($this->getId(), 1, array())))->toDatabase();
			//createNotification("bargin_bin_remove", "bin_empty", "<a href=\"/addon.php?id=" . $addonObj->id . "\">" . $addonObj->name . "</a> was removed from the bargain bin");
		}
	}

	public function getDownloads($branch = 0) {
		if(!$this->isInit()) {
			throw new Exception('AddonObject not init');
			return;
		}

		if($branch === 0) {
			return array_sum($this->downloads);
		} else if($branch <= 3) {
			return $this->downloads[$branch-1];
		} else {
			throw new Exception("Invalid Index");
		}
	}

	public function getBoard() {
		return BoardManager::getFromId($this->boardId);
	}

	public function getDescription() {
		return $this->description;
	}

	public function updateDescription($desc) {
		$db = new DatabaseManager();

		$db->query("UPDATE `addon_addons` SET `description`='" . $db->sanitize($desc) . "' WHERE id='" . $this->id . "';");
		$this->description = $desc;
	}

	public function getScreenshotCount() {
		return $this->screenshots;
	}

	public function removeScreenshot($id) {
		if($id >= $this->getScreenshotCount()) {
			throw new Exception("Invalid Screenshot");
		}
		$dirName = dirname(__DIR__) . '/files/screenshots/' . $this->getId() . '/';
		for($i = ($id+1); $i < $this->getScreenshotCount(); $i++) {
			echo ($i) . " to " . ($i-1) . "\n";
			rename($dirName . ($i) . '.png', $dirName . ($i-1) . '.png');
			rename($dirName . ($i) . '_thumb.png', $dirName . ($i-1) . '_thumb.png');
		}
		//unlink($dirName . ($this->getScreenshotCount()-1) . '.png'); //we now have a dupe, remove

		//updateDatabase
		$db = new DatabaseManager();
		$db->query("UPDATE `addon_addons` SET `screenshots`=" . ($this->getScreenshotCount()-1) . " WHERE id=" . $this->getId());
		$this->screenshots--;
	}

	public function addScreenshot($fileData) {
		$file = $fileData["tmp_name"];
		$name = $fileData["name"];
		$dirName = dirname(__DIR__) . '/files/screenshots/' . $this->getId() . '/';
		$targetName = $dirName . $this->getScreenshotCount() . '.png';
		$thumbName = $dirName . $this->getScreenshotCount() . '_thumb.png';
		$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

		if(!is_dir($dirName)) {
			mkdir($dirName, 0777, true);
		}

		if($ext == "jpg" || $ext == "jpeg") {
			imagepng(imagecreatefromstring(file_get_contents($file)), $targetName);
		} else if($ext == "png") {
			rename($file, $targetName);
		} else {
			return 1;
		}

		make_thumb($targetName, $thumbName, 150);

		chmod($targetName, "0777");
		chmod($thumbName, "0777");


		$db = new DatabaseManager();
		$db->query("UPDATE `addon_addons` SET `screenshots`=" . ($this->getScreenshotCount()+1) . " WHERE id= " . $this->id . "");
		echo($db->fetchMysqli()->error);
		$this->screenshots++;
	}

	public function failAddon() {
		$db = new DatabaseManager();
		$db->query("UPDATE `addon_addons` SET deleted='1' WHERE id=" . $this->getId());

		(new AddonDeleteNotification(array($this->getId(), 2, array())))->toDatabase();
	}

	public function setMalicious($bool) {
		$db = new DatabaseManager();
		if($bool) {
			$db->query("UPDATE `addon_files` SET `malicious`=2 WHERE id=" . $this->getFileId($this->getLatestBranch()));
		}
	}

	public function setDangerous($bool) {
		$db = new DatabaseManager();
		$db->query("UPDATE `addon_addons` SET danger = '" . $bool . "' WHERE id=" . $this->getId());
		if($bool) {
			(new AddonDangerousNotification(array($this->getId())))->toDatabase();
		}
	}

	public function getComments() {
		$db = new DatabaseManager();
		$commentRes = $db->query("SELECT * FROM `addon_comments` WHERE aid='" . $this->id . "' ORDER BY timestamp DESC");
		$comments = array();
		while($comment = $commentRes->fetch_object()) {
			$author = UserManager::getFromId($comment->uid);
			$comments[] = new Comment(array($comment->comment, $author, $comment->timestamp));
		}

		return $comments;
	}

	public function getCommentsRange($start, $end) {
		$db = new DatabaseManager();
		$commentRes = $db->query("SELECT * FROM `addon_comments` WHERE aid='" . $this->id . "' ORDER BY timestamp DESC LIMIT $start, $end");
		$comments = array();
		while($comment = $commentRes->fetch_object()) {
			$author = UserManager::getFromId($comment->uid);
			$comments[] = new Comment(array($comment->comment, $author, $comment->timestamp));
		}

		return $comments;
	}

	public function generateVersionFile($branch, $version = null) {
		try {
			$fileObj = $this->getFile($branch);
		} catch (Exception $e) {
			echo "failed";
			return;
		}

		echo "is file: " . dirname(__DIR__) . '/files/decomp/' . $fileObj->getHash() . '/version.txt<br />';
		if(is_file(dirname(__DIR__) . '/files/decomp/' . $fileObj->getHash() . '/version.txt')) {
			echo "unlink";
			unlink(dirname(__DIR__) . '/files/decomp/' . $fileObj->getHash() . '/version.txt');
		}

		if(is_file(dirname(__DIR__) . '/files/decomp/' . $fileObj->getHash() . '/rtbInfo.txt')) {
			unlink(dirname(__DIR__) . '/files/decomp/' . $fileObj->getHash() . '/rtbInfo.txt');
		}

		if(is_dir(dirname(__DIR__) . '/files/decomp/' . $fileObj->getHash() . '/__MACOSX')) {
			$dir = dirname(__DIR__) . '/files/decomp/' . $fileObj->getHash() . '/__MACOSX';
			$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new RecursiveIteratorIterator($it,
			             RecursiveIteratorIterator::CHILD_FIRST);
			foreach($files as $file) {
			    if ($file->isDir()){
			        rmdir($file->getRealPath());
			    } else {
			        unlink($file->getRealPath());
			    }
			}
			rmdir($dir);
		}

		if($version == null) {
			$version = $this->getLatestVersion($branch);
		}

		if($version == false) {
			$version = 0;
		}

		$channelName[1] = "stable";
		$channelName[2] = "unstable";
		$channelName[3] = "development";

		$verDat = new stdClass();
		$verDat->version = $version;
		$verDat->channel = $channelName[$branch];

		$liveBranch = new stdClass();
		$liveBranch->url = "http://blocklandglass.com/api/support_updater/repo.php";
		$liveBranch->format = "JSON";
		$liveBranch->id = $this->getId();

		$backupRepo = new stdClass();
		$backupRepo->url = "http://cdn.blocklandglass.com/repo.txt";
		$backupRepo->format = "JSON";
		$backupRepo->id = $this->getId();

		$verDat->repositories = array($liveBranch, $backupRepo);

		$file = fopen(dirname(__DIR__) . '/files/decomp/' . $fileObj->getHash() . '/version.json', "w");
		$content = json_encode($verDat, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		fwrite($file, $content);
		fclose($file);

		//GLASS FILE DATA
		$glassData = new stdClass();
		$glassData->formatVersion = 1;
		$glassData->id = $this->getId();
		$glassData->board = $this->getBoard()->getId();
		$glassData->filename = $this->getFilename();
		$glassData->title = $this->getName();

		$file = fopen(dirname(__DIR__) . '/files/decomp/' . $fileObj->getHash() . '/glass.json', "w");
		$content = json_encode($glassData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		fwrite($file, $content);
		fclose($file);

		if(is_file(dirname(__DIR__) . '/files/comp/' . $fileObj->getHash() . ".zip")) {
			unlink(dirname(__DIR__) . '/files/comp/' . $fileObj->getHash() . ".zip");
		}
		Zip(dirname(__DIR__). '/files/decomp/' . $fileObj->getHash() . '/',
			dirname(__DIR__) . '/files/comp/' . $fileObj->getHash() . ".zip");
	}
}

function make_thumb($src, $dest, $desired_width) {

	// read the source image
	$source_image = imagecreatefrompng($src);
	$width = imagesx($source_image);
	$height = imagesy($source_image);

	// find the "desired height" of this thumbnail, relative to the desired width
	$desired_height = floor($height * ($desired_width / $width));

	// create a new, "virtual" image
	$virtual_image = imagecreatetruecolor($desired_width, $desired_height);

	// copy source image at a resized size
	imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

	// create the physical thumbnail image to its destination
	imagepng($virtual_image, $dest);
}

if(!function_exists("Zip")) {
function Zip($source, $destination) {
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    $source = str_replace('\\', '/', realpath($source));

    if (is_dir($source) === true)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file)
        {
            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                continue;

            $file = realpath($file);

            if (is_dir($file) === true)
            {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            }
            else if (is_file($file) === true)
            {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    }
    else if (is_file($source) === true)
    {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}
}
*/

?>
