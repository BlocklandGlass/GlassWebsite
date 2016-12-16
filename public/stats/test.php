<?php
use Glass\CronStatManager;

$stat1 = CronStatManager::getEntry("2015-11-27 17:00:00", "hour");
$stat2 = CronStatManager::getEntry("2015-11-27 18:00:00", "hour");

$res = CronStatManager::compare($stat1, $stat2);

header('Content-Type: text/json');
echo json_encode($res, JSON_PRETTY_PRINT);
?>
