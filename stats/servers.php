<?php
	require_once dirname(__DIR__) . "/private/class/GroupManager.php";
	require_once dirname(__DIR__) . "/private/class/UserManager.php";
	require_once dirname(__DIR__) . "/private/class/ServerTracker.php";

	$_PAGETITLE = "Glass | Current Servers";

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

	$servers = ServerTracker::getActiveServers();
?>
<div class="maincontainer">
	<table>
		<tbody>
			<?php foreach($servers as $s) {
		    echo "<tr><td><b>" . $s->host . "</b> " . $s->ip . ":" . $s->port . "</td>";
				$clients = json_decode($s->clients);
				$str = "";
				foreach($clients as $cl) {
					$str = $str . $cl->name . " <i>(" . $cl->blid . ")</i><br/>";
				}
				echo "<td>$str</td></tr>";
		  } ?>
		</tbody>
	</table>
</div>
