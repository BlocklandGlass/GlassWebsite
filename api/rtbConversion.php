<?php
require_once dirname(__DIR__) . '/class/DatabaseManager.php';
$db = new DatabaseManager();

$mods = split("-", $_GET['mods']);
$sqlString = "";
foreach($mods as $mod) {
	if($sqlString != "") {
		$sqlString = $sqlString . " OR ";
	}
	$sqlString = $sqlString . "rtbId='" . $db->sanitize($mod) . "'";
}



$conversions = array();
$result = $db->query("SELECT * FROM `addon_rtb` WHERE glassId IS NOT NULL AND (" . $sqlString . ")");
while($obj = $result->fetch_object()) {
	$addonRes = $db->query("SELECT `name`,`id`,`filename` FROM `addon_addons` WHERE id=" . $obj->glassId);
	$obj->addonData = $addonRes->fetch_object();
	$conversions[] = $obj;
}

echo json_encode($conversions);
?>
