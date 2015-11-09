<?php
//	require_once(realpath(dirname(__DIR__) . "/private/class/BoardManager.php"));
//	require_once dirname(__DIR__) . "/private/class/BoardManager.php";

	$_PAGETITLE = "Glass | Add-Ons";

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<?php include(realpath(dirname(__DIR__) . "/private/searchbar.php")); ?>

	<table style="width: 100%">
		<tbody>
			<tr>
				<td style="width:50%">
					<h3>Trending</h3>
					<table style="width: 100%">
						<tbody>
							<tr>
								<td style="padding: 15px; width: 20px;">1.</td>
								<td><a href="#">Blockland Glass</a> by Jincux</td>
								<td style="padding: 20px;">164</td>
							</tr>
							<tr>
								<td style="padding: 15px; width: 20px;">2.</td>
								<td><a href="#">Preferences</a> by Jincux</td>
								<td style="padding: 20px;">133</td>
							</tr>
							<tr>
								<td style="padding: 15px; width: 20px;">3.</td>
								<td><a href="#">Admin Chat</a> by Jincux</td>
								<td style="padding: 20px;">101</td>
							</tr>
							<tr>
								<td style="padding: 15px; width: 20px;">4.</td>
								<td><a href="#">Server Vote</a> by Jincux</td>
								<td style="padding: 20px;">70</td>
							</tr>
							<tr>
								<td style="padding: 15px; width: 20px;">5.</td>
								<td><a href="#">Some other mod</a> by Nexus</td>
								<td style="padding: 20px;">56</td>
							</tr>
						</tbody>
					</table>
				</td>
				<td style="width:50%">
					<h3>Recent Uploads</h3>
					<table style="width: 100%">
						<tbody>
							<tr>
								<td style="padding: 15px; width: 20px;">1.</td>
								<td><a href="#">Blockland Glass</a> by Jincux</td>
								<td style="padding: 20px;">164</td>
							</tr>
							<tr>
								<td style="padding: 15px; width: 20px;">2.</td>
								<td><a href="#">Preferences</a> by Jincux</td>
								<td style="padding: 20px;">133</td>
							</tr>
							<tr>
								<td style="padding: 15px; width: 20px;">3.</td>
								<td><a href="#">Admin Chat</a> by Jincux</td>
								<td style="padding: 20px;">101</td>
							</tr>
							<tr>
								<td style="padding: 15px; width: 20px;">4.</td>
								<td><a href="#">Server Vote</a> by Jincux</td>
								<td style="padding: 20px;">70</td>
							</tr>
							<tr>
								<td style="padding: 15px; width: 20px;">5.</td>
								<td><a href="#">Some other mod</a> by Nexus</td>
								<td style="padding: 20px;">56</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<h3>Tags</h3>
					<p style="line-height: 200%">
						<a style="background-color: #ceffce; padding: 4px; border: 2px solid #99ff99; border-radius: 3px; margin: 0px;"><img style="padding-right: 4px;" src="https://blocklandglass.com/icon/icons16/tag_green.png">Server</a>
						<a style="background-color: #ffcece; padding: 4px; border: 2px solid #ff9999; border-radius: 3px; margin: 0px;"><img style="padding-right: 4px;" src="https://blocklandglass.com/icon/icons16/tag_red.png">Client</a>
						<a style="background-color: #ceceff; padding: 4px; border: 2px solid #9999ff; border-radius: 3px; margin: 0px;"><img style="padding-right: 4px;" src="https://blocklandglass.com/icon/icons16/tag_blue.png">Support</a>
						<a style="background-color: #ceffce; padding: 4px; border: 2px solid #99ff99; border-radius: 3px; margin: 0px;"><img style="padding-right: 4px;" src="https://blocklandglass.com/icon/icons16/tag_green.png">Weapon</a>
						<a style="background-color: #ffcece; padding: 4px; border: 2px solid #ff9999; border-radius: 3px; margin: 0px;"><img style="padding-right: 4px;" src="https://blocklandglass.com/icon/icons16/tag_red.png">Brick</a>
						<a style="background-color: #ceceff; padding: 4px; border: 2px solid #9999ff; border-radius: 3px; margin: 0px;"><img style="padding-right: 4px;" src="https://blocklandglass.com/icon/icons16/tag_blue.png">Sound</a>
						<a style="background-color: #ceffce; padding: 4px; border: 2px solid #99ff99; border-radius: 3px; margin: 0px;"><img style="padding-right: 4px;" src="https://blocklandglass.com/icon/icons16/tag_green.png">Environment</a>
						<a style="background-color: #ffcece; padding: 4px; border: 2px solid #ff9999; border-radius: 3px; margin: 0px;"><img style="padding-right: 4px;" src="https://blocklandglass.com/icon/icons16/tag_red.png">Skin</a>
						<a style="background-color: #ceceff; padding: 4px; border: 2px solid #9999ff; border-radius: 3px; margin: 0px;"><img style="padding-right: 4px;" src="https://blocklandglass.com/icon/icons16/tag_blue.png">Decal</a>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php /*
	<table class="addontable">
	<tbody>
	<?php
		$boardIndex = include(realpath(dirname(__DIR__) . "/private/json/getBoardIndex.php"));

		foreach($boardIndex as $subCategory => $boards) {
			echo("<tr class=\"addonheader\"><td colspan=\"3\"><b>" . htmlspecialchars($subCategory) . "</b></td></tr>");

			foreach($boards as $board) {
				echo("<tr><td><image src=\"http://blocklandglass.com/icon/icons32/" . $board->icon . ".png\" /></td>");
				echo("<td><a href=\"board.php?id=" . $board->id . "\">   " . htmlspecialchars($board->name) . "</a></td>");
				echo("<td>" . $board->count . "</td></tr>");
			}
		}

		//This got kind of messy when I edited it to reflect boardManager changes
		//We should probably redo part of it anyway to reflect tags
		//$boards = BoardManager::getAllBoards();
		//usort($boards, function($a, $b) {
		//	return strcmp($a->getName(), $b->getName());
		//});
		//$subcat = array();
		//foreach($boards as $board) {
		//	$subcat[$board->getSubCategory()][] = $board;
		//}
		//foreach($subcat as $subName=>$sub) {
		//	echo "<tr class=\"addonheader\">
		//		<td colspan=\"3\"><b>" . htmlspecialchars($subName) . "</b></td>
		//	</tr>";
		//	foreach($sub as $board) {
		//		echo "<tr><td><image src=\"http://blocklandglass.com/icon/icons32/" . $board->getIcon() . ".png\" /></td>";
		//		echo "<td><a href=\"board.php?id=" . $board->getID() . "\">   " . htmlspecialchars($board->getName()) . "</a></td>";
		//		echo "<td>" . $board->getCount() . "</td></tr>";
		//	}
		//}
		?>
		<tr class="addonheader">
			<td colspan="3"></td>
		</tr> */?>
	</tbody>
	</table>
</div>
<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
