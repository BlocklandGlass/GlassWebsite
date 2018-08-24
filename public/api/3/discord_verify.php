<?php
require dirname(__DIR__) . '/../../private/autoload.php';

use Glass\DiscordKeyManager;

header('Content-Type: text/json');
$key  = $_REQUEST['key'] ?? false;
$discord = $_REQUEST['discord'] ?? false;

if($key === false || $discord === false) {
  return;
}

$blid = DiscordKeyManager::verifyKey($key);

$ret = new stdClass();

if(!$blid) {
  $ret->status = "bad-key";
  die(json_encode($ret));
}

$res = DiscordKeyManager::linkDiscordBlid($blid, $discord);

if($res === false) {
  $ret->status = "already-linked";
  die(json_encode($ret));
}

$ret->status = "success";
$ret->blid = $blid;
die(json_encode($ret));
?>
