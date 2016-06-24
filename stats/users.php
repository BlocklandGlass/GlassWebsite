<?php
	require_once dirname(__DIR__) . "/private/class/GroupManager.php";
	require_once dirname(__DIR__) . "/private/class/UserManager.php";
	require_once dirname(__DIR__) . "/private/class/UserLog.php";

	$_PAGETITLE = "Glass | Current Users";

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

	$users = UserLog::getRecentlyActive();
?>
<div class="maincontainer">
	<?php foreach($users as $u) {
    echo "<b>" . UserLog::getCurrentUsername($u->blid) . "</b> " . $u->blid . "<br />";
  } ?>
</div>
