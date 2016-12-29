<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\BoardManager;

	$_PAGETITLE = "Blockland Glass | Boards";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
?>

<div class="maincontainer">
  <?php include(realpath(dirname(__DIR__) . "/../private/searchbar.php")); ?>
  <div style="margin-left: 20px;">
		<a href="/addons/">Add-Ons</a> >> <a href="#">Boards</a>
	</div>
	<div class="tile">
		<?php
			$groups = BoardManager::getBoardGroups();
			foreach($groups as $group) {
				echo '<table class="boardtable" style="margin-bottom: 15px;">';
				echo '<tbody>';
			  $boards = BoardManager::getGroup($group);

				?>
				<tr class="boardheader shadow-1" style="position: relative !important;">
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

				echo '</tbody></table>';
			}

			if(sizeof($groups) == 0) {
				?>
				<tr>
					<td colspan="3">No Boards - Likely an error?</td>
				</tr>
				<?php
			}
		?>
	</div>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
