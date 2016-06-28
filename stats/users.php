<?php
	require_once dirname(__DIR__) . "/private/class/GroupManager.php";
	require_once dirname(__DIR__) . "/private/class/UserManager.php";
	require_once dirname(__DIR__) . "/private/class/UserLog.php";
	require_once dirname(__DIR__) . "/private/class/StatUsageManager.php";

	$_PAGETITLE = "Glass | Current Users";

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

	$users = UserLog::getRecentlyActive();
?>
<div class="maincontainer">
	<table>
		<tbody>
			<?php foreach($users as $u) {
		    echo "<tr><td><b>" . UserLog::getCurrentUsername($u->blid) . "</b></td><td>" . $u->blid . "</td><td>" . StatUsageManager::getVersionUsed($u->blid, 11) . "</td></tr>";
		  } ?>
		</tbody>
	</table>
</div>
