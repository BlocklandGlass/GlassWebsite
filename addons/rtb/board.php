<?php
	require_once(realpath(dirname(__DIR__) . "/../private/class/BoardManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
	//require_once(realpath(dirname(__DIR__) . "/private/class/AddonObject.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserManager.php"));
	//require_once(realpath(dirname(__DIR__) . "/private/class/UserHandler.php"));

	//TO DO: rewrite this page to use /private/json/getBoardAddonsWithUsers.php
	//	And probably an ajax page to go with it

  require_once(realpath(dirname(__DIR__) . "/../private/class/RTBAddonManager.php"));

	$_PAGETITLE = "Blockland Glass | RTB Boards";
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
?>
<div class="maincontainer">
  <h1 style="text-align:center"><img src="/img/rtb_logo.gif"><br /><?php echo $_GET['name']; ?></h1>
  <a href="/addons/">Addons</a> >> <a href="/addons/rtb/">RTB Archives</a> >> <a href="#"><?php echo $_GET['name']; ?></a>
	<table class="boardtable">
	<tbody>
		<tr class="boardheader">
			<td>Name</td>
			<td>ID</td>
		</tr>
<?php
  $addons = RTBAddonManager::getFromType($_GET['name']);

	foreach($addons as $addon) {
		?>
		<tr>
		<td style="width: 33%"><a href="view.php?id=<?php echo $addon->id?>"><?php echo $addon->title ?></a></td>
		<td><?php echo $addon->id ?></td>
		</tr><?php
	}

	//TO DO: page number links should also appear at the bottom, probably inside of the grey footer
?>
		<tr class="boardheader">
			<td colspan="4"></td>
		</tr>
		</tbody>
	</table>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
