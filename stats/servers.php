<?php
	require_once dirname(__DIR__) . "/private/class/GroupManager.php";
	require_once dirname(__DIR__) . "/private/class/UserManager.php";
	require_once dirname(__DIR__) . "/private/class/ServerTracker.php";

	$_PAGETITLE = "Blockland Glass | Current Servers";

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

	$servers = ServerTracker::getActiveServers();
?>
<style>
.list td {
  padding: 10px;
}

.list tr:nth-child(2n+1) td {
  background-color: #ddd;
}

.list tr:first-child td {
  background-color: #777;
  color: #fff;
  font-weight: bold;
}

.list tr td:first-child {
  border-radius: 10px 0 0 10px;
}

.list tr td:last-child {
  border-radius: 0 10px 10px 0;
}

.list {
  margin: 0 auto;
}

.maincontainer p {
  text-align: center;
}

form {
  text-align: center;
}

</style>
<div class="maincontainer">
	<?php
	 foreach($servers as $s) {
    echo "<div class=\"tile\" style=\"width: 50%; margin: 0 auto\"><h3>" . utf8_encode($s->host) . "'s Server</h3><br />";
		echo "" . $s->ip . ":" . $s->port . "<hr />";

		$clients = json_decode($s->clients);
		$str = "";

		echo '<table class="list" style="width: 100%">'
				. '<tbody>'
				. '<tr>'
				. '<td>Username</td>'
				. '<td>BLID</td>'
				. '<td>Glass</td>'
				. '</tr>';

		if(sizeof($clients) > 0) {
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
  } ?>
</div>
