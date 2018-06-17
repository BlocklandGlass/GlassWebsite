<?php
	require dirname(__DIR__) . '/../private/autoload.php';
  $_PAGETITLE = "Blockland Glass | " . utf8_encode($addonObject->getName());

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
?>
<div class="maincontainer">
	<?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php")); #636

		echo "<span style=\"font-size: 9pt;\"><a href=\"/addons/\">Add-Ons</a> >> ";
		echo "<a href=\"#\">" . utf8_encode($addonObject->getName()) . "</a></span>";
		echo "<h2>" . utf8_encode($addonObject->getName()) . "</h2>";
	?>
	<div style="margin-bottom: 15px; display: inline-block; width: 100%; font-size: 0.8em">
		This add-on has already been approved.
  </div>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/../private/footer.php"));
?>
