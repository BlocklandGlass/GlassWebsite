<?php
	require_once(realpath(dirname(__FILE__) . "/StatManager.php"));

	$stats = StatManager::getPreviousStats();
	return $stats;
?>
