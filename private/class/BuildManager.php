<?php
require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));

class BuildManager {
	private static $objectCacheTime = 300; //5 minutes, enough time for someone to preview the build and then download it
	private static $userBuildsCacheTime = 60;

	public static $maxFileSize = 10000000;

	public static function getFromID($id, $resource = false) {
		$buildObject = apc_fetch('buildObject_' . $id, $success);

		if($success === false) {
			if($resource !== false) {
				$buildObject = new BuildObject($resource);
			} else {
				$database = new DatabaseManager();
				BuildManager::verifyTable($database);
				$resource = $database->query("SELECT * FROM `build_builds` WHERE `id` = '" . $database->sanitize($id) . "' LIMIT 1");

				if(!$resource) {
					throw new Exception("Database error: " . $database->error());
				}

				if($resource->num_rows == 0) {
					$buildObject = false;
				} else {
					$buildObject = new BuildObject($resource->fetch_object());
				}
				$resource->close();
			}
			apc_store('buildObject_' . $id, $buildObject, BuildManager::$objectCacheTime);
		}
		return $buildObject;
	}

	public static function getBuildsFromBLID($id) {
		$userBuilds = apc_fetch('userBuilds_' . $id, $success);

		if($success === false) {
			$database = new DatabaseManager();
			BuildManager::verifyTable($database);
			$resource = $database->query("SELECT * FROM `build_builds` WHERE `blid` = '" . $database->sanitize($id) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}
			$userBuilds = [];

			while($row = $resource->fetch_object()) {
				$userBuilds[] = BuildManager::getFromID($row->id, $row)->getID();
			}
			$resource->close();
			apc_store('userBuilds_' . $id, $userBuilds, BuildManager::$userBuildsCacheTime);
		}
		return $userBuilds;
	}

	/**
	 *  Upload a build with contents as an array of strings
	 *  
	 *  $parameters - an array with the following optional settings
	 *  	"tags" => array of tag ids
	 *  	"screenshots" => idk yet
	 *  	"credits" => sure
	 */
	public static function uploadBuild($blid, $fileName, $contents, $parameters = []) { //to do
		//a filter for file names
		//allows letters, numbers, '.', '-', '_', '\'', '!', and ' '
		if(preg_replace("/[^a-zA-Z0-9\.\-\_\ \'\!]/", "", $fileName) !== $fileName) {
			$response = [
				"message" => "Invalid file name - You may use letters, numbers, spaces, and the following symbols: -, ', _, ., and !"
			];
			return $response;
		}
		$fileExt = pathinfo($fileName, PATHINFO_EXTENSION);

		if($fileExt != "bls") {
			$response = [
				"message" => "Only .bls files are allowed"
			];
			return $response;
		}
		$targetPath = realPath(dirname(__FILE__) . "uploads/" . $fileName);

		if(file_exists($targetPath)) {
			$response = [
				"message" => "A file with that name already exists."
			];
			return $response;
		}
		$check = BuildManager::validateBLSContents($contents);

		//to do:
		//actual file saving
		//create database entries
		//start stat tracking
		//redirect user to manage page
		//event logging
		if($check['ok']) {
			$response = [
				"message" => $check['message']
			];
			return $response;
		} else {
			$response = [
				"message" => $check['message']
			];
			return $response;
		}
	}

