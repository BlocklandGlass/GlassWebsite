<?php
header('Content-Type: text/json');

$files = array();
foreach(scandir(dirname(__FILE__)) as $file) {
  if(strpos($file, ".gft")) {
    $files[] = $file;
  }
}

echo json_encode($files);
?>
