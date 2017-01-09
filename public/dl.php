<?php
	require_once dirname(__DIR__) . '/private/autoload.php';
	require_once(realpath(dirname(__DIR__) . "/private/header.php"));
	require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
	use Glass\AddonManager;
?>
<div class="maincontainer">
	<p>
		<h2>Download</h2>
		In addition to offering add-on services online, Glass offers a variety of in-game features. You'll be able to automatically install your downloaded add-ons (as well as keep them up to date), be able to talk to other people playing Blockland and manage your servers' preferences.
	</p>
  <?php
  $glassAddonId = 11; //this needs to be changed before going live, or we need a "find addon by name"
  $id = "stable";
  $class = "green";
  $ao = AddonManager::getFromId($glassAddonId);
  $version = $ao->getVersion();
  ?>
  <div style="text-align: center">
    <?php
    echo '<a href="/addons/download.php?id=' . $glassAddonId . '&beta=0" class="btn dlbtn ' . $class . '"><b>' . ucfirst($id) . '</b><span style="font-size:9pt"><br />v' . $version . '</span></a>';
    ?>
  </div>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
