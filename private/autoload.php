<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
spl_autoload_register(function ($class) {
  $parts = explode('\\', $class);
  $path = dirname(__FILE__) . '/class/' . end($parts) . '.php';
  require_once $path;
});

?>
