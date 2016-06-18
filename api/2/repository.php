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

$addonIds = explode("-", $db->sanitize($_GET['mods']));

$repo = new stdClass();

$repo->name = "Blockland Glass Generated Repo";
$ao = 'add-ons';
$repo->$ao = array();

foreach($addonIds as $id) {
  $obj = AddonManager::getFromId($id);

  if(!is_object($obj)) {
    $addon = new stdClass();
    $addon->id = $id;
    $addon->error = "Unable to create object";
    array_push($repo->$ao, $addon);
    continue;
  }
  //$webUrl = "api.blocklandglass.com";
  $webUrl = "test.blocklandglass.com";
  $cdnUrl = "cdn.blocklandglass.com";

  $addon = new stdClass();
	$addon->name = substr($obj->getFilename(), 0, strlen($obj->getFilename())-4);
	$addon->description = str_replace("\r\n", "<br>", $obj->getDescription());

  $chanObj = new stdClass();
  $chanObj->name = "stable";
  $chanObj->version = $obj->getVersion();
  $chanObj->file = "http://" . $webUrl . "/api/2/download.php?type=addon_update&id=" . $obj->getId() . "&branch=1";
  $chanObj->changelog = "http://" . $webUrl . "/api/2/changelog.php?id=" . $obj->getId() . "&branch=1";

  if(isset($_REQUEST['legacy']) && $_REQUEST['legacy'] == 1 && $id != 11) {
    $chanObj->name = "*";
  }

  $addon->channels[] = $chanObj;

  if($obj->hasBeta()) {
    $chanObj = new stdClass();
    $chanObj->name = "beta";
    $chanObj->version = $obj->getBetaVersion();
    $chanObj->file = "http://" . $webUrl . "/api/2/download.php?type=addon_update&id=" . $obj->getId() . "&branch=2";
    $chanObj->changelog = "http://" . $webUrl . "/api/2/changelog.php?id=" . $obj->getId() . "&branch=2";

    $addon->channels[] = $chanObj;
  }

  array_push($repo->$ao, $addon);
}

echo str_replace("\n", "\r\n", json_encode($repo, JSON_PRETTY_PRINT));
