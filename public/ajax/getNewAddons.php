<table class="listTable" style="width: 100%; text-align:left;">
	<thead>
		<tr>
			<th></th>
			<th>Add-On</th>
			<th>Date</th>
			<th>Author</th>
		</tr>
	</thead>
	<tbody>
	<?php
		use Glass\BoardManager;
		$response = include(realpath(dirname(__DIR__) . "/../private/json/getNewAddonsWithUsers.php"));
		$addons = $response['addons'];
		$users = $response['users'];

		foreach($addons as $index=>$addon) {
			$user = $users[$addon->blid];
			?>
			<tr>
				<td style="padding: 10px; width: 20px;font-family: Impact, HelveticaNeue-CondensedBold, Helvetica Neue; font-size:1.5em"><?php echo $index+1; ?></td>
				<td style="line-height: 1.1em; text-align: left;">
					<a href="/addons/addon.php?id=<?php echo $addon->id ?>"><?php echo utf8_encode($addon->getName()) ?></a>
					<br />
					<a style="font-size: 0.8em; color: #999;" href="/addons/board.php?id=<?php echo $addon->getBoard() ?>">
	        <?php
	        	// work around because boardmanager::getfromid decides to hang if you're not logged into the site
	        	echo htmlspecialchars(BoardManager::getFromId($addon->getBoard())->getName()); ?>
          </a>
				</td>
				<td style="font-size: .8em">
					<?php
						$since = time()-strtotime($addon->uploadDate);
						if($since < 30) {
							$time = "Just now";
						} else if($since < 60) {
							$time = floor($since) . " seconds ago";
						} else if($since < 3600) {
							$time = floor($since/60) . " minute" . (floor($since/60) == 1 ? "" : "s") . " ago";
						} else if($since < 3600*24) {
							$time = floor($since/(3600)) . " hour" . (floor($since/3600) == 1 ? "" : "s") . " ago";
						} else if($since < 3600*48) {
							$time = "Yesterday at " . date("g:i A", strtotime($addon->uploadDate));
						} else {
							$time = date("m/j/Y", strtotime($addon->uploadDate));
						}
						echo $time; ?>
				</td>
				<td style="font-size: .8em">
					<?php echo htmlspecialchars($user->getUsername()); ?>
				</td>
			</tr>
			<?php
		}
	?>
	</tbody>
</table>
