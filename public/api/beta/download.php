<?php

$file = dirname(__DIR__) . '/../beta/System_BlocklandGlass.zip';
if(file_exists($file)) {
  header('Content-Type: application/zip');
  header('Content-Disposition: filename="System_BlocklandGlass.zip"');
  echo file_get_contents($file);
} else {
  http_response_code(404);
  echo "No beta!";
}
