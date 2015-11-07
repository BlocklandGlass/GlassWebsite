<table class="commenttable">
<tbody>
<?php
	//I really don't know if I want to have this page serve json and have browsers turn the json into html
	//or if I want to send an html table like this

	//header("Content-Type: application/json");
	//echo(json_encode(include(realpath(dirname(__DIR__) . "/private/json/getComments.php"))));
	$response = include(realpath(dirname(__DIR__) . "/private/json/getPageCommentsWithUsers.php"));

	if(empty($response)) {
		echo("<tr style=\"vertical-align:top\">");
		echo("<td colspan=\"2\" style=\"text-align: center;\">");
		echo("Bad Request.");
		echo("</td></tr>");
	} else {
		$users = $response['users'];
		$comments = $response['comments'];

		if(empty($comments)) {
			echo("<tr style=\"vertical-align:top\">");
			echo("<td colspan=\"2\" style=\"text-align: center;\">");
			echo("There are no comments here yet.");
			echo("</td></tr>");
		} else {
			foreach($comments as $comment) {
				$user = $users[$comment->blid];
				echo("<tr style=\"vertical-align:top\">");
				echo("<td style=\"width: 150px;\">");
				echo("<a href=\"/user/view.php?id=" . $user['blid'] . "\">" . htmlspecialchars($user['username']) . "</a>");
				echo("<br /><span style=\"font-size: .8em;\">" . $user['blid'] . "<br />");

				if($user['admin']) {
					echo("Administrator");
				} elseif($user['banned']) {
					echo("Banned");
				}
				echo("</span></td><td>");
				echo(htmlspecialchars($row->comment));
				echo("</td></tr>");
			}
		}
	}
?>
</tbody>
</table>
