<?php
require_once dirname(__DIR__) . "/../../../private/class/BoardManager.php";

$ret = new stdClass();
$ret->boards = array();
$boards = BoardManager::getAllBoards();

foreach($boards as $board) {
  $retboard = new stdClass();
  $retboard->id = $board->getId();
  $retboard->name = $board->getName();
  $retboard->video = $board->getVideo();
  $retboard->description = $board->getDescription();
  $ret->boards[] = $retboard;
}

echo json_encode($ret, JSON_PRETTY_PRINT);
?>
