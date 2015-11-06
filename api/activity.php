<?php
require_once dirname(__DIR__) . '/class/NotificationHandler.php';
require_once dirname(__DIR__) . '/class/UserHandler.php';
$returnArray = array();

$beg = 0;
$end = 20;

foreach(NotificationHandler::loadRange($beg, $end) as $note) {
  $name = $note->type;
  $noteObj = new $name(json_decode($note->data));
  $noteObj->setIngame(true);

  $time = strtotime($note->timestamp);

  $retObj = new stdClass();
  $retObj->image = $noteObj->getImage();
  $retObj->text = $noteObj->__toString();
  $retObj->datestring = $note->timestamp;
  $retObj->timestamp = $time;

  $returnArray[] = $retObj;
}

echo json_encode($returnArray, JSON_PRETTY_PRINT);
?>
