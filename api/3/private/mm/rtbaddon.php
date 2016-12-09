<?php
require_once dirname(__DIR__) . "/../../../private/class/RTBAddonManager.php";

$ret = new stdClass();
$ret->status = "success";
$ret->addon = RTBAddonManager::getAddonFromId($_REQUEST['id']);

/*$ret->addon->author = utf8_encode($ret->addon->author);
$ret->addon->description = utf8_encode($ret->addon->description);
$ret->addon->title = utf8_encode($ret->addon->title);*/

echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
