<?php
use Glass\BoardManager;

$ret = new stdClass();
$ret->boards = array();
$boards = BoardManager::getAllBoards();

foreach($boards as $board) {
  $retboard = new stdClass();
  $retboard->id = $board->getId();
  $retboard->name = $board->getName();
  $retboard->video = "";
  $retboard->icon = $board->getIcon();
  $retboard->description = $board->getDescription();
  $ret->boards[] = $retboard;
}

$ret->status = "success";

echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
