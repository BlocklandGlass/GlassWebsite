<?php
header('Content-Type: text');

if(isset($_GET['doc'])) {
  $doc = $_GET['doc'];

  if(strpos($doc, ".txt") === false) {
    $doc .= ".txt";
  }

  echo file_get_contents(dirname(__FILE__) . '/private/docs/' . $doc);
} else {
  $path = dirname(__FILE__) . '/private/docs/';
  $files = scandir($path);

  foreach($files as $file) {
    if(strpos($file, ".") === 0)
      continue;
    echo "$file\n";
  }
}
