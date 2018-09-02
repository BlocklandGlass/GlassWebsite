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


$fp = fopen(dirname(__DIR__) . "/../../filebin/" . $file, 'r');
if(!$fp) {
  $result->status = "error";
  $result->error = "Failed to open resource " . (dirname(__DIR__) . "/../../filebin/" . $file);
  die(json_encode($result, JSON_PRETTY_PRINT));
}

$contents = '';
while(!feof($fp)) {
  $contents .= fread($fp, 2);
}
fclose($fp);

$ext = substr($file, strrpos($file, ".")+1);
$contentTypes = [
  "png" => "image/png",
  "jpg" => "image/jpeg",
  "jpeg" => "image/jpeg",
];

if(isset($contentTypes[$ext])) {
  header('Content-Type: ' . $contentTypes[$ext]);
  echo($contents);
} else {
  if(!ctype_print($contents)) {
    header('Content-Type: application/ocelot-stream');
    header('Content-Disposition: attachment; filename=' . substr($file, strrpos($file, "/")+1) . '.zip');
  }
  echo($contents);
  //$result->status = "error";
  //$result->error = "Unsupported filetype";
  die(json_encode($result, JSON_PRETTY_PRINT));
}
