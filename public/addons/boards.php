<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\BoardManager;

	$_PAGETITLE = "Boards | Blockland Glass";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
?>

<div class="maincontainer">
	<?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
  <ul class="addonnav tile">
    <?php
      include(realpath(dirname(__DIR__) . "/../private/searchbar.php"));
    ?>
  </ul>
  <div style="margin-top: 20px; margin-left: 20px;">
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
				<tr class="boardheader" style="position: relative !important;">
					<td colspan="3"><strong><?php echo $group; ?></strong></td>
				</tr>
				<?php

				foreach($boards as $board) {
					?>
					<tr>
						<td style="width: 40px"><img src="/img/icons32/<?php echo $board->getIcon() ?>.png" /></td>
						<td><a href="board.php?id=<?php echo($board->getID()); ?>"><?php echo($board->getName()); ?></a></td>
						<td style="width: 15%"><?php echo($board->getCount()); ?></td>
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
