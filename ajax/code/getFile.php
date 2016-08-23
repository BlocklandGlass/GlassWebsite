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
if(!is_file($filePath) || !is_readable($filePath)) {
  $result->status = "error";
  $result->error = "Failed to find readable zip";
  die(json_encode($result, JSON_PRETTY_PRINT));
}

$zip = new ZipArchive();
$res = $zip->open($filePath);
if($res === TRUE) {
  $files = array();

  $ext = substr($file, strrpos($file, ".")+1);

  $allowed = [
    "txt",
    "cs",
    "gui",
    "json",
    "md",
    "blb"
  ];

  $raw = [
    "jpg",
    "jpeg",
    "png"
  ];

  if(in_array(strtolower($ext), $allowed)) {
    $str = $zip->getFromName($file);

    if($str === false) {
      $result->status = "error";
      $result->error = "Failed to open file in zip";
      die(json_encode($result, JSON_PRETTY_PRINT));
    }
    $str = preg_replace('((\").*?(\"))', "<span class=\"mu_text\">$0</span>", $str);

    $str = str_replace("\t", "  ", $str);
    $str = str_replace("function", "<span class=\"mu_function\">function</span>", $str);
    $str = preg_replace("((?<=(\s|\())exec)", "<span class=\"mu_exec\">$0</span>", $str);
    $str = str_replace("return", "<span class=\"mu_return\">return</span>", $str);
    $str = str_replace("new", "<span class=\"mu_new\">new</span>", $str);

    $str = preg_replace('(\/\/.*)', "<span class=\"mu_comment\">$0</span>", $str);

    $str = preg_replace('((?<=(\s|\())\$[^=]+?(?=(\s|\)|\,)))', "<span class=\"mu_global\">$0</span>", $str);
    $str = preg_replace('((?<=(\s|\())\%.*?(?=(\)|\s|\.|\,|\[|\])))', "<span class=\"mu_local\">$0</span>", $str);





    //$str = preg_replace('((?<=(\s|\())\$[^=]+?\s)', "<span class=\"mu_global\">$0</span>", $str);

    $result->source = $str;
    $result->file = $file;
    $result->status = "success";
  } else if(in_array(strtolower($ext), $raw)) {
    $result->image = true;
    $result->file = $file;
    $result->status = "success";
  } else {
    $result->message = "Unsupported file type.";
    $result->file = $file;
    $result->status = "success";
  }
} else {
  $result->status = "error";
  $result->error = "Failed to open zip";
  die(json_encode($result, JSON_PRETTY_PRINT));
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
