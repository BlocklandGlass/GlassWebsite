<?php
	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<?php
		echo "<span style=\"font-size: 9pt;\"><a href=\"/addons/\">Add-Ons</a> >> ";
		echo "<a href=\"#\">" . htmlspecialchars($addonObject->getName()) . "</a></span>";
		echo "<h2>" . htmlspecialchars($addonObject->getName()) . "</h2>";
	?>
	<div style="margin-bottom: 15px; display: inline-block; width: 100%; font-size: 0.8em; background-color: #ffcccc; text-align:center; border-radius: 10px; padding: 10px; font-size: 1em">
		<b>This add-on was rejected by a Glass Reviewer:</b><br />
    <i>"<?php echo $addonObject->getRejectReason() ?>"</i>
  </div>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/private/footer.php"));
?>
