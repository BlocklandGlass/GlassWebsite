<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';

  use Glass\GroupManager;
	use Glass\UserManager;
	use Glass\UserLog;

	$blid = htmlspecialchars($_GET['blid']) ?? false;

  if(!is_numeric($blid) || $blid < 0) {
    die('Invalid BLID.');
  }

	$hasAccount = true;

	if($blid > -1) {
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
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
	<div class="tile">
		<?php
			if($failed) {
				$msg  = "<h2>Uh Oh</h2>";
				$msg .= "<p>We've never seen that user before. Sorry!</p>";
				echo $msg;
				return;
			}

			echo "<h2>$name</h2>";
			echo "<p>";

			if($hasAccount) {
        $groups = GroupManager::getGroupsFromBLID($blid);
        if(sizeof($groups) > 0) {
          echo "This user is part of the following groups:<br>";
          echo "<div style=\"margin-left: 15px;\">";
          foreach($groups as $gid) {
            $group = GroupManager::getFromId($gid);
            echo "<img src=\"/img/icons16/" . $group->getIcon() . ".png\"> <span style=\"font-weight: bold; color: #" . $group->getColor() . ";\" title=\"" . $group->getDescription() . "\">" . $group->getName() . ($group->getLeader() == $blid ? " (Leader)" : "") . "</span><br>";
          }
          echo "</div>";
        }
			}

			echo "<p><strong>BL_ID:</strong> $blid";
			echo "<br /><strong>Last Seen:</strong> $lastseen";
			echo "</p>";
			//echo("<a href=\"/addons/search.php?blid=" . htmlspecialchars($userObject->getBLID()) . "\"><strong>Find Add-Ons by this user</strong></a>");
			?>
		<hr />
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
					echo "No recorded name changes.";
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
