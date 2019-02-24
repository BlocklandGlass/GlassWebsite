<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';

  use Glass\GroupManager;
	use Glass\UserManager;
	use Glass\UserLog;

	$blid = htmlspecialchars($_GET['blid']) ?? false;

  if(!is_numeric($blid) || $blid < 1) {
    die('Invalid BLID.');
  }

	$hasAccount = true;

	if($blid > 0) {
		try {
			$userObject = UserManager::getFromBLID($blid);
			if($userObject) {
				$hasAccount = true;
			} else {
				$hasAccount = false;
			}
		} catch (Exception $e) {
			$hasAccount = false;
		}

		$userLog = UserLog::getCurrentUsername($blid);
		if(!$userLog && !$hasAccount) {
			$failed = true;
		} else {
			$failed = false;
		}

    $history = UserLog::getHistory($blid);
    if($hasAccount) {
      $name = htmlspecialchars(utf8_encode($userObject->getName()));
    } else {
      $name = htmlspecialchars(utf8_encode($userLog));
    }

    $lastseen = UserLog::getLastSeen($blid);
    if($lastseen) {
      $time = strtotime($lastseen);
      $lastseen = date("F j, Y, g:i a", $time);
    } else {
      $lastseen = "Never";
    }
	} else {
		$failed = true;
    $lastseen = "Never";
	}

  $_PAGETITLE = ($failed ? "Unknown Profile | Blockland Glass" : $name . "'s Profile | Blockland Glass");
  $_PAGEDESCRIPTION = "BL_ID: $blid\r\nLast Seen: $lastseen";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	use Glass\AddonManager;
	use Glass\StatUsageManager;
?>
<style>
  .user-group {
    padding: 1rem;
    width: 220px;
    background-color: #ddd;
    display: inline-block;
    margin: 5px;
  }

  .user-group div:nth-of-type(1) {
    display: inline-block;
  }

  .user-group div:nth-of-type(2) {
    margin-left: 10px;
    display: inline-block;
    font-weight: bold;
  }

  .user-group:hover {
    background-color: #ccc;
  }
</style>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
  <?php
    if($failed) {
      $msg = "<div class=\"tile\">";
      $msg .= "<h2>Uh Oh</h2>";
      $msg .= "<p>We've never seen that user before. Sorry!</p>";
      $msg .= "</div>";
      echo $msg;
      return;
    }

    echo "<div class=\"tile\" style=\"font-size: 3rem;\">";
    echo "$name";
    echo "</div>";
    echo "<div class=\"tile\">";
    echo "<h2>Info</h2>";

    echo "<p><strong>BL_ID:</strong> $blid";
    echo "<br /><strong>Last Seen:</strong> $lastseen</p>";

    if($hasAccount) {
      $groups = GroupManager::getGroupsFromBLID($blid);
      if(sizeof($groups) > 0) {
        echo "<p>Additionally, this user is part of the following group(s):</p>";
        foreach($groups as $gid) {
          $group = GroupManager::getFromId($gid);
          echo "<a href=\"/user/group.php?name=" . $group->getName() . "\" class=\"user-group\" title=\"" . $group->getDescription() . "\"><div><img src=\"/img/icons32/" . $group->getIcon() . ".png\"></div><div style=\"color: #" . $group->getColor() . ";\">" . $group->getName() . ($group->getLeader() == $blid ? " (Leader)" : "") . "</div></a>";
        }
      }
    }
    echo "</p>";
    echo "</div>";

    // echo "<div class=\"tile\">";
    // echo "<h2>Recent Activity</h2>";
    // echo "<p>..?</p>";
    // echo "</div>";
  ?>
  <div class="tile">
    <h2>Previous Names</h2>
    <table class="listTable" style="width: 100%">
      <thead>
        <tr>
          <th style="width: 50%">Username</th>
          <th>Date Changed</th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach($history as $namedata) {
          echo "<tr>";
          echo "<td>" . htmlspecialchars(utf8_encode($namedata->username)) . "</td>";
          echo "<td>" . $namedata->date . "</td>";
          echo "</tr>";
        }

        if(sizeof($history) == 0) {
          echo "<tr><td colspan=\"2\" style=\"text-align: center\">";
          echo "No name changes on record.";
          echo "</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
	<?php
	if($hasAccount) {
	?>
	<div class="tile">
    <h2>Uploaded Content</h2>
		<table class="listTable" style="width: 100%">
			<thead>
				<tr>
					<th>
						Add-On
					</th>
					<th>
						Downloads
					</th>
					<th>
						Active Users (Week)
					</th>
				</tr>
			</thead>
			<tbody>
				<?php

				$addons = AddonManager::getFromBLID($userObject->getBLID(), ["deleted"=>0, "approved"=>1]);
				foreach($addons as $aid) {
					$addon = AddonManager::getFromId($aid);
					$name = $addon->getName();
					$downloads = $addon->getTotalDownloads();
					$users = StatUsageManager::getActiveUsers($aid, 7);

					echo "<tr><td><a href=\"/addons/addon.php?id=$aid\">$name</a></td><td>$downloads</td><td>$users</td>";
				}

				if(sizeof($addons) == 0) {
					echo '<tr><td colspan="3" style="text-align: center">No uploaded content.</td></tr>';
				}

				?>
			</tbody>
		</table>
	</div>

	<?php
	}
	?>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
