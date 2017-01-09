<?php
require dirname(__DIR__) . '/autoload.php';
header('Content-Type: text/json');
use Glass\CronStatManager;
use Glass\StatManager;

StatManager::endIteration();

?>
