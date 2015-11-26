<?php
require_once dirname(dirname(__DIR__)) . '/private/class/AddonManager.php';
require_once dirname(dirname(__DIR__)) . '/private/class/DatabaseManager.php';
require_once dirname(dirname(__DIR__)) . '/private/class/SemVer.php';
header('Content-Type: text/json');

$db = new DatabaseManager();

if(!isset($_GET['mods'])) {
  $ret = new stdClass();
  $ret->status = "error";
  $ret->error = "mods field is blank";
  die(json_encode($ret, JSON_PRETTY_PRINT));
}

$addonIds = split("-", $db->sanitize($_GET['mods']));

$repo = new stdClass();

$repo->name = "Blockland Glass Generated Repo";
$ao = 'add-ons';
$repo->$ao = array();

foreach($addonIds as $id) {
  $obj = AddonManager::getFromId($id);
  $webUrl = "api.blocklandglass.com";
  $cdnUrl = "cdn.blocklandglass.com";

  $addon = new stdClass();
	$addon->name = $obj->getFilename();
	$addon->description = str_replace("\r\n", "<br>", $obj->getDescription());

	$channelId[1] = "stable";
	$channelId[2] = "unstable";
	$channelId[3] = "development";
  foreach($channelId as $cid=>$name) {
    $channel = new stdClass();
    $chanDat = $obj->getBranchInfo($cid);

    if($chanDat !== false) {
      $channel->name = $channelId[$cid];
      $channel->version = $chanDat->version;
      if($chanDat->restart !== null && $chanDat->restart !== false) {
        $channel->restartRequired = $chanDat->restart;
      }
      $channel->file = "http://" . $webUrl . "/api/2/download.php?type=addon_update&id=" . $obj->getId() . "&branch=" . $cid;
      $channel->changelog = "http://" . $webUrl . "/api/2/changelog.php?id=" . $obj->getId() . "&branch=" . $cid;

      $addon->channels[] = $channel;
    }
  }

  array_push($repo->$ao, $addon);
}

echo json_encode($repo, JSON_PRETTY_PRINT);
