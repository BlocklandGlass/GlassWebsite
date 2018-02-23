<?php
require_once dirname(__DIR__) . '/../../private/autoload.php';
use Glass\ServerTracker;

header('Content-Type: text/json');

$reqIp = $_GET['ip'] ?? false;
$reqPort = $_GET['port'] ?? false;
if(!$reqIp || !$reqPort) {
  $error = new \stdClass();
  $error->status = "error";
  $error->error = "Missing fields";
  die(json_encode($error));
}

$servers = ServerTracker::getActiveServers();

$res = new \stdClass();

foreach($servers as $s) {
  $host = utf8_encode($s->host);
  $ip = $s->ip;
  $port = $s->port;

  if($ip != $reqIp || $port != $reqPort)
    continue;

  $res->host = $host;
  $res->ip = $ip;
  $res->port = $port;

  $clients = json_decode($s->clients);

  $res->clients = $clients;

  echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  die();
}

$error = new \stdClass();
$error->status = "error";
$error->error = "Server not found";
die(json_encode($error));
?>
