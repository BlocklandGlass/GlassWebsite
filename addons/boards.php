<?php
	require_once(realpath(dirname(__DIR__) . "/private/class/BoardManager.php"));
	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
<?php include(realpath(dirname(__DIR__) . "/private/searchbar.php")); ?>
<table style="margin-left: auto;margin-right: auto;">
<tbody>
<tr>
<td class="center" colspan="2"><h3>Board Directory</h3></td>

<?php
	$boards = BoardManager::getAllBoards();
	$alternator = false;

	foreach($boards as $board) {
		if(!$alternator) {
			echo("</tr><tr>");
		}
		$alternator = !$alternator;
		?>
		<td class="boardbutton">
			<div style="background-image:url('/img/<?php echo $board->getVideo() ?>.png');">
				<a href="/addons/board.php?id=<?php echo $board->getID() ?>">
					<div class="boardbuttoncontent">
						<div>
							<h2><?php echo $board->getName(); ?></h2>
							<div class="boarddesc"><?php echo $board->getDescription(); ?></div>
						</div>
					</div>
				</a>
			</div>
		</td>
		<?php
		/*
		echo("<td class=\"boardcontainer\"><div class=\"videomask\"><a href=\"/addons/board.php?id=" . $board->getID() . "\">");
		echo("<p class=\"boardtitle\">" . $board->getName() . "</p>");
		echo("<p class=\"boarddesc\">" . $board->getDescription() . "</p>");
		echo("<video class=\"background_video\" width=\"320\" height=\"180\" poster=\"/img/" . $board->getVideo() . ".png\" loop>");
		echo("<source src=\"/img/" . $board->getVideo() . ".webm\">");
		echo("<source src=\"/img/" . $board->getVideo() . ".mp4\">");
		echo("Your browser does not support video tags.");
		echo("</video></a></div></td>");*/
	}
?>
</tr>
</tbody>
</table>
</div>

<script type="text/javascript">
$(document).ready(function () {
	$(".background_video").hover(function () {
		this.play();
	}, function () {
		this.pause();
	});
});
</script>

<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
