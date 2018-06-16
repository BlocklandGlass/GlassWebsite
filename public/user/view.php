<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	use Glass\UserManager;
	use Glass\UserLog;
	use Glass\AddonManager;
	use Glass\StatUsageManager;

	$blid = $_GET['blid'] ?? false;

	$hasAccount = true;

	if($blid) {
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
	} else {
		$failed = true;
	}
?>
<div class="maincontainer">
	<div class="tile">
		<?php
			if($failed) {
				$msg  = "<h2>Uh Oh</h2>";
				$msg .= "<p>We've never seen that user before. Sorry!</p>";
				echo $msg;
				return;
			}

			$history = UserLog::getHistory($blid);
			if($hasAccount) {
				$name = htmlspecialchars(utf8_encode($userObject->getName()));
			} else {
				$name = htmlspecialchars(utf8_encode($userLog));
			}

			echo "<h2>$name</h2>";
			echo "<p>";

			if($hasAccount) {
				$title = false;
				if($userObject->inGroup("Administrator")) {
					$title = "Administrator";
					$color = "red";
				} else if($userObject->inGroup("Moderator")) {
					$title = "Chat Moderator";
					$color = "orange";
				} else if($userObject->inGroup("Reviewer")) {
					$title = "Mod Reviewer";
					$color = "green";
				}

				if($title) {
					echo "This user is a verified <span style=\"color: $color; font-weight: bold;\">$title</span>.";
				}
			}

			$lastseen = UserLog::getLastSeen($blid);
			if($lastseen) {
				$time = strtotime($lastseen);
				$lastseen = date("F j, Y, g:i a", $time);
			} else {
				$lastseen = "Never";
			}
			echo "<p><b>Last Seen:</b> $lastseen";
			echo "<br /><b>BL_ID:</b> $blid";
			echo "</p>";
			//echo("<a href=\"/addons/search.php?blid=" . htmlspecialchars($userObject->getBLID()) . "\"><b>Find Add-Ons by this user</b></a>");
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
