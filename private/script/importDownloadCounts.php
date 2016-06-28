<?php
require_once dirname(__DIR__) . '/class/DatabaseManager.php';
return;
$db = new DatabaseManager();

$json = file_get_contents("http://blocklandglass.com/downloadsExport.php");
$data = json_decode($json, true);

foreach($data as $id=>$dat) {
  $web = $dat['downloads_web'];
  $ig = $dat['downloads_ingame'];
  $up = $dat['downloads_update'];
  $total = $web+$ig+$up;

  $db->query("UPDATE `addon_stats` SET `totalDownloads` = (`totalDownloads` + $total),
  `webDownloads` = (`webDownloads` + $web),
  `ingameDownloads` = (`ingameDownloads` + $ig),
  `updateDownloads` = (`updateDownloads` + $up) WHERE `aid`=$id");
  echo($db->error());
}

?>
