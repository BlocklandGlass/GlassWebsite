<?php
//requirements
require_once realpath(dirname(__DIR__) . "/../private/class/AddonManager.php");
header('Content-Type: text/json');

//function definition


//start
$result = new stdClass();
$result->status = "undefined";

if(!isset($_REQUEST['id'])) {
  $result->status = "error";
  $result->error = "Missing field: id";
  die(json_encode($result, JSON_PRETTY_PRINT));
}

$id = $_REQUEST['id'];
if(!is_numeric($id) || $id < 1) {
  $result->status = "error";
  $result->error = "Invalid field: id";
  die(json_encode($result, JSON_PRETTY_PRINT));
}

if(!isset($_REQUEST['file'])) {
  $result->status = "error";
  $result->error = "Missing field: file";
  die(json_encode($result, JSON_PRETTY_PRINT));
}

$file = $_REQUEST['file'];

$addon = AddonManager::getFromId($id);
if($addon === false) {
  $result->status = "error";
  $result->error = "Failed to get AddonObject";
  die(json_encode($result, JSON_PRETTY_PRINT));
}

$filePath = dirname(__DIR__) . '/../addons/files/local/' . $addon->getId() . '.zip';

$fp = fopen("zip://" . realpath($filePath) . "#" . $file, 'r');
if(!$fp) {
  $result->status = "error";
  $result->error = "Failed to open resource";
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
    header('Content-Disposition: attachment; filename=' . substr($file, strrpos($file, "/")+1));
  }
  echo($contents);
  //$result->status = "error";
  //$result->error = "Unsupported filetype";
  die(json_encode($result, JSON_PRETTY_PRINT));
}
