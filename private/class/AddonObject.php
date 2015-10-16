<?php
require_once dirname(__FILE__) . '/DatabaseManager.php';
require_once dirname(__FILE__) . '/BoardManager.php';
require_once dirname(__FILE__) . '/UserHandler.php';
require_once dirname(__FILE__) . '/AddonManager.php';
require_once dirname(__FILE__) . '/FileObject.php';

class AddonObject {
	private $id;
	private $name;
	private $fileName;

	private $authorBlid;
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

	public function isInit() {
		return $this->init;
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

		$this->authorBlid = $obj->author;

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
		return $this->updaterData;
	}

	public function getApprovalInfo() {
		return $this->approvalData;
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

		return $this->ratingData;
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

		//return author object, dont construct until needed!
		if(!is_object($this->author)) {
			$author = new UserHandler();
			$author->initFromBLID($this->authorBlid);
			$this->author = $author;
		}

		return $this->author;
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

	/* read the source image */
	$source_image = imagecreatefrompng($src);
	$width = imagesx($source_image);
	$height = imagesy($source_image);

	/* find the "desired height" of this thumbnail, relative to the desired width  */
	$desired_height = floor($height * ($desired_width / $width));

	/* create a new, "virtual" image */
	$virtual_image = imagecreatetruecolor($desired_width, $desired_height);

	/* copy source image at a resized size */
	imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

	/* create the physical thumbnail image to its destination */
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
?>
