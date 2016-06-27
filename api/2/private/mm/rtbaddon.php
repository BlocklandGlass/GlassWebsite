<?php
require_once dirname(__DIR__) . "/../../../private/class/RTBAddonManager.php";

$ret = new stdClass();
$ret->status = "success";
$ret->addon = RTBAddonManager::getAddonFromId($_REQUEST['id']);

echo json_encode($ret, JSON_PRETTY_PRINT);
