<?php
header('Content-Type: text/json');
use Glass\CronStatManager;
$csm = new CronStatManager();
$csm->collectHourStat(true);

use Glass\StatManager;
StatManager::saveHistory();
//use Glass\AddonManager;
//AddonManager::checkUpstreamRepos();
?>
