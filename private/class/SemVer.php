<?php
class SemVer {
  public $raw;

  public $version;

  public $prereleaseGreek;
  public $prerelease;

  public $buildData;

  public $hasBuildData;
  public $hasPrerelease;

  public static $greek = array("alpha", "beta", "rc");

  public static function sort($tarray) {
    foreach($tarray as $text) {
      try {
        $array[] = $obj = new SemVer($text);
      } catch (Exception $e) {
        //echo $e->getMessage() . "\n";
      }
    }

    usort($array, function($a, $b) {
        return $a->compare($b);
    });
    return $array;
  }

  function __construct($str) {
    $this->raw = $str;

    if($str == "") {
      $str = "0";
    }

    $hasBuildData = $hasPrerelease = false;
    if(strpos($str, "-") !== false) {
      $this->hasPrerelease = $hasPrerelease = true;
    }

    if(strpos($str, "+") !== false) {
      $this->hasBuildData = $hasBuildData = true;
      $this->buildData = substr($str, strpos($str, "+")+1);
    }

    if($hasBuildData && $hasPrerelease) {
      if(strpos($str, "+") < strpos($str, "-")) {
        throw new Exception("Build text must come after prerelease text");
      }
    }

    if($hasPrerelease) {
      $versionText = substr($str, 0, strpos($str, "-"));
    } else if($hasBuildData) {
      $versionText = substr($str, 0, strpos($str, "+"));
    } else {
      $versionText = $str;
    }

    $this->parseVersionText($versionText);

    if($hasPrerelease) {
      if($hasBuildData) {
        $preText = substr($str, strpos($str, "-")+1, strpos($str, "+")-strpos($str, "-")-1);
      } else {
        $preText = substr($str, strpos($str, "-")+1);
      }
      $this->parsePrereleaseText($preText);
    }
  }

  function __toString() {
    return $this->raw;
  }

  function parseVersionText($str) {
    $parts = explode(".", $str);

    if(sizeof($parts) > 3) {
      throw new Exception("Too many fields in version string. Three expected");
    }

    foreach($parts as $part) {
      if(!is_numeric($part)) {
        throw new Exception("Version string not numeric");
      }

      if($part < 0) {
        throw new Exception("Version string cannot be negative");
      }
    }

    $maj = $min = $pat = 0;
    $maj += @$parts[0];
    $min += @$parts[1];
    $pat += @$parts[2];

    $this->version = array($maj, $min, $pat);
  }

  function parsePrereleaseText($str) {
    $str = str_replace("-", ".", $str);
    $parts = explode(".", $str);

    $greek = array_shift($parts);

    if(!in_array(strtolower($greek), SemVer::$greek)) {
      throw new Exception("Invalid prerelease. Expected Alpha, Beta, or RC");
    }

    if(sizeof($parts) > 3) {
      throw new Exception("Too many fields in prerelease string. Three expected");
    }

    foreach($parts as $part) {
      if(!is_numeric($part)) {
        throw new Exception("Prerelease string not numeric");
      }

      if($part < 0) {
        throw new Exception("Prerelease string cannot be negative");
      }
    }

    $maj = $min = $pat = 0;
    $maj += @$parts[0];
    $min += @$parts[1];
    $pat += @$parts[2];

    $this->prereleaseGreek = $greek;
    $this->prerelease = array($maj, $min, $pat);
  }

  //-1 = this is less
  //0 = same
  //1 = this is greater
  function compare($that) {
    for($i = 0; $i < 3; $i++) {
      if($that->version[$i] > $this->version[$i]) {
        return -1;
      } else if($that->version[$i] < $this->version[$i]) {
        return 1;
      }
    }

    if(array_search($that->prereleaseGreek, SemVer::$greek) > array_search($this->prereleaseGreek, SemVer::$greek)) {
      return -1;
    } else if(array_search($that->prereleaseGreek, SemVer::$greek) < array_search($this->prereleaseGreek, SemVer::$greek)) {
      return 1;
    }

    for($i = 0; $i < 3; $i++) {
      if($that->prerelease[$i] > $this->prerelease[$i]) {
        return -1;
      } else if($that->prerelease[$i] < $this->prerelease[$i]) {
        return 1;
      }
    }

    return 0;
  }

  function greaterThan($that) {
    if($this->compare($that) == 1) {
      return true;
    } else {
      return false;
    }
  }

  function lessThan($that) {
    if($this->compare($that) == -1) {
      return true;
    } else {
      return false;
    }
  }

  function isSame($that) {
    if($this->compare($that) == 0) {
      return true;
    } else {
      return false;
    }
  }
}

?>
