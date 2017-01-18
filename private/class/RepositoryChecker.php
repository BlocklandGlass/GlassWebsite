<?php

namespace Glass;

class RepositoryChecker {
  public static function getRepository($address, $type) {
    if(strpos($address, "http") === false) {
      $address = "http://" . $address;
    }

    if(filter_var($address, FILTER_VALIDATE_URL) === false) {
      throw new \Exception("$address is not a valid URL");
      return;
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $address,
        CURLOPT_USERAGENT => "GlassRepositoryChecker",
        CURLOPT_FAILONERROR => true,
        CURLOPT_FOLLOWLOCATION => false
    ));

    $text   = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if($status !== 200) {
      throw new \Exception("Failed to fetch $address: status $status");
    }

    if($text === false || $text == "") {
      throw new \Exception("Failed to get contents of $address");
    }

    $type = strtolower($type);
    if($type == "tml") {
      return RepositoryChecker::parseTML($text);
    } else if($type == "json") {
      return RepositoryChecker::parseJSON($text);
    }
  }

  public static function parseJSON($text) {
    $obj = json_decode($text);
    if($obj == false) {
      throw new \Exception("Failed to parse JSON");
    } else {
      $key = "add-ons";
      $obj->addons = $obj->$key;
      return $obj;
    }
  }

  public static function parseTML($text) {
    $repo = new \stdClass();
    $repo->name = "Nameless Repository";
    $repo->addons = array();

    $idx = 0;
    $ln = 0;
    $lnIdx = 0;
    $tagChain = [];
    $currentAddon = null;

    while($idx < strlen($text)) {
      $char = substr($text, $idx, 1);

      if($char == "<") {
        $colon = strpos($text, ":", $idx+1);
        $end = strpos($text, ">", $idx+1);
        if($end < $colon) {
          $tag = substr($text, $idx+1, $end-$idx-1);
          $val = null;
        } else {
          $tag = substr($text, $idx+1, $colon-$idx-1);
          $val = substr($text, $colon+1, $end-$colon-1);
        }

        if(substr($tag, 0, 1) == "/") {
          $topTag = $tagChain[sizeof($tagChain)-1];
          $tagType = substr($tag, 1);
          if($tagType == $topTag) {
            array_pop($tagChain);
          } else {
            throw new \Exception("Unexpected '$tag' at $ln:$lnIdx");
          }

          switch($tag) {
            case "/addon":
              $repo->addons[] = $currentAddon;
              $currentAddon = null;
              $currentChannel = null;
              break;

            case "/channel":
              $currentAddon->channels[] = $currentChannel;
              $currentChannel = null;
              break;

            case "/repository":
              break;
          }
        } else {
          //non-closing tags
          $ignore = [
            "desc",
            "version",
            "restartRequired",
            "file",
            "crc",
            "changelog"
          ];

          if(!in_array($tag, $ignore)) {
            array_push($tagChain, $tag);
          }

          switch($tag) {
            case "repository":
              $repo->name = $val;
              break;

            case "addon":
              $currentAddon = new \stdClass();
              $currentAddon->name = $val;
              $currentAddon->channels = [];
              break;

            case "channel":
              $currentChannel = new \stdClass();
              $currentChannel->name = $val;


            case "version":
              if($currentChannel != null) {
                $currentChannel->version = $val;
              }
              break;

            case "desc":
              if($currentChannel != null) {
                $currentChannel->desc = $val;
              }
              break;

            case "restartRequired":
              if($currentChannel != null) {
                $currentChannel->restartRequired = $val;
              }
              break;

            case "changelog":
              if($currentChannel != null) {
                $currentChannel->changelog = $val;
              }
              break;

            case "file":
              if($currentChannel != null) {
                $currentChannel->file = $val;
              }
              break;
          }
        }
      }

      if($char == "\n") {
        $ln++;
        $lnIdx = 0;
      }

      $idx++;
      $lnIdx++;
    }
    return $repo;
  }
}
