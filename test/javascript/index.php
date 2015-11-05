<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>QUnit Tests</title>
	<link rel="stylesheet" href="//code.jquery.com/qunit/qunit-1.20.0.css">
	<script>
		var targetUrl = "res/House.bls";
	</script>
</head>
<body>
	<div id="qunit"></div>
	<div id="qunit-fixture"></div>

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

	<script src="//code.jquery.com/qunit/qunit-1.20.0.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="res/babylon.js"></script>
	<script src="res/NBL.js"></script>
	<script src="tests.js"></script>
</body>
</html>
