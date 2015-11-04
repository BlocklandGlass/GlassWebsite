<?php
	require_once(realpath(dirname(__DIR__) . "/private/class/DatabaseManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/lib/Parsedown.php"));

	$_PAGETITLE = "Glass | Search Results";

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<?php include(realpath(dirname(__DIR__) . "/api/search.php")); ?>
</div>

<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
