<?php
require_once dirname(__DIR__) . '/../../private/autoload.php';
use Glass\AddonManager;
header('Content-Type: text/tml');

$addon = AddonManager::getFromId($_REQUEST['id']);
if($addon === false) {
  die("Error: add-on doesn't exist");
}

$updates = AddonManager::getUpdates($addon);
foreach($updates as $up) {
  if(!$up->isApproved()) continue;

  echo "<version:" . $up->getVersion() . ">\n";
  echo $up->getChangeLog();
  echo "\n</version>\n";
}
?>
