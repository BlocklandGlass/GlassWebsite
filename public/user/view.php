<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	use Glass\UserManager;
	use Glass\UserLog;

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
	}

	$userLog = UserLog::getCurrentUsername($blid);
	if(!$userLog && !$hasAccount) {
		$failed = true;
	} else {
		$failed = false;
	}
?>
<div class="maincontainer">
	<div class="tile">
		<?php
			if($failed) {
				$msg  = "<h2>Uh-Oh</h2>";
				$msg .= "<p>We've never seen that user before. Sorry!</p>";
				die($msg);
				return;
			}

			$history = UserLog::getHistory($blid);
			if($hassAccount) {
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
					$title = "Moderator";
					$color = "orange";
				} else if($userObject->inGroup("Reviewer")) {
					$title = "Reviewer";
					$color = "green";
				}

				if($title) {
					echo "This user is a <span style=\"color: $color; font-weight: bold;\">$title</span>.";
				}
			}

			$lastseen = UserLog::getLastSeen($blid);
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
				?>
			</tbody>
		</table>
	</div>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
