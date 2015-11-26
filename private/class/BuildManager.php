<?php
require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));
require_once(realpath(dirname(__FILE__) . '/BuildObject.php'));

class BuildManager {
	private static $objectCacheTime = 3600; //1 hour
	private static $userBuildsCacheTime = 60;
	public static $escapedCharacters = [
		"/\\n/",
		"/\\t/",
		"/\\\\/",
		"/\\\"/",
		"/\\\'/"
	];
	public static $unescapedCharacters = [
		"\n",
		"\t",
		"\\",
		"\"",
		"\'"
	];

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

	//do we actually want to enforce unique file names?
	public static function getIDFromFileName($name) {
		$buildID = apc_fetch('buildByFileName_' . $name, $success);

		if($success === false) {
			$database = new DatabaseManager();
			BuildManager::verifyTable($database);
			$resource = $database->query("SELECT * FROM `build_builds` WHERE `filename` = '" . $database->sanitize($name) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}

			if($resource->num_rows == 0) {
				$buildID = false;
			} else {
				$row = $resource->fetch_object();
				$buildID = BuildManager::getFromID($row->id, $row)->getID();
			}
			$resource->close();
			apc_store('buildByFileName_' . $name, $buildID, BuildManager::$objectCacheTime);
		}
		return $buildID;
	}

	/**
	 *  Upload a build with contents as an array of strings
	 */
	public static function uploadBuild($blid, $buildName, $fileName, $contents, $tempPath, $description = false) {
		//to do, generate a random name for storage?, and allow the build name to contain any characters and be not unique - done
		//a filter for file names
		//allows letters, numbers, '-', '_', '\'', '!', and ' '
		if(!preg_match("/^[a-zA-Z0-9\-\_\ \'\!]{1,56}\.bls$/", $fileName)) {
			$response = [
				"message" => "Invalid File Name - You may use up to 56 characters followed by '.bls' and include letters, numbers, spaces, and the following symbols: -, ', _, and !"
			];
			return $response;
		}

		//validate build name
		//match 1-60 of any character except newlines
		if(!preg_match("/^.{1,60}$/", $buildName)) {
			$response = [
				"message" => "Your Build Title can only contain up to 60 characters and cannot contain line breaks"
			];
			return $response;
		}
		//temporary file storage for now
		//$targetPath = dirname(__DIR__) . "/../builds/uploads/" . $buildName . ".bls";

		//if(file_exists($targetPath)) {
		$other = BuildManager::getIDFromFileName($fileName);

		if($other !== false) {
			$response = [
				"message" => "A Build with that File Name already exists - ID " . $other
			];
			return $response;
		}
		$check = BuildManager::validateBLSContents($contents);

		//to do:
		//actual file saving - check
		//create database entries - check
		//start stat tracking - check?
		//redirect user to manage page - check
		//event logging
		if(!$check['ok']) {
			$response = [
				"message" => $check['message']
			];
			return $response;
		}

		if($description === false) {
			$description = $check['description'];
		}

		//if(!move_uploaded_file($tempPath, $targetPath)) {
		//	$response = [
		//		"message" => "An error occurred while saving your build, please contact an administrator if this persists"
		//	];
		//	return $response;
		//}

		//it's go time
		$database = new DatabaseManager();
		BuildManager::verifyTable($database);
		if(!$database->query("INSERT INTO `build_builds` (`blid`, `name`, `filename`, `bricks`, `description`) VALUES ('" .
			$database->sanitize($blid) . "', '" .
			$database->sanitize($buildName) . "', '" .
			$database->sanitize($fileName) . "', '" .
			$database->sanitize($check['brickcount']) . "', '" .
			$database->sanitize($description) . "')")) {
			throw new Exception("Database error: " . $database->error());
		}
		$id = $database->fetchMysqli()->insert_id;
		require_once(realpath(dirname(__FILE__) . '/AWSFileManager.php'));
		AWSFileManager::uploadNewBuild($id, $tempPath);
		require_once(realpath(dirname(__FILE__) . '/StatManager.php'));
		StatManager::addStatsToBuild($id);

		//to do: stats
		$response = [
			"redirect" => "/builds/manage.php?id=" . $id
		];
		return $response;
	}

