<?php
require dirname(__DIR__) . '/../../private/autoload.php';

require_once dirname(__FILE__) . "/private/ClientConnection.php";
header('Content-Type: text/json');

use Glass\UserManager;
use Glass\DiscordKeyManager;

function unauthorized() {
  http_response_code(403);
  die();
}

function badParameters() {
  http_response_code(400);
  die();
}

$ident  = $_GET['ident'] ?? false;
$ip     = $_SERVER['REMOTE_ADDR'];

$client = false;
if($ident) {
  $client = ClientConnection::loadFromIdentifier($ident);

  if($client === false || !$client->checkIp($ip)) {
    unauthorized();
  }
} else {
  badParameters();
}

$json   = file_get_contents('php://input');
$object = json_decode($json);

$key = DiscordKeyManager::newKey($client->getBlid());
echo $key;
