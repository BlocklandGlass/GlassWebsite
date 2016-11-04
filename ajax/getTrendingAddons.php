<tbody>
<?php
	$response = include(realpath(dirname(__DIR__) . "/private/json/getTrendingAddonsWithUsers.php"));
	$addons = $response['addons'];
	$users = $response['users'];

	foreach($addons as $index=>$addon) {
		$user = $users[$addon->blid];

		$med = false;
		if($index == 0) {
			$col = "ffeb7f";
			$med = true;
		} else if($index == 1) {
			$col = "cfcfcf";
			$med = true;
		} else if($index == 2) {
			$col = "d7985a";
			$med = true;
		} else if(floor($index/2) == $index/2) {
			$col = "e3e3e3";
		} else {
			$col = "ededed";
		}
		?>
		<tr style="background-color:#<?php echo $col;?>; border-radius: 15px; padding: 5px; margin:5px; display:block;<?php echo $med ? "box-shadow: 1px 2px 3px #888888;" : "" ?> ">
			<td style="padding: 10px; width: 20px;font-family: Impact, HelveticaNeue-CondensedBold, Helvetica Neue; font-size:1.5em"><?php echo $index+1; ?></td>
			<td style="line-height: 1em;"><a href="/addons/addon.php?id=<?php echo $addon->id?>"><?php echo htmlspecialchars($addon->name) ?></a> in
				<a href="/addons/board.php?id=<?php echo $addon->board?>"><?php echo($addon->board); ?></a><br />
				<span style="font-weight: bold; font-size: .6em"><?php echo date("M jS Y, g:i a", strtotime($addon->uploadDate)) ?></span></td>
		</tr>
		<?php
		/*echo("<tr>");
		echo("<td style=\"padding: 10px; width: 20px;\">" . ($index+1) . ".</td>");
		echo("<td><a href=\"/addons/addon.php?id=" . $addon->id . "\">" . htmlspecialchars($addon->name) . "</a>");
		echo(" by <a href=\"/user/view.php?blid=" . $user->blid . "\">" . htmlspecialchars($user->username) . "</a></td>");
		echo("<td style=\"padding: 10px;\">" . $addon->getTotalDownloads() . "</td>"); //to do: send data along
		echo("</tr>");*/
	}
?>
</tbody>
