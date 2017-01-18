<?php
namespace Glass;

require_once dirname(__FILE__) . '/DatabaseManager.php';
require_once dirname(__FILE__) . '/RepositoryChecker.php';
require_once dirname(__FILE__) . '/AddonManager.php';

class RepositoryManager {
  private static $repoCache = [];
  public static function addRepositoryToAddon($addon, $url, $type, $channel) {
    if(!is_object($addon)) {
      $addon = AddonManager::getFromId($addon);
    }

    if(RepositoryManager::getRepository($addon) !== false) {
        return ["status" => "error",
                "error"  => "Already has repository"];
    }

    $db = new DatabaseManager();
    RepositoryManager::verifyTable($db);

    $url    = $db->sanitize($url);
    $type   = $db->sanitize($type);
    $channel= $db->sanitize($channel);
    $aid    = $db->sanitize($addon->getId());

    $res = $db->query("INSERT INTO `addon_repositories` (aid, url, type, channel) VALUES ('$aid', '$url', '$type', '$channel');");

    if($res) {
      return ["status" => "success"];
    } else {
      return ["status" => "error",
              "error"  => "Database Error: " . $db->error()];
    }
  }

  public static function getRepository($addon) {
    if(!is_object($addon)) {
      $addon = AddonManager::getFromId($addon);
    }

    $db = new DatabaseManager();
    RepositoryManager::verifyTable($db);

    $aid = $db->sanitize($addon->getId());

    $res = $db->query("SELECT * FROM `addon_repositories` WHERE `aid`='$aid'");
    if($res) {
      if($obj = $res->fetch_object()) {
        return $obj;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public static function validateRepository($addon, $url, $type, $channel) {
    if(!is_object($addon)) {
      $addon = AddonManager::getFromId($addon);
    }

    $name = substr($addon->getFilename(), 0, -4);

    try {
      $repoObj = RepositoryChecker::getRepository($url, $type);
    } catch(\Exception $e) {
      return ["status" => "error",
              "error"  => "Error reading repo: " . $e->getMessage()];
    }

    $addons = $repoObj->addons;

    foreach($addons as $ao) {
      if($ao->name == $name) {

        foreach($ao->channels as $channelObj) {
          if($channelObj->name == $channel || $channelObj->name == "*") {
            return ["status" => "success",
                    "error"  => "Found add-on and channel " . $channelObj->name];
          }
        }

        return ["status" => "error",
                "error"  => "Found add-on but couldn't find channel '$channel'"];

      }
    }

    return ["status" => "error",
            "error"  => "Couldn't find '$name' in {$repoObj->name}"];
  }

  public static function getAddonUpstream($addon) {
    if(!is_object($addon)) {
      $addon = AddonManager::getFromId($addon);
    }

    $name = substr($addon->getFilename(), 0, -4);
    $repoInfo = RepositoryManager::getRepository($addon);

    if($repoCache[$repoInfo->url] ?? false) {
      $repoObj = $repoCache[$repoInfo->url];
    } else {
      try {
        $repoObj = RepositoryChecker::getRepository($repoInfo->url, $repoInfo->type);
        $channel = $repoInfo->channel;
      } catch(\Exception $e) {
        return ["status" => "error",
                "error"  => "Error reading repo: " . $e->getMessage()];
      }
    }

    $addons = $repoObj->addons;

    foreach($addons as $ao) {
      if($ao->name == $name) {

        foreach($ao->channels as $channelObj) {
          if($channelObj->name == $channel || $channelObj->name == "*") {
            return ["status" => "success",
                    "data" => $channelObj];
          }
        }

        return ["status" => "error",
                "error"  => "Found add-on but couldn't find channel '$channel'"];

      }
    }

    return ["status" => "error",
            "error"  => "Couldn't find '$name' in repo '{$repoObj->name}''"];
  }

  public static function checkAllRepositories() {
    $db = new DatabaseManager();
    RepositoryManager::verifyTable($db);

    $result = $db->query("SELECT * FROM `addon_repositories`");

    echo "Found " . $result->num_rows . " results\n";

    if($result) {
      while($obj = $result->fetch_object()) {
        echo "\n";
        $addon = AddonManager::getFromId($obj->aid);
        $res = RepositoryManager::getAddonUpstream($addon);
        if($res['status'] == "error") {
          echo "Error getting upstream for {$obj->aid}: {$res['error']}\n";
        } else {
          $data = $res['data'];
          if($addon->getVersion() != $data->version) {
            echo "Update found for " . $addon->getName() . " [" . $addon->getVersion() . " -> " . $data->version . "]\n";

            $pending = false;
            $updates = AddonManager::getUpdates($addon);
            foreach($updates as $update) {
              if($update->isPending()) {
                if($update->getVersion() == $data->version) {
                  echo "Update is already pending...\n";
                  $pending = true;
                  continue;
                } else {
                  AddonManager::cancelUpdate($update->getId());
                }
              }
            }

            if($pending)
              continue;

            echo "Downloading...\n";

            $start = microtime(true);
            $path = dirname(__DIR__) . "/../filebin/upstream/";
            $file = $path . $addon->getFilename();

            if(!file_exists(dirname($file))) {
              mkdir(dirname($file), 0777, true);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $data->file);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $contents = curl_exec($ch);

            curl_close ($ch);
            $f = fopen($file, "w+");
            fputs($f, $contents);
            fclose($f);

            echo "File is " . floor(filesize($file)/(1024)) . "Kb\n";
            echo "Completed in " . round(microtime(true) - $start, 3) . "s\n";

            $version = $data->version;
            $changelog = "Upstream Update";
            $restart = $data->restartRequired == $data->version;
            AddonManager::submitUpdate($addon, $version, $file, $changelog, $restart);
          }
        }
      }
    }
  }

  public static function verifyTable($database) {
		UserManager::verifyTable($database);

		if(!$database->query("CREATE TABLE IF NOT EXISTS `addon_repositories` (
  			`id` INT NOT NULL AUTO_INCREMENT,

  			`aid` INT NOT NULL,

  			`url` TEXT NOT NULL,
  			`type` TEXT NOT NULL,
  			`channel` TEXT NOT NULL,

  			FOREIGN KEY (`aid`)
  				REFERENCES addon_addons(`id`)
  				ON UPDATE CASCADE
  				ON DELETE CASCADE,

  			PRIMARY KEY (`id`),

        UNIQUE KEY (`aid`)
      )
      ")) {
			throw new \Exception("Error creating table: " . $database->error());
		}
  }
}
