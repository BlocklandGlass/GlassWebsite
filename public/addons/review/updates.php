<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
	$_PAGETITLE = "Blockland Glass | Update List";
	include(realpath(dirname(__DIR__) . "/../../private/header.php"));
	use Glass\AddonManager;
	use Glass\UserManager;

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
  <?php
    include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
  ?>
  <div class="tile">
    <h2><image style="height: 1.5em" src="/img/icons32/document_info.png" /> Mod Reviewer Information <span style="font-size: 0.5em; color: gray">(As of 11/4/2016)</span></h2>
    <p><i>If you would like to suggest amendments to the following information, contact an administrator.</i></p>
    <h3><image style="height: 1.4em" src="/img/icons32/brick_error.png" /> Brick Packs</h3>
    <p><b>If the add-on being updated was previously imported from RTB</b>, ensure the update does not change UI names as this will break old save files.</p>
    <h3><image style="height: 1.4em" src="/img/icons32/caution_biohazard.png" /> Malicious Updates</h3>
    <p><b>Above all else, ensure the update is not malicious, that it can not be easily exploited by a regular user and has no backdoors - <span style="color: red">this is your top priority</span>.</b></p>
	</div>
  <div class="tile" style="margin-top 15px">
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
