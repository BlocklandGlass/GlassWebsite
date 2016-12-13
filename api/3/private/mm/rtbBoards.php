<?php
require_once dirname(__DIR__) . "/../../../private/class/RTBAddonManager.php";

$ret = new stdClass();
$ret->status = "success";
$ret->boards = RTBAddonManager::getBoards();

echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
