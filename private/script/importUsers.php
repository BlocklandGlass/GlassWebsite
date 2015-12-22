<?php
require_once dirname(__DIR__) . '/class/DatabaseManager.php';
require_once dirname(__DIR__) . '/class/AWSFileManager.php';

$oldDat = json_decode(file_get_contents(dirname(__FILE__) . '/key.json'));
$mysql = new mysqli("localhost", $oldDat->username, $oldDat->password, "blocklandGlass");
$resource = $mysql->query("SELECT * FROM `users` WHERE `verified`=1");

$database = new DatabaseManager();

$database->query("delete from `users`");

while($user = $resource->fetch_object()) {
  $database->query("INSERT INTO `blocklandglass2`.`users` (`username`, `blid`, `password`, `email`, `salt`, `registration_date`, `session_last_active`, `verified`, `banned`, `admin`, `profile`) " .
  "VALUES ('" . $user->username . "', '" . $user->blid . "', '" . $user->password . "', '', '" . $user->salt . "', CURRENT_TIMESTAMP, '" . $user->session_last_active . "', '1', '0', '0', NULL);");
}


?>
