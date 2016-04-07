<?php
require_once dirname(__DIR__) . '/class/DatabaseManager.php';
require_once dirname(__DIR__) . '/class/AWSFileManager.php';

$oldDat = json_decode(file_get_contents(dirname(__FILE__) . '/key.json'));
$mysql = new mysqli("localhost", $oldDat->username, $oldDat->password, "blocklandGlass");
$allresource = $mysql->query("SELECT `id` FROM `addon_addons` WHERE `deleted`='0'");
echo "Importing " . $allresource->num_rows . " addons...<hr />";

while(($obj = $allresource->fetch_object()) !== null) {
  $_REQUEST['id'] = $obj->id;
  include 'importAddon.php';
  echo "\n";
}

?>
