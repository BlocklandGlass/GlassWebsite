<?php
	include(realpath(dirname(__DIR__) . "/../private/class/RTBAddonManager.php"));

  $addonData = RTBAddonManager::getAddonFromId($_GET['id']);

	$_PAGETITLE = "Glass | " . htmlspecialchars($addonData->title);

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
?>
<div class="maincontainer">
	<span style="font-size: 9pt;"><a href="/addons/">Addons</a> >> <a href="/addons/rtb/">RTB Archives</a> >> <a href="board.php?name=<?php echo $addonData->type; ?>"><?php echo $addonData->type; ?></a> >> <a href="#"><?php echo htmlspecialchars($addonData->title); ?></a></span>
  <?php
		echo "<h2>" . htmlspecialchars($addonData->title) . "</h2>";
	?>
	<div style="margin-bottom: 15px; display: inline-block; width: 100%; font-size: 0.8em">
		<div class="addoninfoleft">
			<image style="height:1.5em" src="http://blocklandglass.com/icon/icons32/folder_vertical_zipper.png" />
			<?php
			echo $addonData->filename;
			?>
			<br />
		</div>
	</div>
	<hr />
	<div style="text-align:center"><img src="/img/rtb_logo.gif"></div>
	<hr />
	<div style="text-align: center">
		<?php
		$id = "RTB";
		$class = "red";
		echo '<a href="http://cdn.blocklandglass.com/rtb/' . $addonData->filename .  '" class="btn dlbtn ' . $class . '"><b>' . ucfirst($id) . '</b><span style="font-size:9pt"><br />Imported Archive</span></a>';
		?>
	</div>
</div>
<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
