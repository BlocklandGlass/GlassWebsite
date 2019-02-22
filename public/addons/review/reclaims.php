<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
	$_PAGETITLE = "Reclaim List | Blockland Glass";
	include(realpath(dirname(__DIR__) . "/../../private/header.php"));
	use Glass\AddonManager;
	use Glass\RTBAddonManager;
	use Glass\UserManager;

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
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
  ?>
  <div class="tile">
    <h2><image style="height: 1.5em" src="/img/icons32/document_info.png" /> Mod Reviewer Information <span style="font-size: 0.5em; color: gray">(As of 11/3/2016)</span></h2>
    <p><i>If you would like to suggest amendments to the following information, contact an administrator.</i></p>
    <h3><image style="height: 1.4em" src="/img/icons32/creative_commons.png" /> Ownership</h3>
    <p>Ensure that the user trying to reclaim the add-on is the original author and not a third party or impersonator.</p>
    <h3><image style="height: 1.4em" src="/img/icons32/roadworks.png" /> Quality</h3>
    <p>Ensure the add-on being imported is not an add-on of which came from RTB's Bargain Bin.</p>
	</div>
  <div class="tile">
    <table style="width: 100%" class="listTable">
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
          echo "<tr><td colspan=\"4\" style=\"text-align:center\">Nothing to review.</td></tr>";
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
