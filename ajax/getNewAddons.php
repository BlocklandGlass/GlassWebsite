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
			<td style="padding: 10px; width: 20px;font-family: Impact, Helvetica Neue Condensed Black; font-size:1.5em"><?php echo $index+1; ?></td>
			<td style="line-height: 1em;"><a href="/addons/addon.php?id=<?php echo $addon->id?>"><?php echo htmlspecialchars($addon->name) ?></a> by
				<a href="/user/view.php?blid=<?php echo $user->blid ?>"><?php echo htmlspecialchars($user->username) ?></a><br />
				<span style="font-weight: bold; font-size: .6em"><?php echo date("F jS \a\\t g:i a", strtotime($addon->uploadDate)) ?></span></td>
		</tr>
		<?php
	}
?>
</tbody>
