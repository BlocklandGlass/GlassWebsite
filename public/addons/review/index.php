<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
	$_PAGETITLE = "Review List | Blockland Glass";

	include(realpath(dirname(__DIR__) . "/../../private/header.php"));
	use Glass\AddonManager;
	use Glass\UserManager;

	$user = UserManager::getCurrent();
	if(!$user || !$user->inGroup("Reviewer")) {
    header('Location: /addons');
    return;
  }
?>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
  ?>
	<div class="tile">
		<h2><image style="height: 1.5em" src="/img/icons32/document_info.png" /> Mod Reviewer Information <span style="font-size: 0.5em; color: gray">(As of 11/5/2016)</span></h2>
		<p><i>If you would like to suggest amendments to the following information, contact an administrator.</i></p>
		<h3><image style="height: 1.4em" src="/img/icons32/file_save_as.png" /> Filenames</h3>
		<p>Ensure that the add-on's filename is appropriate to the add-on's function, and follows the standard Blockland add-on filename convention: <i>AddonType_AddonName.zip</i></p>
		<h3><image style="height: 1.4em" src="/img/icons32/creative_commons.png" /> Ownership</h3>
		<p>Ensure that the user uploading the add-on is the original author of it.</p>
		<h3><image style="height: 1.4em" src="/img/icons32/gear_in.png" /> Client Add-Ons</h3>
		<p><strong>These add-ons are to be inspected very carefully.</strong> Ensure that:</p>
		<ul>
			<li>They only interact with the server(s) they're intended for.</li>
			<li>They add no additional functionality outside of their intended operation.</li>
			<li>They clean up after themselves.</li>
		</ul>
		<h3><image style="height: 1.4em" src="/img/icons32/roadworks.png" /> Quality</h3>
		<p>Glass is not as stringent as RTB used to be, but do ensure that the add-on is not complete garbage (i.e. has a practical use) and that a reasonable amount of effort has been put into it. Bad examples:</p>
		<ul>
			<li>Variants of the Shoe RP gamemode.</li>
			<li>Clearly unfinished add-ons.</li>
			<li>Duplicates of other add-ons.</li>
		</ul>
		<h3><image style="height: 1.4em" src="/img/icons32/caution_biohazard.png" /> Malicious Add-Ons</h3>
		<p><strong>Above all else, ensure the add-on is not malicious, that it can not be easily exploited by a regular user and has no backdoors - <span style="color: red">this is your top priority</span>.</strong></p>
	</div>
	<div class="tile">
		<table style="width: 100%" class="listTable">
	    <thead>
	      <tr><th>Add-On</th><th>Uploader</th><th>Uploaded</th></tr>
	    </thead>
	    <tbody>
	    <?php
	      $list = AddonManager::getUnapproved();
	      foreach($list as $addon) {
					$manager = UserManager::getFromBLID($addon->getManagerBLID());
					if(is_object($manager)) {
						$name = $manager->getName();
					}	else {
						$name = $addon->getManagerBLID();
					}
	        echo "<tr>";
	        echo "<td><a href=\"/addons/addon.php?id=" . $addon->getId() . "\">" . $addon->getName() . "</a></td>";
	        echo "<td>" . htmlspecialchars(utf8_encode($name)) . "</td>";
	        echo "<td>" . date("M jS Y, g:i A", strtotime($addon->getUploadDate())) . "</td>";
	        echo "</tr>";
	      }

				if(sizeof($list) == 0) {
					echo "<tr><td colspan=\"3\" style=\"text-align:center\">Nothing to review.</td></tr>";
				}
	    ?>
	    </tbody>
	  </table>
	</div>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../../private/footer.php")); ?>
