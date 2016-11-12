<?php
header('Content-Type: text/json');
require_once dirname(__DIR__) . '/class/CronStatManager.php';
$csm = new CronStatManager();
$csm->collectHourStat(true);

require_once dirname(__DIR__) . '/class/StatManager.php';
StatManager::saveHistory();
//require_once dirname(__DIR__) . '/class/AddonManager.php';
//AddonManager::checkUpstreamRepos();
?>
