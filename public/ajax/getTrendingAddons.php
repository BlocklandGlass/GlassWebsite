<table class="listTable" style="width: 100%; text-align:left;">
	<thead>
		<tr>
			<th></th>
			<th>Add-On</th>
			<th>Uploader</th>
			<th>Downloads</th>
		</tr>
	</thead>
	<tbody>
	<?php
		use Glass\BoardManager;
		$response = include(realpath(dirname(__DIR__) . "/../private/json/getTrendingAddonsWithUsers.php"));
		$addons = $response['addons'];
		$users = $response['users'];

		$ct = 0;

		foreach($addons as $index=>$addon) {
			$user = $users[$addon->blid];

			if($addon->getDownloads("iteration") == 0) {
				break;
			}
			$ct++;
			?>
			<tr>
				<td style="text-align: center; padding: 10px; width: 20px;font-family: Impact, HelveticaNeue-CondensedBold, Helvetica Neue; font-size:1.5em"><?php echo $index+1; ?></td>
				<td style="line-height: 1.1em; text-align: left">
					<a href="/addons/addon.php?id=<?php echo $addon->id ?>"><?php echo htmlspecialchars($addon->getName()) ?></a>
					<br />
					<a style="font-size: 0.8em; color: #999;" href="/addons/board.php?id=<?php echo $addon->getBoard() ?>">
	        <?php
	        	echo htmlspecialchars(BoardManager::getFromId($addon->getBoard())->getName());
					?>
	      </td>
				<td>
					<?php
						echo htmlspecialchars(utf8_encode($addon->getAuthor()->getUsername()));
					?>
				</td>
				<td style="font-weight: bold;">
					<?php
						echo $addon->getDownloads("iteration");
					?>
				</td>
			</tr>
			<?php
		}

		if($ct == 0) {
			echo '<tr><td colspan="4" style="text-align:center; padding: 15px">No downloads this week.</td></tr>';
		}
	?>
	</tbody>
</table>
