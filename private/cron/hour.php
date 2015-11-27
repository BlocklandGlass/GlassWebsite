<?php
header('Content-Type: text/json');
require_once dirname(__DIR__) . '/class/CronStatManager.php';

echo json_encode(CronStatManager::collectHourStat(true), JSON_PRETTY_PRINT);
?>
