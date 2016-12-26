<?php
namespace Glass;

use Glass\SemVer;
use Glass\UserManager;

class UploadManager {
  public static function getStatus($submission, $file) {
    if(!isset($submission['csrftoken'])) {
      return [
        "message" => "Upload an add-on!"
      ];
    }

  	$user = UserManager::getCurrent();

    $name = $submission['addonname'] ?? "";
    $summary = $submission['summary'] ?? "";
    $boardId = $submission['board'] ?? false;
    $description = $submission['description'] ?? "";
    $filename = $submission['filename'] ?? "";
    $version = $submission['version'] ?? "";

    //================================
    // Submission Validation
    //================================
    $problems = array();

    //name
    $name = trim($name);

    if(strlen($name) == 0) {
      $problems[] = "Missing name";
    } if(strlen($name) < 3) {
      $problems[] = "Add-On name is too short!";
    }

    //summary
    $summary = trim($summary);

    if(strlen($summary) == 0) {
      $problems[] = "Missing summary";
    }

    //board
    if(!$boardId) {
      $problems[] = "No board selected";
    } else {
      $board = BoardManager::getFromID($boardId);
      if(!$board) {
        $problems[] = "Invalid board";
      }
    }

    //description
    $description = trim($description);

    if(strlen($description) == 0) {
      $problems[] = "Missing description";
    }

    //filename
    $filename = trim($filename);

    if(strlen($filename) == 0) {
      $problems[] = "Missing description";
    }

    $idx = strpos($filename, "_");
    if($idx === false || $idx == 0 || $idx == strlen($filename)-1) {
      $problems[] = "Invalid filename";
    }

    if(strpos($filename, ".zip") === false) {
      $filename = $filename . ".zip";
    }

    //version
    try {
      $sem = new SemVer($version);
    } catch(\Exception $e) {
      $problems[] = "Invalid version: " . $e->getMessage();
    }

    if(sizeof($problems) > 0) {
      return [
        "message" => "There were issues with your upload",
        "problems" => $problems,
        "values" => $submission
      ];
    }

    //================================
    // File Validation
    //================================

    if(empty($file['name'])) {
      $problems[] = "No file was uploaded";
    } else if(pathinfo($file['name'], PATHINFO_EXTENSION) != "zip") {
  		$problems[] = "Only .zip files are allowed";
  	}

    if(sizeof($problems) > 0) {
      return [
        "message" => "There were issues with your upload",
        "problems" => $problems,
        "values" => $submission
      ];
    }

    $tempPath = $file['tmp_name'];
    $newPath = dirname(dirname(__DIR__)) . '/filebin/upload/' . $user->getBlid() . '.' . time() . '.zip';

    if(!file_exists(dirname($newPath))) {
      mkdir(dirname($newPath), 0777, true);
    }

    move_uploaded_file($tempPath, $newPath);
    chmod($newPath, 0777);

    //================================
    // Add-On Validation
    //================================

    $valid = AddonFileHandler::validateAddon($newPath)
      || AddonFileHandler::validateColorset($newPath)
      || AddonFileHandler::validatePrint($newPath);

    if(!$valid) {
      return [
        "message" => "Your add-on is missing required files",
        "values" => $submission
      ];
    }

    //================================
    // Finishing Up
    //================================

    return AddonManager::uploadNewAddon($user, $boardId, $name, $newPath, $filename, $description, $summary, $version);
  }

  public static function handleAJAXScreenshot($aid, $file) {
    $res = new \stdClass();

    $addon = AddonManager::getFromID($aid);
    if($addon === false) {
      $res->status = "error";
      $res->error = "Add-On not found!";
      return $res;
    }

    if(empty($file['name'])) {
      $res->status = "error";
      $res->error = "No file was uploaded!";
      return $res;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if($ext != "png" && $ext != "jpg" && $ext != "jpeg") {
      $res->status = "failed";
  		$res->message = "Only png and jpeg files are allowed";
      return $res;
  	}

    $tempPath = $file['tmp_name'];
    $newPath = dirname(dirname(__DIR__)) . '/filebin/upload/' . $addon->getId() . '.screenshot.' . time() . '.' . $ext;

    move_uploaded_file($tempPath, $newPath);
    chmod($newPath, 0777);

    try {
      ScreenshotManager::uploadScreenshotForAddon($addon, $ext, $newPath);
      $res->status = "success";
      return $res;
    } catch(\Exception $e) {
      $res->status = "error";
      $res->error = "Exception: " . $e->getMessage();
      return $res;
    }
  }
}

 ?>
