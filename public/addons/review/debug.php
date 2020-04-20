<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require dirname(__DIR__) . '/../../private/autoload.php';

use Glass\AddonManager;
use Glass\UserManager;

$userObject = UserManager::getCurrent();

if(!$userObject || !$userObject->inGroup("Administrator"))
  die();

try {
  $addon = AddonManager::getFromID(11);
  $update = AddonManager::getUpdates($addon)[0];

  AddonManager::approveUpdate($update);
} catch(Exception $e) {
  echo($e->getMessage());
}