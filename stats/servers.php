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
	<?php foreach($servers as $s) {
    echo "<b>" . $s->host . "</b> " . $s->ip . ":" . $s->port . "<br />";
  } ?>
</div>
