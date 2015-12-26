<?php
	$_PAGETITLE = "Glass | Update List";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserManager.php"));
?>
<div class="maincontainer">
  <table style="width: 100%">
    <thead>
      <tr><th>Add-On</th><th>Branch</th><th>Version</th></tr>
    </thead>
    <tbody>
    <?php
      // this is going to be INCREDIBLY inefficent, i'll take another pass later
      $list = AddonManager::getAll();
      foreach($list as $addon) {
        $manager = UserManager::getFromBLID($addon->getManagerBLID());
				if(is_object($manager)) {
					$name = $manager->getName();
				}	else {
					$name = $addon->getManagerBLID();
				}


        $versionInfo = $addon->getVersionInfo();
        foreach($versionInfo as $branch=>$dat) {
          if(isset($dat->pending)) {
            echo "<tr>";
            echo "<td>";
            echo $name;
            echo "</td>";

            echo "<td>";
            echo $branch;
            echo "</td>";

            echo "<td>";
            echo $dat->pending->version;
            echo "</td>";
            echo "</tr>";
            //echo json_encode($dat->pending);
          }
        }
      }
    ?>
    </tbody>
  </table>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
