<?php
require_once(realpath(dirname(__FILE__) . "/private/header.php"));
require_once(realpath(dirname(__FILE__) . "/private/navigationbar.php"));
require_once(realpath(dirname(__FILE__) . "/private/class/UserLog.php"));
require_once(realpath(dirname(__FILE__) . "/private/class/StatManager.php"));
?>

<div class="bigheadcontainer">
	<h1>Blockland Glass</h1>
	<h2 style="font-weight: normal">A service for the community, by the community</h2>
	<a href="dl.php" class="btn blue"><b>Download</b></a><br />
	<!-- <a href="builds" class="btn green" style="width: 150px">Builds</a> -->
	<a href="addons" class="btn yellow" style="width: 150px">Add-Ons</a><br /><br />
</div>
<div class="maincontainer">
	<p>
		<h3>What's Glass?</h3>
		Blockland Glass is a service made for <a href="http://blockland.us">Blockland</a> to help expand and cultivate the community. Currently, Glass acts as a content and social platform offering the ability to download Glass add-ons ingame, manage your servers' preferences, add friends and talk to others through the chatroom or direct messaging.
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
		echo StatManager::getMasterServerStats()['users'];
		?></b> users. Of those, <a href="stats/users.php"><?php
		echo sizeof(UserLog::getRecentlyActive());
		?></a> users are running Glass - which equates to <?php
		$nonglass = StatManager::getMasterServerStats()['users'];
		$glass = sizeof(UserLog::getRecentlyActive());
		echo 100/$nonglass*$glass;
		?>%! Glass has <?php
		echo UserLog::getUniqueCount();?> active users, with a total of <?php
		$web = StatManager::getAllAddonDownloads("web");
		$ingame = StatManager::getAllAddonDownloads("ingame");
		$updates = StatManager::getAllAddonDownloads("updates");
		echo $web+$ingame+$updates; ?> downloads.
	</p>
	<br />
	<p>
		<h3>Want to get involved?</h3>
		As you can probably tell, this site points straight to the old one. The old one was developed independently and is very distasteful. In an attempt to modernize the site, this new one is entirely open source. Please, contribute on <a href="https://github.com/BlocklandGlass">GitHub</a> and check out the <a href="https://forum.blockland.us/index.php?topic=284376.0">Glass Development Topic</a> over on the Blockland Forums!
	</p>
</div>

<?php require_once(realpath(dirname(__FILE__) . "/private/footer.php")); ?>
