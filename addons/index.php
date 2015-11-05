<?php
	require_once(realpath(dirname(__DIR__) . "/private/class/BoardManager.php"));
	require_once dirname(__DIR__) . "/private/class/BoardManager.php";

	$_PAGETITLE = "Glass | Add-Ons";

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<form action="search.php" method="post">
		<input class="searchbar" type="text" name="query" placeholder="Search..."/>
	</form>

	<table class="addontable">
	<tbody>
		<?php
		//This got kind of messy when I edited it to reflect boardManager changes
		//We should probably redo part of it anyway to reflect tags
		$boards = BoardManager::getAllBoards();
		usort($boards, function($a, $b) {
			return strcmp($a["name"], $b["name"]);
		});
		$subcat = array();
		foreach($boards as $board) {
			$subcat[$board["subCategory"]][] = $board;
		}
		foreach($subcat as $subName=>$sub) {
			echo "<tr class=\"addonheader\">
				<td colspan=\"3\"><b>" . htmlspecialchars($subName) . "</b></td>
			</tr>";
			foreach($sub as $board) {
				//echo "<tr><td><image src=\"http://blocklandglass.com/icon/icons32/" . $board->getImage() . ".png\" /></td><td><a href=\"board.php?id=" . $board->getId() . "\">   " . htmlspecialchars($board->getName()) . "</a></td><td>" . $board->getCount() . "</td></tr>";
				echo "<tr><td><image src=\"http://blocklandglass.com/icon/icons32/" . $board["icon"] . ".png\" /></td>";
				echo "<td><a href=\"board.php?id=" . $board["id"] . "\">   " . htmlspecialchars($board["name"]) . "</a></td>";
				$obj = new BoardObject($board["id"]);
				$count = $obj->getCount();
				echo "<td>" . $count . "</td></tr>";
			}
		}
		?>
		<tr class="addonheader">
			<td colspan="3"></td>
		</tr>
	</tbody>
	</table>
</div>
<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
