<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\GroupManager;
	use Glass\UserManager;
	use Glass\AddonManager;
	use Glass\RTBAddonManager;

	$_PAGETITLE = "Blockland Glass | Add-Ons";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));

	$user = UserManager::getCurrent();
?>
<div class="maincontainer">
	<?php include(realpath(dirname(__DIR__) . "/../private/searchbar.php")); ?>
	<ul class="addonnav tile">
		<li><a href="/addons/boards.php">Boards</a></li>
		<li><a href="/addons/rtb/">RTB Archive</a></li>
		<?php
		if($user && $user->inGroup("Reviewer")) {
		?>
		<li><a class="addonnavmod" href="review/list.php">Review<?php if(sizeof(AddonManager::getUnapproved()) > 0) { echo " <span class=\"notice\">!</span>"; } ?></a></li>
		<li><a class="addonnavmod" href="review/updates.php">Updates<?php if(sizeof(AddonManager::getPendingUpdates()) > 0) { echo " <span class=\"notice\">!</span>"; } ?></a></li>
		<li><a class="addonnavmod" href="review/reclaims.php">Reclaims<?php if(sizeof(RTBAddonManager::getPendingReclaims()) > 0) { echo " <span class=\"notice\">!</span>"; } ?></a></li>
		<?php } ?>
	</ul>

	<div style="display: flex; flex-wrap: wrap;">
		<div style="flex-grow: 1; flex-shrink: 1; overflow-x: auto;">
			<div style="text-align: center; margin-top: 15px;">
				<h3>Weekly Downloads</h3>
			</div>

			<div class="tile">
				<?php include(realpath(dirname(__DIR__) . "/ajax/getTrendingAddons.php")); ?>
			</div>
		</div>

		<div style="flex-grow: 1; flex-shrink: 1; overflow-x: auto;">
			<div style="text-align: center; margin-top: 15px;">
				<h3>Recent Uploads</h3>
			</div>

			<div class="tile">
				<?php include(realpath(dirname(__DIR__) . "/ajax/getNewAddons.php")); ?>
			</div>
		</div>
	</div>
</div>
<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
