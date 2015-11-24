<?php
	$_PAGETITLE = "Glass | Build Viewer";
	include(realpath(dirname(__DIR__) . "/private/header.php"));
?>

<canvas id="canvas">
	If you can see this, your browser may not support HTML 5
</canvas>

<div id="overlay" onclick = "NBL.pop_menu();">
</div>

<div id="overlay_info">
	<a id="overlay_close" href="javascript:NBL.pop_menu();">Close</a>
	<h2>Escape Menu</h2>
	<input type="file" id="files" name="files[]"/><br>
	<output id="list"></output><br>
	<button type="button" onclick="NBL.pop_menu();">Close</button>
</div>

<div id="viewer_nav_container">
	<?php include(realpath(dirname(__DIR__) . "/private/navigationbar.php")); ?>
</div>

<script src="/js/vendor/babylon.js"></script>
<link rel="stylesheet" href="/css/NBL.css">
<script type="text/javascript">
<?php
	//TO DO: add a toolbar at the bottom with things like "upload", "save", "clear", etc

	//$testfile = "./res/Arch of Constantine.bls";
	$testfile = "./res/House.bls";
	echo("var targetUrl = \"" . $testfile . "\";");

	//even though the jquery is redundant, it seems I need to include it before NBL.js
?>
</script>
<script src="/js/NBL.js"></script>

<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
