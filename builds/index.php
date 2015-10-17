<?php
require_once(realpath(dirname(__DIR__) . "/private/class/BoardManager.php"));

$_PAGETITLE = "Glass | Builds";
$_OPENHEAD = true;
require_once(realpath(dirname(__DIR__) . "/private/header.php"));
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
<?php
require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
  <div style="text-align: center;"><h1>Build Gallery</h1>
  <i>Coming soon!</i></div>
  <p>Clear from the community's multiple requests and attempts for a save gallery, it's quite a popular idea. We plan on finally tackling the social sharing of your hours of work on your builds.</p>
  <p>Want a little taste of what's to come? Check out this!</p>
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
  <span style="font-size: 0.8em">Use WASD and your mouse to navigate!</span>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
