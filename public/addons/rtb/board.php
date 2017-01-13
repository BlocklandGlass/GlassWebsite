<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
	use Glass\BoardManager;
	use Glass\AddonManager;
	//use Glass\AddonObject;
	use Glass\UserManager;
	//use Glass\UserHandler;

	//TO DO: rewrite this page to use /private/json/getBoardAddonsWithUsers.php
	//	And probably an ajax page to go with it

  use Glass\RTBAddonManager;

	$_PAGETITLE = "Blockland Glass | RTB Boards";
	include(realpath(dirname(__DIR__) . "/../../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
?>
<div class="maincontainer">
  <h1 style="text-align:center"><img src="/img/rtb_logo.gif"><br /><?php echo $_GET['name']; ?></h1>
  <a href="/addons/">Add-Ons</a> >> <a href="/addons/rtb/">RTB Archive</a> >> <a href="#"><?php echo $_GET['name']; ?></a>
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

<?php include(realpath(dirname(__DIR__) . "/../../private/footer.php")); ?>
