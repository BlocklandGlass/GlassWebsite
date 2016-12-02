<?php
require_once dirname(__DIR__) . "/../../../private/class/BoardManager.php";

$ret = new stdClass();
$ret->groups = array();

$groups = BoardManager::getBoardGroups();

foreach($groups as $group) {
  $boards = BoardManager::getGroup($group);

  $groupObj = new stdClass();
  $groupObj->name = $group;
  $groupObj->boards = array();

  foreach($boards as $board) {
    $retboard = new stdClass();
    $retboard->id = $board->getId();
    $retboard->name = $board->getName();
    $retboard->icon = $board->getIcon();

    $groupObj->boards[] = $retboard;
  }

  $ret->groups[] = $groupObj;
}

$ret->status = "success";

echo json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
