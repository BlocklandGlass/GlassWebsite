<?php
	require_once dirname(__DIR__) . "/private/class/GroupManager.php";
	require_once dirname(__DIR__) . "/private/class/UserManager.php";
	require_once dirname(__DIR__) . "/private/class/ServerTracker.php";

	$_PAGETITLE = "Blockland Glass | Current Servers";

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

	$servers = ServerTracker::getActiveServers();
?>
<div class="maincontainer">
	<?php
	 foreach($servers as $s) {
    echo "<div class=\"tile\" style=\"width: 50%; margin: 0 auto; margin-bottom: 10px\"><h3 style=\"padding-bottom: 0; margin-bottom: 0\">" . utf8_encode($s->host) . "'s Server</h3>";
		echo "" . $s->ip . ":" . $s->port . "<hr />";

		$clients = json_decode($s->clients);
		$str = "";

		echo '<table class="listTable" style="width: 100%">'
				. '<thead>'
				. '<tr>'
				. '<th>Username</th>'
				. '<th>BLID</th>'
				. '<th>Glass</th>'
				. '</tr></thead><tbody>';

		if(sizeof($clients) > 0 && $clients[0]->name != "") {
			foreach($clients as $cl) {
				$name = utf8_encode($cl->name);

				echo '<tr>';
				echo '<td>' . $name . '</td>';
				echo '<td>' . $cl->blid . '</td>';
				echo '<td>' . ($cl->version == "" ? "No" : "Yes") . '</td>';
				echo '</tr>';
			}
			echo "</tr>";
		} else {
			echo '<tr><td colspan="3" style="text-align:center">No users!</td></tr>';
		}
		echo '</tbody></table>';
		echo '</div>';
  } ?>
</div>
