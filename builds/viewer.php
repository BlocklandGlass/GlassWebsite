<!DOCTYPE html>
<html>
<head>
<script src="./hand.js"></script>
<script src="./babylon.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="./NBL.js"></script>
<link rel="stylesheet" href="./NBL.css">

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
</body>
</html>