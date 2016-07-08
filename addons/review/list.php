<?php
	$_PAGETITLE = "Glass | Review List";

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
	<p>These add-ons have been submitted but need to be looked over. Ensure that they are not malicious and do not contain backdoors, then categorize them appropriately</p>
	<b>Client Add-Ons:</b>
	<br />
	<p>I want these to be checked very carefully. These will be downloaded on the spot by users who have no idea what they're downloading. They'll likely be executed right before the server is joined. Make sure they only interact with the server they're intended for and that they clean up after themselves. Make sure that they add no additional client functionality outside of in-game changes.</p>
  <table style="width: 100%">
    <thead>
      <tr><th>Add-On</th><th>Author</th><th>Uploaded</th></tr>
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
        echo "<td>" . date("D, g:i a", strtotime($addon->getUploadDate())) . "</td>";
        echo "</tr>";
      }

			if(sizeof($list) == 0) {
				echo "<tr><td colspan=\"3\" style=\"text-align:center\">Nothing to approve!</td></tr>";
			}
    ?>
    </tbody>
  </table>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
