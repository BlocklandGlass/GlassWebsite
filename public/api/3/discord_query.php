<?php
require dirname(__DIR__) . '/../../private/autoload.php';

use Glass\DiscordKeyManager;
use Glass\UserLog;

header('Content-Type: text/json');
$blid    = $_REQUEST['blid'] ?? false;
$discord = $_REQUEST['discord'] ?? false;
$secret = $_REQUEST['secret'] ?? false;

$ret = new stdClass();

if(($blid === false && $discord === false) || $secret === false) {
  $ret->status = "bad-arguments";
  die(json_encode($ret));
  return;
}

if(!DiscordKeyManager::checkSecret($secret)) {
  $ret->status = "bad-secret";
  die(json_encode($ret));
  return;
}

if($blid) {
  $ret->blid = $blid;
  $ret->discord = DiscordKeyManager::getDiscord($blid);
} else if($discord) {
  $ret->blid = DiscordKeyManager::getBlid($discord);
  $ret->discord = $discord;
} else {
  $ret->status = "bad-arguments";
  die(json_encode($ret));
}

$ret->username = UserLog::getCurrentUsername($ret->blid);

$ret->status = "success";
die(json_encode($ret, JSON_PRETTY_PRINT));
?>
