<?php
header('Content-Type: text/json');
require_once dirname(__DIR__) . "/../../../private/class/RTBAddonManager.php";

$name = $_REQUEST['name'] ?? false;
$page = $_REQUEST['page'] ?? 1;

$pageSize = 15;
$start = ($page-1)*$pageSize;

$ret = new stdClass();
$ret->status = "success";
$ret->pages = ceil(RTBAddonManager::getTypeCount($name)/$pageSize);
$ret->addons = RTBAddonManager::getFromType($name, $start, $pageSize);

echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
