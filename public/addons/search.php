<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	//use Glass\DatabaseManager;
	//require_once(realpath(dirname(__DIR__) . "/../private/lib/Parsedown.php"));

	$_PAGETITLE = "Search Results | Blockland Glass";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
?>
<style>
  .searchresult {
    border: 1px solid #ccc;
    margin-bottom: 1rem;
  }
</style>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
    include(realpath(dirname(__DIR__) . "/../private/subnavigationbar.php"));
  ?>
  <div class="tile" id="searchResults">
		<?php include(realpath(dirname(__DIR__) . "/ajax/search.php")); ?>
	</div>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../private/footer.php"));
?>