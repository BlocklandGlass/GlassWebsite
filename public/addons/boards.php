<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\BoardManager;

	$_PAGETITLE = "Blockland Glass | Boards";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
?>

<div class="maincontainer">
  <?php include(realpath(dirname(__DIR__) . "/../private/searchbar.php")); ?>
  <h1 style="text-align:center">Boards</h1>
  <a href="/addons/">Add-Ons</a> >> <a href="#">Boards</a>
	<div class="tile">
	<table class="boardtable">
		<tbody>
			<tr class="boardheader shadow-1">
				<td></td>
				<td>Name</td>
				<td>Files</td>
			</tr>
			<?php
				$groups = BoardManager::getBoardGroups();
				foreach($groups as $group) {
				  $boards = BoardManager::getGroup($group);

					?>
					<tr><td style="width:0px"></td></tr>
					<tr class="boardheader shadow-1">
						<td colspan="3"><b><?php echo $group; ?></b></td>
					</tr>
					<?php

					foreach($boards as $board) {
						?>
						<tr>
							<td><img src="/img/icons32/<?php echo $board->getIcon() ?>.png" /></td>
							<td><a href="board.php?id=<?php echo($board->getID()); ?>"><?php echo($board->getName()); ?></a></td>
							<td><?php echo($board->getCount()); ?></td>
						</tr>
						<?php
					}
				}
			?>
		</tbody>
	</table>
	</div>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
