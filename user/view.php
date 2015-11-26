<?php
	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/UserLog.php"));
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
<?php
	/*Ideas:
		- select avatar from some predetermined list
		- custom description
		- friends list
		- linking to steam
	*/

	if($failed) {
		echo("<h3>Uh-Oh</h3>");
		echo("<p>Whoever you're looking for either never existed or deleted their account.</p>");
	} else {
		$history = UserLog::getHistory($userObject->getBLID());

		echo("<h3>" . htmlspecialchars($userObject->getName()) . "</h3>");
		echo("<p><b>Last Seen:</b> " . $history[0]->lastseen);
		echo("<br /><b>BL_ID:</b> " . $userObject->getBLID());
		echo "</p><hr />";
		echo("<a href=\"/addons/search.php?blid=" . htmlspecialchars($userObject->getBLID()) . "\"><b>Find Add-Ons by this user</b></a>");
		?>
		<hr />
		<table style="width: 100%">
			<thead>
				<tr>
					<th style="width: 33%">Username</th><th>Last Seen</th><th>First Seen</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach($history as $namedata) {
					echo "<tr>";
					echo "<td>" . $namedata->username . "</td>";
					echo "<td>" . $namedata->lastseen . "</td>";
					echo "<td>" . $namedata->firstseen . "</td>";
					echo "</tr>";
				}
				?>
			</tbody>
		</table>
		<?php

		//echo("<br /><a href=\"javascript:{}\" onclick=\"document.getElementById('addonSearch').submit();\"><b>Find Add-Ons by this user</b></a></p>");
		//echo("<form id=\"addonSearch\" action=\"/addons/search.php\" method=\"post\">");
		//echo("<input type=\"hidden\" name=\"query\" value=\"\">");
		//echo("<input type=\"hidden\" name=\"blid\" value=\"" . $userObject->getBLID() . "\">");
		//echo("</form>");
	}
?>
</div>

<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
