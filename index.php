<?php
require_once(realpath(dirname(__FILE__) . "/private/class/InstallationManager.php"));

if(InstallationManager::checkInstallation() == false) {
	header('Location: /install/');
	return;
}

require_once(realpath(dirname(__FILE__) . "/private/header.php"));
require_once(realpath(dirname(__FILE__) . "/private/navigationbar.php"));
require_once(realpath(dirname(__FILE__) . "/private/class/UserLog.php"));
require_once(realpath(dirname(__FILE__) . "/private/class/StatManager.php"));

$testEnvironment = false;
if(is_file(dirname(__FILE__) . '/private/test.json')) {
	$testEnvironment = true;
}
?>

<div class="bigheadcontainer">
	<!-- <h1>Blockland Glass</h1> -->
	<image style="margin: 10px 0px 5px 0px; max-width: 100%" src="/img/logoWhite.png" />
	<h2 style="font-weight: normal">A service for the community, by the community</h2>
	<a href="dl.php" class="btn blue"><b>Download</b></a><br />
	<!-- <a href="builds" class="btn green" style="width: 150px">Builds</a> -->
	<a href="addons" class="btn yellow" style="width: 150px">Add-Ons</a><br /><br />
</div>
<div class="maincontainer">
	<?php
	if($testEnvironment) {
	?>
	<p>
		<h3>Glass Test Site</h3>
		Welcome to the Glass test site! This is not the same as our normal site. The test site operates independently with it's own database and file system. Changes on the test site reflect future versions of the live Glass website.
	</p>
	<br />
	<?php
	}
	?>
	<p>
		<h3>What's Glass?</h3>
		Blockland Glass is a service made for <a href="http://blockland.us">Blockland</a> to help expand and cultivate the community. Currently, Glass acts as a content and social platform offering the ability to download Glass add-ons in-game, manage your servers' preferences, add friends and talk to others through the chatroom or direct messaging.
	</p>
	<br />
	<p>
		<h3>By the community?</h3>
		Glass is intended to be a group project. Although the bulk of the work has been fronted individually, we're striving to move to an open-source site, allowing for Glass to be a truely community made project.
	</p>
	<br />
	<p>
		<h3>Live Stats</h3>
		Right now, there's <b><?php
		echo StatManager::getMasterServerStats()['servers'];
		?></b> Blockland servers online with <b><?php
		echo $ct = StatManager::getMasterServerStats()['users'];
		?></b> <?php echo ($ct == 1 ? "user" : "users") ?>. Of those, <a href="stats/users.php"><?php
		echo $ct = sizeof(UserLog::getRecentlyActive());
		?></a> <?php echo ($ct == 1 ? "user" : "users") ?> are running Glass - which equates to <?php
		$nonglass = StatManager::getMasterServerStats()['users'];
		$glass = sizeof(UserLog::getRecentlyActive());
        $percentage = floor(100/$nonglass*$glass);
		if($percentage > 100)
            echo "<b>" . $percentage . "%</b> (how is this happening)";
        else
            echo "<b>" . $percentage . "%</b>";
		?>
		of Blockland as of this moment. Glass has <b>
		<?php
		echo $ct = UserLog::getUniqueCount();
		?>
		</b>
        active <?php echo ($ct == 1 ? "user" : "users") ?>, with a total of <a href="stats/"><?php
		$web = StatManager::getAllAddonDownloads("web");
		$ingame = StatManager::getAllAddonDownloads("ingame");
		$updates = StatManager::getAllAddonDownloads("updates");
		echo $web+$ingame; ?></b></a> downloads and <b><?php echo $updates; ?></b> updates.
	</p>
	<br />
	<p>
		<h3>Want to get involved?</h3>
		Blockland Glass is an open-source project open to any contributions. If you're interested, please contribute on <a href="https://github.com/BlocklandGlass">GitHub</a> and check out the <a href="https://forum.blockland.us/index.php?topic=284376.0">Blockland Glass Topic</a> over on the Blockland Forums!
	</p>
</div>

<?php require_once(realpath(dirname(__FILE__) . "/private/footer.php")); ?>
