<?php
require_once(realpath(dirname(__DIR__) . "/private/class/ScreenshotManager.php"));
$ss = ScreenshotManager::getFromId($_GET['id']);
echo "<img src=\"" . $ss->getUrl() . "\" />";
?>
