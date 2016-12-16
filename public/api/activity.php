<?php
$returnArray = array();

$retObj = new stdClass();
$retObj->image = "Update";
$retObj->text = "An update is available!";
$retObj->datestring = date("g:ia",time());
$retObj->timestamp = time();

$returnArray[] = $retObj;

echo json_encode($returnArray, JSON_PRETTY_PRINT);
?>
