<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
use Glass\ScreenshotManager;

if(isset($_POST['delete'])) {
  if($_POST['delete'] == 1) {
    ScreenshotManager::deleteScreenshot($_POST['sid']);
  }
}
$_screenshotContext = "addon";
$res = include(realpath(dirname(__DIR__) . "/../../private/json/uploadScreenshot.php"));

$screenshots = ScreenshotManager::getScreenshotsFromAddon($_GET['id']);
foreach($screenshots as $sid) {
  $ss = ScreenshotManager::getFromId($sid);
  echo "<div style=\"padding: 5px; margin: 10px 10px; background-color: #eee; display:inline-block; width: 128px; vertical-align: middle\">";
  echo "<form target=\"\" method=\"post\">";
  echo "<a href=\"/addons/screenshot.php?id=" . $sid . "\">";
  echo "<img src=\"" . $ss->getThumbUrl() . "\" /></a><br />";
  echo "<input type=\"hidden\" name=\"sid\" value=\"$sid\" />";
  echo "<input type=\"image\" src=\"/img/icons16/delete.png\" name=\"delete\" value=\"1\" />";
  echo "</form>";
  echo "</div>";
}
?>
<hr /><h3><?php echo $res['message']; ?></h3>
<form target="" method="post" enctype="multipart/form-data">
<input type="file" name="uploadfile" id="uploadfile"> <br />
<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
<input type="submit" name="submit" value="Upload"/>
</form>
