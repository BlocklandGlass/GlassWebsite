<?php
require_once(realpath(dirname(__FILE__) . "/private/header.php"));
require_once(realpath(dirname(__FILE__) . "/private/navigationbar.php"));
require_once(realpath(dirname(__FILE__) . "/private/class/AddonManager.php"));
?>
<div class="maincontainer">
	<p>
		<h2>Download</h2>
		In addition to offering add-on and build hosting services online, Glass offers a variety of in-game features. You'll be able to automatically install your downloaded add-ons and builds, as well as keep them up to date.
	</p>
  <?php
  $glassAddonId = 11; //this needs to be changed before going live, or we need a "find addon by name"
  $ao = AddonManager::getFromId($glassAddonId);
  $version = $ao->getVersion();
  ?>
  <div style="text-align: center">
	   <a href="/addons/download.php?id=11&branch=stable" class="btn blue" style="font-weight: normal; color: #fff;"><b>Download</b> v<?php echo $version ?></a><br />
  </div>
</div>

<?php require_once(realpath(dirname(__FILE__) . "/private/footer.php")); ?>
