<table class="commenttable">
<tbody>
<?php
	//I really don't know if I want to have this page serve json and have browsers turn the json into html
	//or if I want to send an html table like this

	//header("Content-Type: application/json");
	//echo(json_encode(include(realpath(dirname(__DIR__) . "/../private/json/getComments.php"))));
	use Glass\UserManager;
  use Glass\GroupManager;
	$response = include(realpath(dirname(__DIR__) . "/../private/json/getPageCommentsWithUsers.php"));

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
				echo("<a href=\"/user/view.php?blid=" . $user->getBLID() . "\">" . utf8_encode($user->getUsername()) . "</a>");
				echo("<br /><span style=\"font-size: .8em;\">" . $user->getBLID());
				echo("<br />" . date("M jS Y, g:i A", strtotime($comment->getTimeStamp())) . "<br />");

        if($user->getBanned()) {
					echo("<span style=\"color: gray\">Banned</span>");
				} else {
          $foundGroup = false;

          if($user->getBLID() == $addonObject->getAuthor()->getBLID()) {
            echo("<span style=\"font-weight: bold;\">Uploader</span>");
          } elseif($user->inGroup("Administrator")) {
            $foundGroup = GroupManager::getFromName("Administrator");
          } elseif($user->inGroup("Reviewer")) {
            $foundGroup = GroupManager::getFromName("Reviewer");
          } elseif($user->inGroup("Moderator")) {
            $foundGroup = GroupManager::getFromName("Moderator");
          }

          if($foundGroup) {
            echo("<span style=\"color: #" . $foundGroup->getColor() . "; font-weight: bold;\">" . htmlspecialchars($foundGroup->getName()) . "</span>");
          }
        }

				echo("</span></td><td>");
				echo(utf8_encode($comment->getComment()));
				echo("</td></tr>");
			}
		}
	}
?>
</tbody>
</table>
