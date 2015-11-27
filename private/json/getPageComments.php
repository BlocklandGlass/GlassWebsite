<?php
	if(!isset($_GET['aid'])) {
		return [];
	}
	require_once(realpath(dirname(__DIR__) . "/class/CommentManager.php"));
	$aid = $_GET['aid'] + 0; //force it to be a number
	$commentIDs = CommentManager::getCommentIDsFromAddon($aid);
	$comments = [];

	foreach($commentIDs as $cid) {
		$comments[] = CommentManager::getFromID($cid);
	}

	return $comments;

//	require_once(realpath(dirname(__DIR__) . "/private/class/DatabaseManager.php"));
//	$database = new DatabaseManager();
//
//	//the "and `verified` = 1 can be deleted if we decide to force blid database entries to be unique
//	$result = $database->query("SELECT * FROM `addon_comments` WHERE `blid` = '" . $database->sanitize($_GET['blid']) . "' AND `verified` = 1");
//
//	if(!$result) {
//		echo("Database error: " . $database->error());
//	} else {
//		if($result->num_rows == 0) {
//			echo("<tr style=\"vertical-align:top\">");
//			echo("<td colspan=\"2\" style=\"text-align: center;\">");
//			echo("There are no comments here yet.");
//			echo("</td></tr>");
//		} else {
//			require_once(realpath(dirname(__DIR__) . "/private/class/UserHandler.php"));
//
//			while($row = $result->fetch_object()) {
//				$user = UserManager::getFromId($row->uid);
//				echo("<tr style=\"vertical-align:top\">");
//				echo("<td style=\"width: 150px;\">");
//				echo("<a href=\"/user/view.php?id=" . $user->getID() . "\">" . htmlspecialchars($user->getUsername()) . "</a>");
//
//				//Not sure where administrator status is stored.  My guess is 'groups' but I can't be certain.
//				//At any rate, we should probably go and rethink the database tables for long term use.
//				echo("<br /><span style=\"font-size: .8em;\">" . $user->getBLID() . "<br />Administrator?</span>");
//				echo("</td><td>");
//				echo(htmlspecialchars($row->comment));
//				echo("</td></tr>");
//			}
//		}
//		$result->close();
//	}
?>
