<?php
	require dirname(__DIR__) . '/../../private/autoload.php';

	use Glass\RTBAddonManager;
	use Glass\AWSFileManager;
	use Glass\AddonManager;

  $addonData = RTBAddonManager::getAddonFromId($_GET['id']);

	$_PAGETITLE = htmlspecialchars($addonData->title) . " | Blockland Glass";

	include(realpath(dirname(__DIR__) . "/../../private/header.php"));
?>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
  ?>
	<span style="font-size: 9pt;"><a href="/addons/">Add-Ons</a> >> <a href="/addons/rtb/">RTB Archive</a> >> <a href="board.php?name=<?php echo $addonData->type; ?>"><?php echo $addonData->type; ?></a> >> <a href="#"><?php echo htmlspecialchars($addonData->title); ?></a></span>
		<div class="tile">
		<?php
			echo "<h2>" . htmlspecialchars($addonData->title) . "</h2>";
		?>
		<div style="margin-bottom: 15px; display: inline-block; width: 100%; font-size: 0.8em">
			<div class="addoninfoleft">
				<image style="height:1.5em" src="/img/icons32/folder_vertical_zipper.png" />
				<?php
				echo $addonData->filename;
				?>
				<br />
				<br />
        <?php
        if($addonData->glass_id == 0 || $addonData->approved != 1) {
          echo 'Are you the former owner of this add-on? You can regain your former userbase by reclaiming the add-on! <a href="reclaim.php?id=' . $_GET['id'] . '">Click here!</a>';
        }
        ?>
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
			echo '<a href="http://' . AWSFileManager::getBucket() . '/rtb/' . $addonData->filename .  '" class="btn dlbtn ' . $class . '"><strong>' . ucfirst($id) . '</strong><span style="font-size:9pt"><br />Imported Archive</span></a>';
			?>
		</div>
		<?php } else {
			$addon = AddonManager::getFromId($addonData->glass_id);
			?>
			<p style="text-align:center">This add-on has been imported to <a href="/addons/addon.php?id=<?php echo $addon->getId(); ?>"><?php echo $addon->getName() ?></a></p>
		<?php } ?>
	</div>
</div>
<?php include(realpath(dirname(__DIR__) . "/../../private/footer.php")); ?>
