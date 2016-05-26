<?php
	$_PAGETITLE = "Glass | Update List";
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
<div class="maincontainer">
  <table style="width: 100%">
    <thead>
      <tr><th>Add-On</th><th>Submitted</th><th>Version</th></tr>
    </thead>
    <tbody>
    <?php
			$updates = AddonManager::getPendingUpdates();
      foreach($updates as $update) {
				$addon = $update->getAddon();
        echo "<tr>";
        echo "<td>";
				echo '<a href="update.php?id=' . $addon->getId() . '">';
        echo $addon->getName();
        echo "</a></td>";

        echo "<td>";
        echo date("M d, H:i", strtotime($update->submitted));
        echo "</td>";

        echo "<td>";
        echo $update->version;
        echo "</td>";
        echo "</tr>";
      }

			if(sizeof($updates) == 0) {
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
