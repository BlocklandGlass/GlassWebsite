<?php

//requirements
use Glass\AddonManager;
header('Content-Type: text/json');

//function definition
function buildFileTree($fileArray) {
  $tree = new stdClass();

  $tree->files = [];
  $tree->dirs = [];
  $tree->abs = "root";
  $tree->root = true;

  $directories = [];
  foreach($fileArray as $file) {
    if(strrpos($file, "/") === false) {
      $tree->files[] = $file;
      continue;
    }

    $dir = substr($file, 0, strrpos($file, "/"));
    $filename = substr($file, strrpos($file, "/")+1);

    if(!isset($directories[$dir])) {
      $directories[$dir] = new stdClass();
      $directories[$dir]->files = [];
      $directories[$dir]->dirs = [];
      $directories[$dir]->abs = $dir;
    }

    if($filename !== false) {
      $directories[$dir]->files[] = $filename;
    }
  }


  uksort($directories, function($objA, $objB) {
    $a = substr_count($objA, "/");
    $b = substr_count($objB, "/");

    if ($a == $b) {
      return 0;
    } else if ($a > $b) {
      return 1;
    } else {
      return -1;
    }
  });

  foreach($directories as $dir=>$obj) {
    $depth = substr_count($dir, "/");

    if($depth == 0) {
      $tree->dirs[$dir] = $obj;
    } else {
      $parent = substr($dir, 0, strrpos($dir, "/"));
      $child = substr($dir, strrpos($dir, "/")+1);
      $directories[$parent]->dirs[$child] = $obj;
    }
  }

  //$tree->directories = $directories;

  return $tree;
}

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
  for ($i = 0; $i < $zip->numFiles; $i++) {
    $fn = $zip->getNameIndex($i);

    $files[] = $fn;
  }
  $result->tree = buildFileTree($files);

  $result->status = "success";
} else {
  $result->status = "error";
  $result->error = "Failed to open zip";
  die(json_encode($result, JSON_PRETTY_PRINT));
}


echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
