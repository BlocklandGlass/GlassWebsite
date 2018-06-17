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

	$_PAGETITLE = "Blockland Glass | RTB Archive";
	include(realpath(dirname(__DIR__) . "/../../private/header.php"));
?>

<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
  ?>
  <h1 style="text-align:center"><img style="max-width: 100%;" src="/img/rtb_logo.gif"><br />Archive</h1>
  <a href="/addons/">Add-Ons</a> >> <a href="#">RTB Archive</a>

	<div class="tile">
		<table class="boardtable">
			<tbody>
				<tr class="boardheader shadow-1">
					<td style="margin:0;padding:0;width:0;"></td>
					<td>Name</td>
					<td style="width:70px">Files</td>
				</tr>
	<?php
	  $boards = RTBAddonManager::getBoards();

		foreach($boards as $board) {
			?>
			<tr>
			<td style="margin:0;padding:0;width:0;"></td>
			<td><a href="board.php?name=<?php echo $board?>"><?php echo $board ?></a></td>
			<td><?php echo RTBAddonManager::getBoardCount($board); ?></td>
			</tr><?php
		}

		//TO DO: page number links should also appear at the bottom, probably inside of the grey footer
	?>
			</tbody>
		</table>
  </div>
</div>

<?php include(realpath(dirname(__DIR__) . "/../../private/footer.php")); ?>
