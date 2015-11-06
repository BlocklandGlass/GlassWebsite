<?php
require_once dirname(dirname(__DIR__)) . '/class/DatabaseManager.php';
require_once dirname(dirname(__DIR__)) . '/class/SemVer.php';
header('Content-Type: text/plain');
$db = new DatabaseManager();
if(!isset($_GET['id'])) {
	$_GET['id'] = 1;
}

$addonIds = split("-", $db->sanitize($_GET['mods']));

function getLatestVersion($branch) {
	global $updateArray;
	$branchUpdates = array();
	foreach($updateArray as $up) {
		if($up->branch == $branch) {
			$branchUpdates[] = $up->version;
		}
	}
	if(empty($branchUpdates)) {
		return null;
	}

	try {
		$sorted = SemVer::sort($branchUpdates);
		$latest = end($sorted);
		//var_dump($latest);
	} catch (Exception $e) {
		return false;
	}

	return $latest;
}

function getLatestRestart($branch) {
	global $updateArray;
	$branchUpdates = array();
	foreach($updateArray as $up) {
		if($up->branch == $branch && $up->restart) {
			$branchUpdates[] = $up->version;
		}
	}

	if(empty($branchUpdates)) {
		return null;
	}

	try {
		$sorted = SemVer::sort($branchUpdates);
		$latest = end($sorted);
	} catch (Exception $e) {
		return false;
	}

	return $latest;
}

$repo = new stdClass();

$repo->name = "Blockland Glass Generated Repo";
$ao = 'add-ons';
$repo->$ao = array();

foreach($addonIds as $id) {

	$addonResult = $db->query("SELECT * FROM `addon_addons` WHERE id=" . $id);
	$addonObj = $addonResult->fetch_object();

	$fileResult = $db->query("SELECT * FROM `addon_files` WHERE id=" . $addonObj->file_stable);
	$fileObj = $fileResult->fetch_object();

	$updateResult = $db->query("SELECT * FROM `addon_updates` WHERE aid=" . $addonObj->id);
	$updateArray = array();
	while($up = $updateResult->fetch_object()) {
		$updateArray[] = $up;
	}

	//var_dump($updateResult);

	$webUrl = "api.blocklandglass.com";

	$addon = new stdClass();
	$addon->name = substr($addonObj->filename, 0, strlen($addonObj->filename)-4);
	$addon->description = str_replace("\r\n", "<br>", $addonObj->description);

	$channelId[1] = "stable";
	$channelId[2] = "unstable";
	$channelId[3] = "development";
	for($i = 1; $i <= 3; $i++) {
		$latest = getLatestVersion($i);
		$restart = getLatestRestart($i);

		if($latest !== null && $latest !== false) {
			$channel = new stdClass();
			$channel->name = $channelId[$i];
			$channel->version = $latest->__toString();
			if($restart !== null && $restart !== false) {
				$channel->restartRequired = $restart->__toString();
			}
			$channel->file = "http://" . $webUrl . "/api/support_updater/download.php?id=" . $id . "&branch=" . $i;
			$channel->changelog = "http://" . $webUrl . "/api/support_updater/changelog.php?id=" . $id . "&branch=" . $i;
			$addon->channels[] = $channel;
		} else if($latest === false) {
			unset($channel);
			$channel = new stdClass();
			$channel->name = $channelId[$i];
			$channel->error = "Invalid SemVer syntax";
			$addon->channels[] = $channel;
		}
	}
	array_push($repo->$ao, $addon);
}

//var_dump($repo);
echo json_encode($repo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
return;
?>
{
	"name":"Blockland Glass Generated Repo",
	"add-ons":
	[
		{
			"name":"<?php ?>",
			"description":"Write about it.",
			"channels":
			[
				{
					"name":"release",
					"version":"3.8.1",
					"restartRequired":"3.8-rc-1",
					"file":"http://example.com/something.zip",
					"changelog":"http://blockland.jincux.tk/support_updater/changelog.php?id=<?php echo $id ?>"
				},
				{
					"name":"beta",
					"version":"4.0-beta-3",
					"restartRequired":"3.8-rc-1",
					"file":"..."
				}
			]
		},
		{
			"name":"Some_AddOn",
			"channels":
			[
				{
					"name":"release",
					"version":"1",
					"file":"..."
				}
			]
		}
	]
}
