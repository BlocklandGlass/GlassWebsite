<?php
use Glass\BuildManager;
use Glass\ScreenshotManager;
$bid = $_REQUEST['id'];

$buldObject = BuildManager::getFromID($bid);
//$screens = ScreenshotManager::getScreenshotsFromAddon($aid); //I dont think this is done

$ret = new \stdClass();

$ret->bid = $bid;
$ret->filename = $info = (new SplFileInfo($buldObject->getFilename()))->getFilename();
$ret->name = $buldObject->getName();
$ret->description = htmlspecialchars_decode($buldObject->getDescription());

$ret->screenshots = array();
/*foreach($screens as $screen) {
  $screenshot = new \stdClass();
  $screenshot->id = $screen->getId();
  $screenshot->url = "http://api.blocklandglass.com/files/screenshots/" . $addonObject->getId() . "/" . $i . ".png";
  $screenshot->thumbnail = "http://api.blocklandglass.com/files/screenshots/" . $addonObject->getId() . "/" . $i . "_thumb.png";
  list($width, $height) = getimagesize(dirname(__DIR__) . "/files/screenshots/" . $addonObject->getId() . "/" . $i . ".png");
  $screenshot->extent = $width . " " . $height;
  $ret->screenshots[] = $screenshot;
}*/

$author = new \stdClass();
//$author->blid = $addonObject->getAuthor()->getBlid();
//$author->name = $addonObject->getAuthor()->getName();
$ret->authors = $author;

echo json_encode($ret, JSON_PRETTY_PRINT);
?>