	/**
	 *  Takes in an array of strings representing the contents of the file.
	 *  Returns an array with parameter "ok" set to 1 on success, and a message describing the status.
	 *  When "ok" is set to 1, the following are also set:
	 *  	brickcount - integer
	 *  	description - string
	 *  	colortable - a array of RGBA colors each as an array of floats accessed by $array['r'], etc
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
		$description = preg_replace(BuildManager::$escapedCharacters, BuildManager::$unescapedCharacters, $description);
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
				$line = $contents[2 + $desclen + $i];

				list($r, $g, $b, $a) = sscanf($line, "%f %f %f %f");
				$colorTable[$i] = [
					"r" => $r,
					"g" => $g,
					"b" => $b,
					"a" => $a
				];
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

	public static function updateBuildID($bid, $buildname, $filename, $description) {
		$build = BuildManager::getFromID($bid);

		if($build === false) {
			$response = [
				"changed" => false,
				"message" => "Build not found"
			];
			return $false;
		} else {
			return BuildManager::updateBuild($build, $buildname, $filename, $description);
		}
	}

	public static function updateBuild($build, $buildname, $filename, $description) {
		//to do: apc_delete and update existing object
		$changed = false;

		if($buildname !== $build->name) {
			//validate build name
			//match 1-60 of any character except newlines
			//this should probably be a method instead of copy pasted from above
			if(!preg_match("/^.{1,60}$/", $buildName)) {
				$response = [
					"changed" => $changed,
					"message" => "Your Build Title can only contain up to 60 characters and cannot contain line breaks"
				];
				return $response;
			}
			$database = new DatabaseManager();
			BuildManager::verifyTable($database);

			if(!$database->query("UPDATE `build_builds` SET `name` = '" .
				$database->sanitize($buildname) . "'")) {
				throw new Exception("Database error: " . $database->error());
			}
			$build->name = $buildname;
			apc_store('buildObject_' . $build->id, $build, BuildManager::$objectCacheTime);
			$changed = true;
		}

		if($filename !== $build->filename) {
			//allows letters, numbers, '-', '_', '\'', '!', and ' '
			if(!preg_match("/^[a-zA-Z0-9\-\_\ \'\!]{1,56}\.bls$/", $filename)) {
				$response = [
					"ok" => $changed,
					"message" => "Invalid File Name - You may use up to 56 characters followed by '.bls' and include letters, numbers, spaces, and the following symbols: -, ', _, and !"
				];
				return $response;
			}
			$other = BuildManager::getIDFromFileName($filename);

			if($other !== false) {
				$response = [
					"changed" => $changed,
					"message" => "A Build with that File Name already exists - ID " . $other
				];
				return $response;
			}
			$database = new DatabaseManager();
			BuildManager::verifyTable($database);

			if(!$database->query("UPDATE `build_builds` SET `filename` = '" .
				$database->sanitize($filename) . "'")) {
				throw new Exception("Database error: " . $database->error());
			}
			$build->filename = $filename;
			apc_store('buildObject_' . $build->id, $build, BuildManager::$objectCacheTime);
			$changed = true;
		}

		if($description !== $build->description) {
			$database = new DatabaseManager();
			BuildManager::verifyTable($database);

			if(!$database->query("UPDATE `build_builds` SET `filename` = '" .
				$database->sanitize($filename) . "'")) {
				throw new Exception("Database error: " . $database->error());
			}
			$build->description = $description;
			apc_store('buildObject_' . $build->id, $build, BuildManager::$objectCacheTime);
			$changed = true;
		}

		if($changed) {
			$response = [
				"changed" => true,
				"message" => "Build Updated"
			];
			return $response;
		} else {
			$response = [
				"changed" => false,
				"message" => ""
			];
			return $response;
		}
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
				`name` VARCHAR(60) NOT NULL,
				`filename` VARCHAR(60) NOT NULL,
				`bricks` INT NOT NULL DEFAULT 0,
				`description` TEXT NOT NULL,
				FOREIGN KEY (`blid`)
					REFERENCES users(`blid`)
					ON UPDATE CASCADE
					ON DELETE CASCADE,
				UNIQUE KEY (`filename`),
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
