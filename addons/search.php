<?php
	require_once(realpath(dirname(__DIR__) . "/private/class/DatabaseManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/lib/Parsedown.php"));

	$_PAGETITLE = "Glass | Search Results";

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<form action="search.php" method="post">
		<input class="searchbar" type="text" name="query" placeholder="Search..."/>
	</form>
	<div id="searchResults">
		<?php include(realpath(dirname(__DIR__) . "/ajax/search.php")); ?>
	</div>
</div>

<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
