<?php
	require_once(realpath(dirname(__FILE__) . "/StatManager.php"));

	$sid = StatManager::getPreviousStats();
	return StatManager::getFromID($sid);
?>
