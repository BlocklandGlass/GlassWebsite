<tbody>
<?php
	$response = include(realpath(dirname(__DIR__) . "/private/json/getTrendingAddonsWithUsers.php"));
	$addons = $response['addons'];
	$users = $response['users'];

	foreach($addons as $index=>$addon) {
		$user = $users[$addon->blid];
		echo("<tr>");
		echo("<td style=\"padding: 10px; width: 20px;\">" . ($index+1) . ".</td>");
		echo("<td><a href=\"/addons/addon.php?id=" . $addon->id . "\">" . htmlspecialchars($addon->name) . "</a>");
		echo(" by <a href=\"/user/view.php?blid=" . $user->blid . "\">" . htmlspecialchars($user->username) . "</a></td>");
		echo("<td style=\"padding: 10px;\">" . $addon->getTotalDownloads() . "</td>"); //to do: send data along
		echo("</tr>");
	}
?>
</tbody>
