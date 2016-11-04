<?php
	$_PAGETITLE = "Blockland Glass | Update List";
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
  <h2><image style="height: 1.5em" src="/img/icons32/document_info.png" /> Glass Reviewer Information <span style="font-size: 0.5em; color: gray">(As of 11/3/2016)</span></h2>
  <p>To be written.</p>
  <hr />
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
