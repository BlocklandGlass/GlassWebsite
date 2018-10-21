<table class="commenttable">
<tbody>
<?php
	//I really don't know if I want to have this page serve json and have browsers turn the json into html
	//or if I want to send an html table like this

	//header("Content-Type: application/json");
	//echo(json_encode(include(realpath(dirname(__DIR__) . "/../private/json/getComments.php"))));
	use Glass\UserManager;
  use Glass\GroupManager;
  use Glass\AddonManager;
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
      if(isset($addonObject)) {
        $rejected = $addonObject->isRejected();
      }
			echo("<tr style=\"vertical-align:top\">");
			echo("<td>Leave a comment:</td>");
			echo("<td style=\"text-align:center\"><textarea name=\"comment\" style=\"font-size:0.6em;\"" . ($rejected ? " disabled" : "") . "></textarea><input type=\"submit\" value=\"Post\"" . ($rejected ? " disabled" : "") . ">");
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
				echo("<a href=\"/user/view.php?blid=" . $user->getBLID() . "\">" . htmlspecialchars(utf8_encode($user->getUsername())) . "</a>");
				echo("<br /><span style=\"font-size: .8em;\">" . $user->getBLID());
				echo("<br />" . date("M jS Y, g:i A", strtotime($comment->getTimeStamp())) . "<br />");

        if($user->getBanned()) {
					echo("<img src=\"/img/icons16/list_suspended_accounts.png\"> <span style=\"color: gray;\" title=\"This user has been banned from the Glass website.\">Banned</span>");
				} else {
          $foundGroup = false;

          if($user->getBLID() == $addonObject->getAuthor()->getBLID()) {
            echo("<img src=\"/img/icons16/vhs.png\"> <span style=\"font-weight: bold;\" title=\"This user uploaded the current add-on.\">Uploader</span>");
          } elseif($user->inGroup("Administrator")) {
            $foundGroup = GroupManager::getFromName("Administrator");
          } elseif($user->inGroup("Reviewer")) {
            $foundGroup = GroupManager::getFromName("Reviewer");
          } elseif($user->inGroup("Moderator")) {
            $foundGroup = GroupManager::getFromName("Moderator");
          }

          if($foundGroup) {
            echo("<img src=\"/img/icons16/" . $foundGroup->getIcon() . ".png\"> <span style=\"color: #" . $foundGroup->getColor() . "; font-weight: bold;\" title=\"" . $foundGroup->getDescription() . "\">" . htmlspecialchars(utf8_encode($foundGroup->getName())) . "</span>");
          }
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
