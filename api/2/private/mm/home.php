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
  $addonDat->uploadDate = date("D, g:i a", strtotime($addon->uploadDate));

  $addonDat->author = $latest['users'][$addon->blid]->getUsername();

  // TODO something with author info

  $ret->latest[] = $addonDat;
}

$ret->trending = [];
foreach($trending['addons'] as $addon) {
  $addonDat = new stdClass();
  $addonDat->id = $addon->id;
  $addonDat->name = $addon->name;
  $addonDat->downloads = $addon->getTotalDownloads();

  $addonDat->author = $trending['users'][$addon->blid]->getUsername();

  // TODO something with author info

  $ret->trending[] = $addonDat;
}

//var_dump($trending);

echo json_encode($ret, JSON_PRETTY_PRINT);
?>
