<table class="commenttable">
<tbody>
<?php
	//This page is designed to be requested by ajax
	//I also want it to be possible to request this content in-game.
	//In the future the file that actually interacts with the database should be in /private/class, while this one processes get requests and formats data

	require_once(realpath(dirname(__DIR__) . "/private/class/DatabaseManager.php"));
	$database = new DatabaseManager();
	$result = $database->query("SELECT * FROM `addon_comments` WHERE `aid` = '" . $database->sanitize($_GET['id']) . "'");

	if(!$result) {
		echo("Database error: " . $database->error());
	} else {
		if($result->num_rows == 0) {
			echo("<tr style=\"vertical-align:top\">");
			echo("<td colspan=\"2\" style=\"text-align: center;\">");
			echo("There are no comments here yet.");
			echo("</td></tr>");
		} else {
			require_once(realpath(dirname(__DIR__) . "/private/class/UserHandler.php"));

			while($row = $result->fetch_object()) {
				$user = UserManager::getFromId($row->uid);
				echo("<tr style=\"vertical-align:top\">");
				echo("<td style=\"width: 150px;\">");
				echo("<a href=\"/user/view.php?id=" . $user->getID() . "\">" . htmlspecialchars($user->getUsername()) . "</a>");

				//Not sure where administrator status is stored.  My guess is 'groups' but I can't be certain.
				//At any rate, we should probably go and rethink the database tables for long term use.
				echo("<br /><span style=\"font-size: .8em;\">" . $user->getBLID() . "<br />Administrator?</span>");
				echo("</td><td>");
				echo(htmlspecialchars($row->comment));
				echo("</td></tr>");
			}
		}
		$result->close();
	}
?>
</tbody>
</table>
