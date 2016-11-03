<?php
	$_PAGETITLE = "Blockland Glass | Reclaim List";
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/RTBAddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserManager.php"));

	$user = UserManager::getCurrent();
	if(!$user || !$user->inGroup("Reviewer")) {
    header('Location: /addons');
    return;
  }

  if(isset($_REQUEST['action'])) {
    if($_REQUEST['action'] == "accept") {
      RTBAddonManager::acceptReclaim($_REQUEST['id'], true);
    } else {
      RTBAddonManager::acceptReclaim($_REQUEST['id'], false);
    }
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
  <p>This part of reviewing probably requires some research. Make sure that the user trying to reclaim the add-on is the original author from RTB. You can download the RTB file and look at the description.txt</p>
  <hr />
  <table style="width: 100%">
    <thead>
      <tr><th>RTB Add-On</th><th>Glass Add-On</th><th>User</th><th> </th></tr>
    </thead>
    <tbody>
    <?php
			$reclaims = RTBAddonManager::getPendingReclaims();
      foreach($reclaims as $rec) {
				$addon = AddonManager::getFromId($rec->glass_id);
        echo "<tr>";
        echo "<td>";
				echo '<a href="/addons/rtb/view.php?id=' . $rec->id . '">';
        echo $rec->title;
        echo "</a></td>";


        echo "<td>";
				echo '<a href="/addons/addon.php?id=' . $addon->getId() . '">';
        echo $addon->getName();
        echo "</a></td>";

        echo "<td>";
        echo UserManager::getFromBlid($addon->getManagerBLID())->getUsername();
        echo "</td>";

        echo "<td>";
        echo "<form target=\"\" method=\"post\">";
        echo "<input type=\"hidden\" name=\"id\" value=\"" . $rec->id . "\" />";
        echo "<input name=\"action\" value=\"accept\" type=\"image\" src=\"/img/icons16/accept_button.png\" /> ";
        echo "<input name=\"action\" value=\"reject\" type=\"image\" src=\"/img/icons16/delete.png\" />";
        echo "</form>";
        echo "</td>";

        echo "</tr>";
      }

			if(sizeof($reclaims) == 0) {
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
