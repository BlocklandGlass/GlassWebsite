<?php
	use Glass\BoardManager;
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
?>
<div class="maincontainer">
<?php
  include(realpath(dirname(__DIR__) . "/../private/navigationbar.php")); #636
  include(realpath(dirname(__DIR__) . "/../private/searchbar.php"));
?>
<table style="margin-left: auto;margin-right: auto;">
<tbody>
<tr>
<td class="center" colspan="2"><h3 style="-webkit-margin-before: 0em;">Board Directory</h3></td>

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
			<div class="videomask">
				<a href="/addons/board.php?id=<?php echo($board->getID()); ?>">
					<video class="background_video" width="320" height="180" poster="/img/<?php echo($board->getVideo()); ?>.png" loop>
						<source src="/img/<?php echo($board->getVideo()); ?>.webm">
						<source src="/img/<?php echo($board->getVideo()); ?>.mp4">
						Your browser does not support video tags.
					</video>
					<div class="videooverlay">
						<p class="boardtitle"><?php echo $board->getName(); ?></p>
						<p class="boarddesc"><?php echo $board->getDescription(); ?></p>
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
	$(".videooverlay").hover(function () {
		this.previousElementSibling.play();
	}, function () {
		this.previousElementSibling.pause();
	});
});
</script>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
