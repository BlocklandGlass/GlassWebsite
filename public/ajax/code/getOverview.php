<?php
//requirements
use Glass\AddonManager;
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

$addon = AddonManager::getFromId($id);
if($addon === false) {
  $result->status = "error";
  $result->error = "Failed to get AddonObject";
  die(json_encode($result, JSON_PRETTY_PRINT));
}

$result->title = $addon->getName();
$result->authorName = "Blockhead";
$result->filename = $addon->getFilename();
$result->status = "success";

echo json_encode($result, JSON_PRETTY_PRINT);
?>
