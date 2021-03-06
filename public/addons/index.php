<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\GroupManager;
	use Glass\UserManager;
	use Glass\AddonManager;
	use Glass\RTBAddonManager;

	$_PAGETITLE = "Add-Ons | Blockland Glass";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));

	$user = UserManager::getCurrent();
?>
<style>
  .flex-container {
    display: flex;

    flex-flow: row wrap;
    margin: 5px;
  }

  .flex-body {
    flex-grow: 1;
    flex-shrink: 1;
    flex: 1;
    overflow-x: auto;

    word-wrap: break-word;
    min-width: 270px;
  }

  @media only screen and (max-width: 768px) {
    .flex-container {
      flex-flow: column wrap;
      margin: 0;
    }

    .flex-body {
      display: inline-block;
    }
  }
</style>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
    include(realpath(dirname(__DIR__) . "/../private/subnavigationbar.php"));
  ?>
	<div class="flex-container">
		<div class="flex-body">
			<div style="text-align: center; margin-top: 15px;">
				<h3>Weekly Downloads</h3>
			</div>

			<div class="tile">
				<?php include(realpath(dirname(__DIR__) . "/ajax/getTrendingAddons.php")); ?>
			</div>
		</div>

		<div class="flex-body">
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
