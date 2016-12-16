<?php
header('Content-Type: text/json');
use Glass\CronStatManager;
use Glass\StatManager;

StatManager::endIteration();

?>
