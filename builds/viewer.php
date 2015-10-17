<?php
$_PAGETITLE = "Glass | Build Viewer";
$_OPENHEAD = true;
include(realpath(dirname(__DIR__) . "/private/header.php"));
?>

<script src="./res/babylon.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="./res/NBL.js"></script>
<link rel="stylesheet" href="./res/NBL.css">

<?php
$testfile = "./res/House.bls";
echo("<script type=\"text/javascript\">");
echo("var targetUrl = \"" . $testfile . "\";");
echo("</script>");

?>

</head>
<body onload="NBL.javascript_init();">

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
	<?php
		include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
	?>
	</div>
</body>
</html>