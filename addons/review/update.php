<?php
require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
$addon = AddonManager::getFromID($_REQUEST['id']);
$updates = AddonManager::getUpdates($addon);
$up = $updates[0];
AddonManager::approveUpdate($up);
header('Location: updates.php');
?>