	/**
	 *  Takes in an array of strings representing the contents of the file.
	 *  Returns an array with parameter "ok" set to 1 on success, and a message describing the status.
	 *  When "ok" is set to 1, the following is also set:
	 *  	brickcount - integer
	 *  	description - string
	 *  	colortable - array of strings representing garbage
	 *  	bricks - array of strings representing uinames of bricks
	 */
	public static function validateBLSContents($contents) {
		//check header
		if(	!preg_match("/^This is a Blockland save file\.  You probably shouldn't modify it cause you'll screw it up\.(\r)?$/", $contents[0]) ||
			!preg_match("/^([0]|[1-9][0-9]*)\r?$/", $contents[1])) {
			$response = [
				"ok" => 0,
				"message" => "This save file appears to be corrupted: bad header."
			];
			return $response;
		}
		$desclen = $contents[1];

		if($desclen > 0) {
			$description = $contents[2];
		} else {
			$description = "";
		}

		for($i=1; $i<$desclen; $i++) {
			$description .= "\n" . $contents[2 + $i];
		}
		$colorTable = [];

		//verify color table
		for($i=0; $i<64; $i++) {
			//Matching a row in the color table - Example:
			//0.592157 0.156863 0.392157 0.694118
			if(!preg_match("/^(((1(\.0+)?)|(0(\.[0-9]+)?))[ ]){3}((1(\.0+)?)|(0(\.[0-9]+)?))\r?$/", $contents[2 + $desclen + $i])) {
				$response = [
					"ok" => 0,
					"message" => "Color parsing error - Color Number " . $i
				];
				return $response;
			} else {
				$colorTable[$i] = $contents[2 + $desclen + $i];
			}
		}

		if(!preg_match("/^Linecount (0|([1-9][0-9]*))\r?$/", $contents[66 + $desclen])) {
			$response = [
				"ok" => 0,
				"message" => "This save file appears to be corrupted: bad linecount."
			];
			return $response;
		}
		list($lineCount) = sscanf($contents[66 + $desclen], "Linecount %d");
		$currentLine = 67 + $desclen;
		$count = count($contents);
		$brickCount = 0;
		$brickUINames = [];
		$bricks = [];

		//verify actual brick data
		for($currline = 67 + $desclen; $currline < $count; $currline++) {
			if(isset($emptyline)) {
				//we saw an empty line that was not the last one
				$response = [
					"ok" => 0,
					"message" => "This save file appears to be corrupted: text after empty line."
				];
				return $response;
			}

			if($contents[$currline] === "") {
				$emptyline = true;
				continue;
			}

			//Matching a line declaring a brick - Example:
			//1x1 Print" 136.75 117.75 9.5 1 0 6 Letters/-space 0 0 1 1 1
			if(!preg_match("/^[^\"]+[\"]( -?([0-9]+(\.[0-9]+)?)){3} [0-3] [0-1] [0-9]+ .* [0-9]+ [0-9]+ [0-1] [0-1] [0-1]\r?$/", $contents[$currline])) {
				$response = [
					"ok" => 0,
					"message" => "This save file appears to be corrupted: Bad brick definition - " . $currline
				];
				return $response;
			}
			$brickCount++;
			$uiname = substr($contents[$currline], 0, strpos($contents[$currline], "\""));

			if(!isset($brickUINames[$uiname])) {
				$brickUINames[$uiname] = true;
				$bricks[] = $uiname;
			}

			//check for +- properties
			for($i=$currline+1; $i < $count; $i++) {
				if(!preg_match("/^\+-/", $contents[$i])) {
					$currline = $i - 1;
					break;
				}
			}
		}

		if($lineCount != $brickCount) {
			$response = [
				"ok" => 0,
				"message" => "This save file appears to be corrupted: Expected " . $lineCount . " bricks but found " . ($currline-70)
			];
			return $response;
		}
		//made it
		$response = [
			"ok" => 1,
			"message" => "File OK.",
			"brickcount" => $brickCount,
			"description" => $description,
			"colortable" => $colorTable,
			"bricks" => $bricks
		];
		return $response;
	}

	public static function verifyTable($database) {
		if($database->debug()) {
			require_once(realpath(dirname(__FILE__) . '/UserManager.php'));
			require_once(realpath(dirname(__FILE__) . '/AddonManager.php'));
			UserManager::verifyTable($database); //we need users table to exist before we can create this one
			AddonManager::verifyTable($database);

			if(!$database->query("CREATE TABLE IF NOT EXISTS `build_builds` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`blid` INT NOT NULL,
				`name` VARCHAR(16) NOT NULL,
				`filename` TEXT NOT NULL,
				`bricks` INT NOT NULL DEFAULT 0,
				`description` TEXT,
				FOREIGN KEY (`blid`)
					REFERENCES users(`blid`)
					ON UPDATE CASCADE
					ON DELETE CASCADE,
				PRIMARY KEY (`id`))")) {
				throw new Exception("Error creating builds table: " . $database->error());
			}

			//to do: probably should move this to another class, maybe make dependencyManager more general
			if(!$database->query("CREATE TABLE IF NOT EXISTS `build_dependency` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`bid` INT NOT NULL,
				`aid` INT NOT NULL,
				FOREIGN KEY (`bid`)
					REFERENCES build_builds(`id`)
					ON UPDATE CASCADE
					ON DELETE CASCADE,
				FOREIGN KEY (`aid`)
					REFERENCES addon_addons(`id`)
					ON UPDATE CASCADE
					ON DELETE CASCADE,
				PRIMARY KEY (`id`))")) {
				throw new Exception("unable to create build dependency table: " . $database->error());
			}
		}
	}
}
?>
