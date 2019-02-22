<?php
	require_once dirname(__DIR__) . '/private/autoload.php';
	use Glass\InstallationManager;

	if(InstallationManager::checkInstallation() == false) {
		header('Location: /install/');
		return;
	}

	require_once(realpath(dirname(__DIR__) . "/private/header.php"));

	use Glass\UserLog;
	use Glass\StatManager;

	$testEnvironment = false;
	if(is_file(dirname(__FILE__) . '/private/test.json')) {
		$testEnvironment = true;
	}
?>
<script>

var imageIndex = 0;
function nextImage() {
	var images = $('.home-slideshow');
	var curImage = $(images[imageIndex]);

	imageIndex++;
	if(imageIndex >= images.length) {
		imageIndex = 0;
	}

	var nextImage = $(images[imageIndex]);

	curImage.css('z-index', '90');
	nextImage.css('z-index', '91');

	nextImage.css('left', '100%');
	nextImage.show();
	nextImage.animate({'left': '0%'}, 700);
	setTimeout(function() {
		curImage.hide();
	}, 750);
}

setInterval(nextImage, 5000);
</script>
<style>
.navcontainer {
	margin-bottom: 0;
}
</style>
<div class="maincontainer">
	<?php
  require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
  ?>
<div class="bigheadcontainer">
	<div class="home-slideshow">
		<img src="/img/home/IcePalace.jpg" />
	</div>
	<div class="home-slideshow" style="display:none">
		<img src="/img/home/GlassElevators.jpg" />
	</div>
	<div class="home-slideshow" style="display:none">
		<img src="/img/home/Parthenon.jpg" />
	</div>

	<div class="home-head">
		<div style="padding-top: 50px;">
			<image style="margin: 10px 0px 5px 0px; max-width: 100%" src="https://blocklandglass.com/img/logoWhite.png" />
			<h2 style="font-weight: normal">A service for the community, by the community</h2>
			<a href="dl.php" class="btn blue" style="width: 150px">Download</a>
			<a href="addons" class="btn yellow" style="width: 150px">Add-Ons</a><br /><br />
		</div>
	</div>
</div>
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
  <div class="home-content">
		<div>
			<h3>What's Glass?</h3>
	    <p>
	      Blockland Glass is a service made for <a href="http://blockland.us">Blockland</a> to help expand and cultivate the community.  Glass acts as a content and social platform offering the ability to download Glass add-ons in-game, manage your servers' preferences, add friends and talk to others through the chatroom or direct messaging.
	    </p>
		</div>
		<div style="float:right; overflow:none; max-width: 100%">
			<img src="/img/index_img_1.png" style="margin: 10px;  max-width: 100%;">
		</div>
		<div>
			<h3 style="display: inline-block; margin: 0">Mod Manager</h3>
			<p>
				The Mod Manager allows you to browse, search, and install add-ons without ever exiting Blockland. You're able to access all add-ons upload directly to Glass, as well as search and download add-ons from the RTB 4 Archive. The Mod Manager also ensures that all of your add-ons are kept up to date, thanks to Support_Updater, and import your old RTB add-ons to be updated to the latest version.
			</p>
		</div>
		<div>
			<h3>Glass Live</h3>
			<p>
				Our social system, dubbed Glass Live, allows you to keep in touch with your friends, chat through public chatrooms or direct message, and join and invite your friends to servers.
			</p>
		</div>
		<div>
			<h3>Preferences</h3>
			<p>
				We've implemented our own preferences system to make up for the loss of RTB preferences. All RTB preferences are automatically imported and available to control, along with some new preference types and options in particular.
			</p>
		</div>
		<div>
			<h3>Server Features</h3>
			<p>
				Glass enables you to preview servers before you join them, viewing the server's preview image and player list. On top of that, we allow you to mark favorite servers, giving you notifications about the server's status and allowing you to view and join it from the main menu. Glass also allows servers to have their own custom loading screen images, similar to how maps images worked before shadows and shaders.
			</p>
		</div>
		<div>
			<h3>Live Stats</h3>
	    <p>
	      Right now, there are <strong><?php
	      echo number_format(StatManager::getMasterServerStats()['servers']);
	      ?></strong> Blockland servers online with <strong><?php
	      echo $ct = number_format(StatManager::getMasterServerStats()['users']);
	      ?></strong> <?php echo ($ct == 1 ? "user" : "users") ?>. <sup title="Blockland servers with Glass installed are also included in the users statistic.">(disclaimer)</sup><br>
        Glass has delivered a total of <a href="stats/"><?php
	      $web = StatManager::getAllAddonDownloads("web")+0;
	      $ingame = StatManager::getAllAddonDownloads("ingame")+0;
	      $updates = StatManager::getAllAddonDownloads("updates")+0;
	      echo number_format($web+$ingame); ?></strong></a> add-on downloads and <strong><?php echo number_format($updates); ?></strong> updates.
	    </p>
		</div>
		<div>
			<h3>Want to get involved?</h3>
	    <p>
	      Blockland Glass is an open-source project open to any contributions.<br>
        If you're interested, please contribute on <a href="https://github.com/BlocklandGlass">GitHub</a> and check out the <a href="https://forum.blockland.us/index.php?topic=284376.0">Blockland Glass Topic</a> over on the Blockland Forums.
	    </p>
		</div>
  </div>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
