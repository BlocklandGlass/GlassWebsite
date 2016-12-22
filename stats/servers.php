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
		$addr = $s->ip . ":" . $s->port;

		echo '<a href="blockland://' . $addr . '">Join (' . $addr . ')</a><hr />';

		$clients = json_decode($s->clients);
		$str = "";

		echo '<table class="listTable" style="width: 100%">'
				. '<thead>'
				. '<tr>'
				. '<th style="width: 30px;"> </th>'
				. '<th>Username</th>'
				. '<th>BL_ID</th>'
				. '<th>Glass</th>'
				. '</tr></thead><tbody>';

		if(sizeof($clients) > 0 && $clients[0]->name != "") {
			foreach($clients as $cl) {
				$name = utf8_encode($cl->name);

				if($cl->status == "")
					$cl->status = "-";

				echo '<tr>';
				echo '<td style="width: 30px; text-align: center">' . $cl->status . '</td>';
				echo '<td style="text-align: left">' . $name . '</td>';
				echo '<td>' . $cl->blid . '</td>';
				echo '<td>' . ($cl->version == "" ? "No" : "Yes") . '</td>';
				echo '</tr>';
			}
			echo "</tr>";
		} else {
			echo '<tr><td colspan="4" style="text-align:center">No users!</td></tr>';
		}
		echo '</tbody></table>';
		echo '</div>';
  } ?>
</div>
