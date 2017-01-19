<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	use Glass\UserManager;
	use Glass\UserLog;
	$failed = false;

	if(isset($_GET['blid'])) {
		try {
			$userObject = UserManager::getFromBLID($_GET['blid']);
		} catch (Exception $e) {
			$failed = true;
		}
	} else {
		$failed = true;
	}
?>
<div class="maincontainer">
	<div class="tile">
		<?php
			if($failed) {
				$msg  = "<h3>Uh-Oh</h3>";
				$msg .= "<p>Whoever you're looking for either never existed or deleted their account.</p>";
				die($msg);
				return;
			}

			$history = UserLog::getHistory($userObject->getBLID());

			echo "<h3>" . htmlspecialchars(utf8_encode($userObject->getName())) . "</h3>";
			echo "<p>";
			if($userObject->inGroup("Administrator")) {
				echo("This user is a <span style=\"color: red; font-weight: bold;\">Administrator</span>.");
			} else if($userObject->inGroup("Moderator")) {
				echo("This user is a <span style=\"color: orange; font-weight: bold;\">Moderator</span>.");
			} else if($userObject->inGroup("Reviewer")) {
				echo("This user is a <span style=\"color: green; font-weight: bold;\">Mod Reviewer</span>.");
			}
			if(sizeof($history) > 0) echo("<p><b>Last Seen:</b> " . $history[0]->lastseen);
			echo("<br /><b>BL_ID:</b> " . $userObject->getBLID());
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
