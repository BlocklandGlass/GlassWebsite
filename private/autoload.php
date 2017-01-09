<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
spl_autoload_register(function ($class) {
  $parts = explode('\\', $class);
  $path = dirname(__FILE__) . '/class/' . end($parts) . '.php';
  if(!is_file($path)) {
    throw new \ErrorException("Bad Require");
  }
  require_once $path;
});

?>
