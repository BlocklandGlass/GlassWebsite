<?php
	//use Glass\DatabaseManager;
	//require_once(realpath(dirname(__DIR__) . "/private/lib/Parsedown.php"));

	$_PAGETITLE = "Blockland Glass | Search Results";

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<?php include(realpath(dirname(__DIR__) . "/private/searchbar.php")); ?>
	<div id="searchResults">
		<?php include(realpath(dirname(__DIR__) . "/ajax/search.php")); ?>
	</div>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
