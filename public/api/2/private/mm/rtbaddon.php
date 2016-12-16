<?php
use Glass\RTBAddonManager;

$ret = new stdClass();
$ret->status = "success";
$ret->addon = RTBAddonManager::getAddonFromId($_REQUEST['id']);

echo json_encode($ret, JSON_PRETTY_PRINT);
