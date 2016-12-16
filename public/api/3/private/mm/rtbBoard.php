<?php
header('Content-Type: text/json');
use Glass\RTBAddonManager;

$name = $_REQUEST['name'] ?? false;
$page = $_REQUEST['page'] ?? 1;

$pageSize = 15;
$start = ($page-1)*$pageSize;

$ret = new stdClass();
$ret->status = "success";
$ret->board_name = $name;
$ret->page = $page;
$ret->pages = ceil(RTBAddonManager::getTypeCount($name)/$pageSize);
$ret->addons = RTBAddonManager::getFromType($name, $start, $pageSize);

foreach($ret->addons as $addon) {
  $addon->description = htmlspecialchars_decode($addon->description);
}

echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
