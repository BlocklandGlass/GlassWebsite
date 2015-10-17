<?php
$_PAGETITLE = "Glass | Search Results";

require_once(realpath(dirname(__DIR__) . "/private/header.php"));
require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<h2>Search Results for <u><?php echo $_POST['query']; ?></u></h2>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
