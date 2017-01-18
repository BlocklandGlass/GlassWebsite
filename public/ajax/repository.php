<?php
  require_once dirname(__DIR__) . '/../private/autoload.php';

  use Glass\RepositoryManager;

  $action = $_POST['action'] ?? false;
  switch($action) {
    case "add":
      $addon   = $_POST['aid'] ?? false;
      $url     = $_POST['url'] ?? false;
      $type    = $_POST['type'] ?? false;
      $channel = $_POST['channel'] ?? false;

      if($addon && $url && $channel && $type) {
        $ret = RepositoryManager::validateRepository($addon, $url, $type, $channel);
        if($ret['status'] == "success") {
          $ret = RepositoryManager::addRepositoryToAddon($addon, $url, $type, $channel);
          die(json_encode($ret));
        } else {
          die(json_encode($ret));
        }
      } else {
        echo("Missing fields");
      }
      break;

    case "delete":
      $addon   = $_POST['aid'] ?? false;
      RepositoryManager::removeRepositoryFromAddon($addon);
      $ret = [
        "status" => "Deleted"
      ];
      die(json_encode($ret));
      break;

    default:
      echo("Default?");

  }
 ?>
