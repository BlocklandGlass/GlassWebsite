<?php
//a lot of this is just going to use staight up mysql since I dont want
//to duplicate the old classes

require_once dirname(__DIR__) . '/class/DatabaseManager.php';
require_once dirname(__DIR__) . '/class/AWSFileManager.php';
require_once dirname(__DIR__) . '/class/StatManager.php';

$oldDat = json_decode(file_get_contents(dirname(__FILE__) . '/key.json'));
$dir = $oldDat->dir;
$db = new DatabaseManager();
$mysql = new mysqli("localhost", $oldDat->username, $oldDat->password, "blocklandGlass");

//We'll have a temporary board for "legacy" add-ons

$aid = $_REQUEST['id'];

$resource = $mysql->query("SELECT * FROM `addon_addons` WHERE `id`='" . $aid . "'");
$res = $resource->fetch_object();

$authorDat = array();
$author = new stdClass();
$author->blid = $res->author;
$author->main = true;
$author->role = "";
$authorDat[] = $author;

$branchId["stable"] = 1;
$branchId["unstable"] = 2;
$branchId["development"] = 3;
$file["stable"] = $res->file_stable;
$versionData = array();
foreach($file as $branch=>$fid) {
  if($fid != 0) {
    $version = new stdClass();
    $fileRes = $mysql->query("SELECT * FROM `addon_files` WHERE `id`='" . $fid . "'");

    $hash = $fileRes->fetch_object()->hash;
    $oldfile = $dir . $hash . ".zip";
    $bid = $branchId[$branch];
    echo "Uploading $oldfile to AWS as {$res->id}_{$bid}.zip<br />";
    //AWSFileManager::upload("addons/{$res->id}_{$bid}", $oldfile);
    AWSFileManager::uploadNewAddon($res->id, $bid, $res->filename, $oldfile);

    $updateRes = $mysql->query("SELECT *
FROM  `addon_updates`
WHERE  `aid` = '" . $aid . "'
AND  `branch`='" . $bid . "' ORDER BY  `time` DESC
LIMIT 0 , 1");
    if($updateRes->num_rows == 0) {
      $version->version = "0.0.0";
      $version->restart = "0.0.0";
    } else {
      $obj = $updateRes->fetch_object();
      $version->version = $obj->version;
      $version->restart = $obj->version; //not worth it
    }
    $versionData[$branch] = $version;
  }
}

$db->query($sql = "INSERT INTO `addon_addons` (`id`, `board`, `blid`, `name`, `filename`, `description`, `version`, `authorInfo`, `reviewInfo`, `deleted`, `approved`, `uploadDate`) VALUES " .
   "('" . $db->sanitize($res->id) . "',"
  . "NULL,"
  . "'" . $db->sanitize($res->author) . "'," //now that I think of it, we need account migration too
  . "'" . $db->sanitize($res->name) . "',"
  . "'" . $db->sanitize($res->filename) . "',"
  . "'" . $db->sanitize($res->description) . "',"
  . "'" . $db->sanitize($versionData['stable']->version) . "',"
  . "'" . $db->sanitize(json_encode($authorDat)) . "',"
  . "'',"
  . "'0',"
  . "'0',"
  . "CURRENT_TIMESTAMP);");

echo "Imported";

echo($db->error());
?>
