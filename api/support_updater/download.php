<?php
require_once dirname(dirname(__DIR__)) . '/class/DatabaseManager.php';
$db = new DatabaseManager();
$aid = $db->sanitize($_GET['id']);
$bid = $db->sanitize($_GET['branch']);
$branch = "";
if($bid == 1) {
	$branch = "file_stable";
} else if($bid == 2) {
	$branch = "file_testing";
} else if($bid == 3) {
	$branch = "file_dev";
}

$addonResult = $db->query("SELECT * FROM `addon_addons` WHERE `id`=" . $aid);
$addonObj = $addonResult->fetch_object();

$fileResult = $db->query("SELECT * FROM `addon_files` WHERE `id`=" . $addonObj->$branch);
$fileObj = $fileResult->fetch_object();

$file = '../../files/comp/' . $fileObj->hash . '.zip';

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename='. $addonObj->filename);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
		if(isset($_GET["ingame"])) {
			$db->query("UPDATE `blocklandGlass`.`addon_addons` SET `downloads_ingame` = '" . ($addonObj->downloads_ingame+1) . "' WHERE `addon_addons`.`id` = " . $aid . ";");
    } else {
			$db->query("UPDATE `blocklandGlass`.`addon_addons` SET `downloads_update` = '" . ($addonObj->downloads_update+1) . "' WHERE `addon_addons`.`id` = " . $aid . ";");
		}
}
?>
