<?php
    $_PAGETITLE = "Blockland Glass | " . utf8_encode($addonObject->getName());

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<?php
		echo "<span style=\"font-size: 9pt;\"><a href=\"/addons/\">Add-Ons</a> >> ";
		echo "<a href=\"#\">" . utf8_encode($addonObject->getName()) . "</a></span>";
		echo "<h2>" . utf8_encode($addonObject->getName()) . "</h2>";
	?>
	<div style="margin-bottom: 15px; display: inline-block; width: 100%; font-size: 0.8em">
		This add-on has not been approved by a Glass Reviewer yet.
  </div>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/private/footer.php"));
?>
