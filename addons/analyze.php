<?php
//header('Content-Type: text/json');
//echo json_encode($obj, JSON_PRETTY_PRINT);

function parseTS_dir($dir) {
  //we need to run parseTS on each file individually
  $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
  $ret = array();
  foreach($objects as $name => $object) {
    if($object->getExtension() == "cs" || $object->getExtension() == "gui") {
      $ret[$name] = parseTS($name);
    }
  }
  return $ret;
}

function parseTS($file) {
  $cmd = realpath(dirname(__DIR__)) . '/private/lib/parsets lint "' . $file . '"';

  $res = shell_exec($cmd);
  return json_decode($res);
}

//var_dump(parseTS_dir(realpath(dirname(__FILE__))));
?>
