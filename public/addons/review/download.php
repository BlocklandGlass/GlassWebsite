<?php
require dirname(__DIR__) . '/../../private/autoload.php';
//requirements
use Glass\AddonManager;
use Glass\UserManager;

$current = UserManager::getCurrent();

if(!$current) {
  $result->status = "error";
  $result->error = "Not logged in.";
  die(json_encode($result, JSON_PRETTY_PRINT));
}

if(!$current->inGroup("Reviewer")) {
  $result->status = "error";
  $result->error = "Access denied.";
  die(json_encode($result, JSON_PRETTY_PRINT));
}

$file = $_REQUEST['file'] ?? "";

if(strpos($file, "..") !== false) {
  $result->status = "error";
  $result->error = "Illegal path: " . $file;
  die(json_encode($result, JSON_PRETTY_PRINT));
}

$filepath = dirname(__DIR__) . "/../../filebin/" . $file;

$fo = fopen($filepath, 'r');
if(!$fo) {
  $result->status = "error";
  $result->error = "Failed to open resource " . $filepath;
  die(json_encode($result, JSON_PRETTY_PRINT));
}

$filesize = filesize($filepath);
$contents = fread($fo, $filesize);
fclose($fo);

if(!ctype_print($contents)) {
  // header('Content-Type: application/octet-stream');
  header('Content-Type: application/zip');
  header('Content-Length: ' . $filesize);
  header('Content-Disposition: attachment; filename=' . substr($file, strrpos($file, "/")+1) . '.zip');
}

echo($contents);