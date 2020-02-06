<?php
require dirname(__DIR__) . '/../../private/autoload.php';

use Glass\UserManager;
use Glass\GroupManager;
use Glass\InstallationManager;

ini_set('max_execution_time', 0);

$user = UserManager::getCurrent();

if($user === false || !$user->inGroup("Administrator")) {
  header('Location: /');
  die();
}

if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
  throw new \Exception("Cross site request forgery attempt blocked");
}

$keyData = json_decode(file_get_contents(dirname(__DIR__) . "/../../private/config.json"));
$path = (dirname(__DIR__) . "/../../private/{$keyData->database}.bak");

if(InstallationManager::isWindows()) {
  exec("mysqldump.exe {$keyData->database} --user=\"{$keyData->username}\" --password=\"{$keyData->password}\" --result-file=\"{$path}\"", $data);
} else {
  exec("mysqldump {$keyData->database} --user=\"{$keyData->username}\" --password=\"{$keyData->password}\" --result-file=\"{$path}\" 2>&1", $data);
}

if(!file_exists($path)) {
  die('Backup operation failed.'); 
}

$fileSize = filesize($path);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"{$keyData->database}.bak\"");
header("Content-Length: {$fileSize}");

$pipe1 = fopen($path, 'rb');
$pipe2 = fopen('php://output', 'wb');

stream_copy_to_stream($pipe1, $pipe2);

fclose($pipe1);
fclose($pipe1);

unset($path);

exit(0);