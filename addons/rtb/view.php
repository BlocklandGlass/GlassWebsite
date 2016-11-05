<?php
	include(realpath(dirname(__DIR__) . "/../private/class/RTBAddonManager.php"));
	include(realpath(dirname(__DIR__) . "/../private/class/AWSFileManager.php"));
	include(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));

  $addonData = RTBAddonManager::getAddonFromId($_GET['id']);

	$_PAGETITLE = "Blockland Glass | " . utf8_encode($addonData->title);

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
?>
<div class="maincontainer">
	<span style="font-size: 9pt;"><a href="/addons/">Add-Ons</a> >> <a href="/addons/rtb/">RTB Archives</a> >> <a href="board.php?name=<?php echo $addonData->type; ?>"><?php echo $addonData->type; ?></a> >> <a href="#"><?php echo htmlspecialchars($addonData->title); ?></a></span>
  <?php
		echo "<h2>" . utf8_encode($addonData->title) . "</h2>";
	?>
	<div style="margin-bottom: 15px; display: inline-block; width: 100%; font-size: 0.8em">
		<div class="addoninfoleft">
			<image style="height:1.5em" src="/img/icons32/folder_vertical_zipper.png" />
			<?php
			echo $addonData->filename;
			?>
			<br />
			<br />
			Are you the former owner of this add-on? You can regain your former userbase by reclaiming the add-on! <a href="reclaim.php?id=<?php echo $_GET['id'] ?>">Click here!</a>
		</div>
	</div>
	<hr />
	<div style="text-align:center"><img src="/img/rtb_logo.gif"></div>
	<hr />
	<?php
	if($addonData->glass_id == 0 || $addonData->approved != 1) { ?>
	<div style="text-align: center">
		<?php
		$id = "RTB";
		$class = "red";
		echo '<a href="http://' . AWSFileManager::getBucket() . '/rtb/' . $addonData->filename .  '" class="btn dlbtn ' . $class . '"><b>' . ucfirst($id) . '</b><span style="font-size:9pt"><br />Imported Archive</span></a>';
		?>
	</div>
	<?php } else {
		$addon = AddonManager::getFromId($addonData->glass_id);
		?>
		<p style="text-align:center">This add-on has been imported to <a href="/addons/addon.php?id=<?php echo $addon->getId(); ?>"><?php echo $addon->getName() ?></a></p>
	<?php } ?>
</div>
<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
