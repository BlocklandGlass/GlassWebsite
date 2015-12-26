<?php
header('Content-Type: text/json');
require_once dirname(__DIR__) . '/class/CronStatManager.php';
CronStatManager::collectHourStat(true);


require_once dirname(__DIR__) . '/class/AddonManager.php';
AddonManager::checkUpstreamRepos();
?>
