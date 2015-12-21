<?php
require_once dirname(__DIR__) . "/../../../private/class/AddonManager.php";

$trending = include(dirname(__DIR__) . "/../../../private/json/getTrendingAddonsWithUsers.php");
$latest = include(dirname(__DIR__) . "/../../../private/json/getNewAddonsWithUsers.php");

//var_dump($latest);
$ret = new stdClass();
$ret->status = "success";
$ret->latest = [];
foreach($latest['addons'] as $addon) {
  $addonDat = new stdClass();
  $addonDat->id = $addon->id;
  $addonDat->name = $addon->name;
  $addonDat->uploadDate = $addon->uploadDate;

  $addonDat->author = $latest['users'][$addon->blid]->getUsername();

  // TODO something with author info

  $ret->latest[] = $addonDat;
}

echo json_encode($ret, JSON_PRETTY_PRINT);
?>
