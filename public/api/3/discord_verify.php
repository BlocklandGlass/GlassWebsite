<?php
require dirname(__DIR__) . '/../../private/autoload.php';

use Glass\DiscordKeyManager;

header('Content-Type: text/json');
$key  = $_REQUEST['key'] ?? false;
$discord = $_REQUEST['discord'] ?? false;
$secret = $_REQUEST['secret'] ?? false;

if($key === false || $discord === false || $secret === false) {
  return;
}

if(!DiscordKeyManager::checkSecret($secret)) {
  http_response_code(403);
  return;
}

$blid = DiscordKeyManager::verifyKey($key);

$ret = new stdClass();

if(!$blid) {
  $ret->status = "bad-key";
  die(json_encode($ret));
}

$linkRes = DiscordKeyManager::linkDiscordBlid($blid, $discord);

if($linkRes !== true) {

  if($linkRes === false) {
    $ret->status = "link-failed";
    die(json_encode($ret));
  } else if(is_numeric($linkRes)) {
    $ret->status = "already-linked";
    $ret->discord = $linkRes;
    die(json_encode($ret));
  }

}

$ret->status = "success";
$ret->blid = $blid;
die(json_encode($ret));
?>
