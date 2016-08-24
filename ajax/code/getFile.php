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

    $styleCodes = [
      1 => "text",
      2 => "function",
      3 => "return",
      4 => "new",
      5 => "comment",
      6 => "exec",
      7 => "conditional",
      8 => "global",
      9 => "local",
    ];

    $unicodeChars = [
      1 => json_decode('"\u1001"'),
      2 => json_decode('"\u1002"'),
      3 => json_decode('"\u1003"'),
      4 => json_decode('"\u1004"'),
      5 => json_decode('"\u1005"'),
      6 => json_decode('"\u1006"'),
      7 => json_decode('"\u1007"'),
      8 => json_decode('"\u1008"'),
      9 => json_decode('"\u1009"'),
    ];

    $str = htmlspecialchars($str);

    $str = preg_replace('((\&quot;).*?(\&quot;))', $unicodeChars[1] . "$0</span>", $str);

    $str = str_replace("\t", "  ", $str);
    $str = str_replace("function", $unicodeChars[2] . "function</span>", $str);
    $str = str_replace("package", $unicodeChars[2] . "package</span>", $str);
    $str = str_replace("return", $unicodeChars[3] . "return</span>", $str);
    //$str = str_replace("new ", $unicodeChars[4] . "new</span> ", $str);

    $str = preg_replace('((?<=(new\s)).*(?=\())', $unicodeChars[6] . "$0</span>", $str);

    $str = preg_replace("((?<=(\s|\())exec)", $unicodeChars[6] . "$0</span>", $str);

    $str = preg_replace("((?<=(\s|\{))((if)|(switch)[$]|for|while))", $unicodeChars[7] . "$0</span>", $str);

    $str = preg_replace("(((\s==)|(=<)|(=>)|(\!=)|(\\$=)|(\!\\$=))[^=])", $unicodeChars[7] . "$0</span>", $str);

    $str = preg_replace('(\/\/.*)', $unicodeChars[5] . "$0</span>", $str);

    $str = preg_replace('((?<=(\s|\())\$[^=]+?(?=(\s|\)|\,|\;)))', $unicodeChars[8] . "$0</span>", $str);
    $str = preg_replace('((?<=(\s|\())\%.*?(?=(\)|\s|\.|\,|\[|\]|\;)))', $unicodeChars[9] . "$0</span>", $str);

    $str = preg_replace('((?<=(\s|\())[a-zA-Z0-9]*(?=(::|\.)))', "<span class=\"mu_object\">$0</span>", $str);

    $str = preg_replace('((?<=(\())[a-zA-Z]*(?=\)))', "<span class=\"mu_object\">$0</span>", $str);

    foreach($unicodeChars as $id=>$unicode) {
      $str = str_replace($unicode, '<span class="mu_' . $styleCodes[$id] . '">', $str);
    }

    //$str = preg_replace('((?<=(\s|\())\$[^=]+?\s)', "<span class=\"mu_global\">$0</span>", $str);

    if(strlen(trim($str)) == 0) {
      $result->message = "<i>This file is empty!</i>";
    } else {
      $result->source = $str;
    }

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
