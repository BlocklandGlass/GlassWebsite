<?php
use Glass\ScreenshotManager;
$ss = ScreenshotManager::getFromId($_GET['id']);
echo "<img src=\"" . $ss->getUrl() . "\" />";
?>
