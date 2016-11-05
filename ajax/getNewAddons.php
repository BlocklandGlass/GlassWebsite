<tbody>
<?php
	$response = include(realpath(dirname(__DIR__) . "/private/json/getNewAddonsWithUsers.php"));
	$addons = $response['addons'];
	$users = $response['users'];

	foreach($addons as $index=>$addon) {
		$user = $users[$addon->blid];
		if(floor($index/2) == $index/2) {
			$col = "ededed";
		} else {
			$col = "e3e3e3";
		}
		?>
		<tr style="background-color:#<?php echo $col;?>; border-radius: 15px; padding: 5px; margin:5px; display:block;">
			<td style="padding: 10px; width: 20px;font-family: Impact, HelveticaNeue-CondensedBold, Helvetica Neue; font-size:1.5em"><?php echo $index+1; ?></td>
			<td style="line-height: 1em;"><a href="/addons/addon.php?id=<?php echo $addon->id ?>"><?php echo htmlspecialchars($addon->getName()) ?></a> in
				<a href="/addons/board.php?id=<?php echo $addon->getBoard() ?>">
        <?php
        // work around because boardmanager::getfromid decides to hang if you're not logged into the site
        $board[1] = "Client Mods";
        $board[2] = "Server Mods";
        $board[3] = "Bricks";
        $board[4] = "Cosmetics";
        $board[5] = "Gamemodes";
        $board[6] = "Tools";
        $board[7] = "Weapons";
        $board[8] = "Colorsets";
        $board[9] = "Vehicles";
        $board[10] = "Bargain Bin";
        $board[11] = "Sounds";

        echo $board[$addon->board];
        ?></a><br />
				<span style="font-weight: bold; font-size: .6em"><?php echo date("M jS Y, g:i A", strtotime($addon->uploadDate)) ?></span></td>
		</tr>
		<?php
	}
?>
</tbody>
