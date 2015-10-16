<?php
$_PAGETITLE = "Glass | Add-On Name";
require_once(realpath(dirname(__DIR__) . "/private/header.php"));

require_once(realpath(dirname(__DIR__) . "/private/class/BoardManager.php"));

//LEGACY CODE, EXAMPLE ONLY
//require_once('../../html/class/BoardManager.php');
?>

<div class="navcontainer">
  <div class="navcontent">
    <!-- temporary nav -->
	  <a class="homebtn" href="/">Blockland Glass</a>
  </div>
</div>
<div class="maincontainer">
  <span style="font-size: 9pt;"><a href="/addons/">Add-Ons</a> >> <a href="#">Some Board</a> >> <a href="#">My Add-On</a></span>
	<h2>My Add-On</h2>
  <p>
    <image src="http://blocklandglass.com/icon/icons32/user.png" /> By <a href="#">Jincux</a><br />
    <image src="http://blocklandglass.com/icon/icons32/cog.png" /> Some Board
  </p>
  <p>
    My add-on does a lot of cool stuff. There's so many reasons why you should get it. First off, it has an incredible small footprint because it <b>literally does nothing</b>! Thats right: the mod is so efficient that it has no impact on your game whatsoever.<br /><br />
    Lets get to 1337 downloads guys!
  </p>
  <a href="dl.php" class="btn dlbtn green"><b>Stable</b><span style="font-size:9pt"><br />v1.1.0</span></a>
  <a href="dl.php" class="btn dlbtn yellow"><b>Unstable</b><span style="font-size:9pt"><br />v1.1.0-alpha.1</span></a>
  <a href="dl.php" class="btn dlbtn red"><b>Development</b><span style="font-size:9pt"><br />v1.1.0-alpha.6</span></a><br />
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
