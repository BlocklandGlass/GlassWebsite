<?php
  require_once dirname(__DIR__) . '/../private/autoload.php';
  use Glass\UploadManager;

  header('Content-Type: text/json');

  $id = $_REQUEST['id'] ?? false;
  $file = $_FILES['image'] ?? false;

  $res = new stdClass();

  if($id === false || $file === false) {
    $res->status = "error";
    $res->error = "Missing parameter(s)";
  } else {
    try {
      $res = UploadManager::handleAJAXScreenshot($id, $file);
    } catch (\Exception $e) {
      $res->status = "error";
      $res->error = $e->getMessage();
    }
  }

  echo json_encode($res, JSON_PRETTY_PRINT);
?>
