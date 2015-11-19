<tbody>
<?php
	$response = include(realpath(dirname(__DIR__) . "/private/json/getNewAddonsWithUsers.php"));
	$addons = $response['addons'];
	$users = $response['users'];

	foreach($addons as $index=>$addon) {
		$user = $users[$addon->blid];
		echo("<tr>");
		echo("<td style=\"padding: 15px; width: 20px;\">" . ($index+1) . ".</td>");
		echo("<td><a href=\"/addons/addon.php?id=" . $addon->id . "\">" . htmlspecialchars($addon->name) . "</a>");
		echo(" by <a href=\"/user/view.php?blid=" . $user->blid . "\">" . htmlspecialchars($user->username) . "</a></td>");
		echo("<td style=\"padding: 20px;\">" . date("D, g:i a", strtotime($addon->uploadDate)) . "</td>");
		echo("</tr>");
	}
?>
</tbody>
