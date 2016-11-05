<?php
	$_PAGETITLE = "Blockland Glass | Review List";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserManager.php"));

	$user = UserManager::getCurrent();
	if(!$user || !$user->inGroup("Reviewer")) {
    header('Location: /addons');
    return;
  }
?>
<style>
thead {
  background-color: #ccc;
  margin-bottom: 10px;
}

table th {
  padding: 5px;
  margin-bottom: 10px;
}

table td {
  padding: 5px;
}

table th:first-child {
  border-radius: 5px 0 0 5px;
}

table th:last-child {
  border-radius: 0 5px 5px 0;
}

tbody tr:nth-child(2n) {
  background-color: #eee;
}
</style>
<div class="maincontainer">
	<h2><image style="height: 1.5em" src="/img/icons32/document_info.png" /> Glass Reviewer Information <span style="font-size: 0.5em; color: gray">(As of 11/5/2016)</span></h2>
	<p><i>If you would like to suggest amendments to the following information, contact a Glass Administrator.</i></p>
	<h3><image style="height: 1.4em" src="/img/icons32/file_save_as.png" /> On the Subject of Filenames</h3>
	<p>Ensure that the add-on's filename is appropriate to the add-on's function, and follows the standard Blockland add-on filename convention: <i>AddonType_AddonName.zip</i></p>
	<h3><image style="height: 1.4em" src="/img/icons32/creative_commons.png" /> On the Subject of Ownership</h3>
	<p>Ensure that the user uploading the add-on is the original author of it.</p>
	<h3><image style="height: 1.4em" src="/img/icons32/gear_in.png" /> On the Subject of Client Add-Ons</h3>
	<p><b>These add-ons are to be inspected very carefully.</b> Ensure that:</p>
	<ul>
		<li>They only interact with the server(s) they're intended for.</li>
		<li>They add no additional functionality outside of their intended operation.</li>
		<li>They clean up after themselves.</li>
	</ul>
	<h3><image style="height: 1.4em" src="/img/icons32/roadworks.png" /> On the Subject of Quality</h3>
	<p>Glass is not as stringent as RTB used to be, but do ensure that the add-on is not complete garbage (i.e. has a practical use) and that a reasonable amount of effort has been put into it. Bad examples:</p>
	<ul>
		<li>Variants of the Shoe RP gamemode.</li>
		<li>Clearly unfinished add-ons.</li>
		<li>Duplicates of other add-ons.</li>
	</ul>
	<h3><image style="height: 1.4em" src="/img/icons32/caution_biohazard.png" /> On the Subject of Malicious Add-Ons</h3>
	<p><b>Above all else, ensure the add-on is not malicious, that it can not be easily exploited by a regular user and has no backdoors - <span style="color: red">this is your top priority</span>.</b></p>
	<hr />
	<table style="width: 100%">
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
        echo "<td><a href=\"inspect.php?id=" . $addon->getId() . "\">" . $addon->getName() . "</a></td>";
        echo "<td>" . $name . "</td>";
        echo "<td>" . date("M jS Y, g:i A", strtotime($addon->getUploadDate())) . "</td>";
        echo "</tr>";
      }

			if(sizeof($list) == 0) {
				echo "<tr><td colspan=\"3\" style=\"text-align:center\">Nothing to review!</td></tr>";
			}
    ?>
    </tbody>
  </table>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
