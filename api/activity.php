<?php
$returnArray = array();

$retObj = new stdClass();
$retObj->image = "refresh";
$retObj->text = "An update is available!";
$retObj->datestring = time();
$retObj->timestamp = time();

$returnArray[] = $retObj;

echo json_encode($returnArray, JSON_PRETTY_PRINT);
?>
