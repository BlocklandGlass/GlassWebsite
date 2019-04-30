<?php
	require_once dirname(__DIR__) . '/private/autoload.php';
  $_PAGETITLE = "Download | Blockland Glass";
	require_once(realpath(dirname(__DIR__) . "/private/header.php"));
	use Glass\AddonManager;
?>
<div class="maincontainer">
  <?php
    require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
  ?>
	<div class="tile">
		<h2>Download</h2>
		<p>
			In addition to offering add-on services online, Glass offers a variety of in-game features. You'll be able to automatically install your downloaded add-ons (as well as keep them up to date), be able to talk to other people playing Blockland and manage your servers' preferences.
		</p>
		<h3>Steam</h3>
		<p>
			Save <i>System_BlocklandGlass.zip</i> in "C:/Program Files (x86)/Steam/steamapps/common/Blockland/Add-Ons"
		</p>
		<h3>Non-Steam</h3>
		<p>
			Save <i>System_BlocklandGlass.zip</i> in "My Documents/Blockland/Add-Ons"
		</p>
	  <?php
	  $glassAddonId = 11; //this needs to be changed before going live, or we need a "find addon by name"
	  $class = "green";
	  $ao = AddonManager::getFromId($glassAddonId);
    if($ao)
      $version = $ao->getVersion();
    else
      $version = "[missing]";
	  ?>
	</div>
  <div style="text-align: center">
    <?php
    echo '<a href="/addons/download.php?id=' . $glassAddonId . '" class="btn dlbtn ' . $class . '"><strong>Download</strong><span style="font-size:9pt"><br />v' . $version . '</span></a>';
    ?>
  </div>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
