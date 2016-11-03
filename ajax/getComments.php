<table class="commenttable">
<tbody>
<?php
	//I really don't know if I want to have this page serve json and have browsers turn the json into html
	//or if I want to send an html table like this

	//header("Content-Type: application/json");
	//echo(json_encode(include(realpath(dirname(__DIR__) . "/private/json/getComments.php"))));
	include_once (realpath(dirname(__DIR__) . "/private/class/UserManager.php"));
	$response = include(realpath(dirname(__DIR__) . "/private/json/getPageCommentsWithUsers.php"));

	$user = UserManager::getCurrent();

	if(empty($response)) {
		echo("<tr style=\"vertical-align:top\">");
		echo("<td colspan=\"2\" style=\"text-align: center;\">");
		echo("Bad Request.");
		echo("</td></tr>");
	} else {
		$users = $response['users'];
		$comments = $response['comments'];

		if($user) {
			echo("<tr style=\"vertical-align:top\">");
			echo("<td>Leave a comment:</td>");
			echo("<td style=\"text-align:center\"><textarea name=\"comment\" style=\"font-size:0.6em;\"></textarea><input type=\"submit\" value=\"Post\">");
			echo("</td></tr>");
		}

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
				echo("<a href=\"/user/view.php?blid=" . $user->getBLID() . "\">" . htmlspecialchars($user->getUsername()) . "</a>");
        echo("<br /><span style=\"font-size: .8em;\">" . date("M jS Y, g:i a", strtotime($comment->getTimeStamp())));
				echo("<br />" . $user->getBLID() . "<br />");

				if($user->inGroup("Administrator")) { //add check if user is author of add-on
					echo("<b style=\"color: red\">Administrator</b>");
				} elseif($user->inGroup("Moderator")) {
					echo("<b style=\"color: orange\">Moderator</b>");
				} elseif($user->inGroup("Reviewer")) {
					echo("<b style=\"color: green\">Reviewer</b>");
				//} elseif($user->banned) {
					//echo("<b>Banned</b>");
				}
				echo("</span></td><td>");
				echo(htmlspecialchars($comment->getComment()));
				echo("</td></tr>");
			}
		}
	}
?>
</tbody>
</table>
