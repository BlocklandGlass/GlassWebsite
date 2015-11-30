<?php
class AddonFileHandler {
  public static function validateAddon($file) {
    $workingDir = dirname(__DIR__) . "/../addons/upload/files/";

    $executable = false;
    $desc = false;

    $fullFile = $workingDir . $file;

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
    if($zip->open($workingDir . $file) == TRUE) {
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

  private static function injectGlassFile($addonObject, $file) { //ideally, we create the addonObject and then do all the file work?
    /*
    {
    "formatVersion": 1,
    "id": "24",
    "board": "1",
    "filename": "Weapon_asdf.zip",
    "title": "Cry"
    }
    */

    // TODO we need to find a way to issue an override out to clients.. aka keep a local manifest.
    // that way, we can change tags and boards without reissuing the file

    $glassData = new stdClass();
    $glassData->formatVersion = 2;
    $glassData->id = $addonObject->getId();
    $glassData->board = $addonObject->getBoardId();
    $glassData->title = $addonObject->getName();
    $glassData->filename = $addonObject->getFilename();
    $glassData->tags = []; // TODO

    $res = file_put_contents($workingDir . "temp/" . $addonObject->getId() . "glass.json", json_encode($glassData));
    if($res === false) {
      return false;
    }

    $zip = new ZipArchive;
    $res = $zip->open($workingDir . $file);
    if($res === TRUE) {
      $zip->addFile($workingDir . "temp/" . $addonObject->getId() . "glass.json", 'glass.json');
    } else {
      return false;
    }
  }

  private static function injectVersionInfo() {

  }

  public static function getVersionInfo($file) {
    $workingDir = dirname(__DIR__) . "/../addons/upload/files/";

    $executable = false;
    $desc = false;

    $fullFile = $workingDir . $file;

    $zip = new ZipArchive();
    $res = $zip->open($fullFile);
    if($res === TRUE) {
      // TODO open version.json
    } else {
      return false;
    }

    return ($executable && $desc);
  }
}
