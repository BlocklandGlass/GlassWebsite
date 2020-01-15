<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
	$_PAGETITLE = "Reclaim List | Blockland Glass";
	include(realpath(dirname(__DIR__) . "/../../private/header.php"));
	use Glass\AddonManager;
	use Glass\RTBAddonManager;
	use Glass\UserManager;

	$user = UserManager::getCurrent();
	if(!$user || !$user->inGroup("Administrator")) {
    header('Location: /addons');
    return;
  }

  if(isset($_POST['action']) && isset($_POST['id'])) {
    $action = $_POST['action'];
    $id = $_POST['id'];

    if($action == "Accept") {
      RTBAddonManager::acceptReclaim($id);
    } elseif($action == "Reject") {
      RTBAddonManager::rejectReclaim($id);
    }
  }
?>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
    include(realpath(dirname(__DIR__) . "/../../private/subnavigationbar.php"));
  ?>
  <div class="tile">
    <table style="width: 100%" class="listTable">
      <thead>
        <tr>
          <th>RTB Add-On</th>
          <th>Glass Add-On</th>
          <th>User</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php
          $reclaims = RTBAddonManager::getPendingReclaims();

          if(sizeof($reclaims) == 0) {
            echo "<tr><td colspan=\"4\" style=\"text-align:center\">Nothing to review.</td></tr>";
          } else {
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
              echo "<form method=\"post\">";
              echo "<input class=\"btn small green\"name=\"action\" value=\"Accept\" type=\"submit\"> ";
              echo "<input class=\"btn small red\" name=\"action\" value=\"Reject\" type=\"submit\">";
              echo "<input type=\"hidden\" name=\"id\" value=\"" . $rec->id . "\">";
              echo "</form>";
              echo "</td>";

              echo "</tr>";
            }
          }
        ?>
      </tbody>
    </table>
  </div>
</div>

<?php

	include(realpath(dirname(__DIR__) . "/../../private/footer.php")); ?>
