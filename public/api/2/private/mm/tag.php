<?php
use Glass\TagManager;

$ret = new stdClass();
$ret->tags = array();
$tags = TagManager::getAllTags();

foreach($tags as $tag) {
  $rettag = new stdClass();
  $rettag->id = $tag->getId();
  $rettag->name = $tag->getName();
  $rettag->icon = $tag->getIcon();
  $rettag->color = $tag->getColor();
  $ret->tags[] = $rettag;
}

echo json_encode($ret, JSON_PRETTY_PRINT);
?>
