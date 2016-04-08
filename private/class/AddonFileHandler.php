<?php
require_once dirname(__FILE__) . '/AddonManager.php';

class AddonFileHandler {
  public static function validateAddon($file) {
    //$workingDir = dirname(__DIR__) . "/../addons/upload/files/";

    $executable = false;
    $desc = false;

    $fullFile = realpath($file);

    $zip = new ZipArchive();
    $res = $zip->open($fullFile);
    if($res === TRUE) {
      for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);

        if($filename == "server.cs" || $filename == "client.cs") {
          $executable = true;
        }

        if($filename == "description.txt") {
          $desc = true;
        }
      }
    } else {
      return false;
    }

    return ($executable && $desc);
  }

  public static function validatePrint() {
    // TODO I have no idea how this works
  }

  public static function validateColorset() {
    $colors = false;
    $desc = false;

    $zip = new ZipArchive;
    if($zip->open($file) == TRUE) {
      for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);

        if($filename == "colorset.txt") {
          $colors = true;
        }

        if($filename == "description.txt") {
          $desc = true;
        }
      }
    }

    return ($executable && $colors);
  }

  public static function injectGlassFile($aid, $file) { //ideally, we create the addonObject and then do all the file work?
    /*
    OLD:
    {
    "formatVersion": 1,
    "id": "24",
    "board": "1",
    "filename": "Weapon_asdf.zip",
    "title": "Cry"
    }
    */

    $addonObject = AddonManager::getFromID($aid);

    $glassData = new stdClass();
    $glassData->formatVersion = 2;
    $glassData->id = $addonObject->getId();
    $glassData->title = $addonObject->getName();
    $glassData->filename = $addonObject->getFilename();

    $workingDir = dirname(dirname(__DIR__)) . "/addons/upload/files/";
    $tempFile = $workingDir . "temp/" . $addonObject->getId() . "glass.json";

    if(!is_dir($workingDir . "temp")) {
      mkdir($workingDir . "temp", 0777, true);
    }

    $res = file_put_contents($tempFile, json_encode($glassData));
    if($res === false) {
      return false;
    }

    $zip = new ZipArchive;
    $res = $zip->open($file);
    if($res === TRUE) {
      $zip->addFile($tempFile, 'glass.json');
      $zip->close();
      unlink($tempFile);
    } else {
      return false;
    }
  }

  public static function injectVersionInfo($aid, $branchId, $file) {
    $addonObject = AddonManager::getFromID($aid);

    /*

    {
        "version": "1.0.0",
        "channel": "stable",
        "repositories": [
            {
                "url": "http://blocklandglass.com/api/support_updater/repo.php",
                "format": "JSON",
                "id": "11"
            },
            {
                "url": "http://cdn.blocklandglass.com/repo.txt",
                "format": "JSON",
                "id": "11"
            }
        ]
    }

    */

    $branchName[1] = "stable";
    $branchName[2] = "beta";

    $versionData = new stdClass();
    $versionData->version = $addonObject->getVersion();
    $versionData->channel = $branchName[$branchId];

    $mainRepo = new stdClass();
    $mainRepo->url = "http://api.blocklandglass.com/api/2/repository.php";
    $mainRepo->format = "JSON";
    $mainRepo->id = $aid;

    $backupRepo = new stdClass();
    $backupRepo->url = "http://cdn.blocklandglass.com/repository.txt";
    $backupRepo->format = "JSON";
    $backupRepo->id = $aid;

    $versionData->repositories = [$mainRepo, $backupRepo];




    $workingDir = dirname(dirname(__DIR__)) . "/addons/upload/files/";
    $tempFile = $workingDir . "temp/" . $addonObject->getId() . "version.json";

    if(!is_dir($workingDir . "temp")) {
      mkdir($workingDir . "temp", 0777, true);
    }

    $res = file_put_contents($tempFile, json_encode($versionData));
    if($res === false) {
      return false;
    }

    $zip = new ZipArchive;
    $res = $zip->open($file);
    if($res === TRUE) {
      $zip->addFile($tempFile, 'version.json');
      $zip->close();
      unlink($tempFile);
    } else {
      return false;
    }
  }

  public static function getVersionInfo($file) {
    $zip = new ZipArchive();
    $res = $zip->open($file);
    if($res === TRUE) {
      if(($json = $zip->getFromName("version.json")) !== false) {
        $obj = json_decode($json);

        $ret = new stdClass();

        $ret->repo = $obj->repositories[0];
        $ret->channel = $obj->channel;
        $ret->version = $obj->version;
        return $ret;
      } else if(($tml = $zip->getFromName("version.txt")) !== false) {
        $ret = new stdClass();
        $ret->repo = new stdClass();
        $lines = explode("\n", $tml);
        foreach($lines as $line) {
          $field = explode("\t", trim($line));
          switch($field[0]) {
            case "channel":
              $ret->channel = $field[1];
              break;

            case "repository":
              $ret->repo->url = $field[1];
              break;

            case "version":
              $ret->version = $field[1];
              break;
          }
        }
        return $ret;
      } else {
        return false;
      }
    } else {
      echo("failed to open");
      return false;
    }

    return ($executable && $desc);
  }
}
