<?php
	//this page is designed to be requested by ajax or the in-game client

	use Glass\DatabaseManager;
	require_once(realpath(dirname(__DIR__) . "/../private/lib/Parsedown.php"));

	if(!isset($_POST['query'])) {
		echo("Invalid search");
	} else {
		$db = new DatabaseManager();
		$baseQuery = "SELECT * FROM `addon_addons` WHERE `name` LIKE '%" . $db->sanitize($_POST['query']) . "%'";

		//later on we can make it so administrators can search for deleted add-ons
		$extendedQuery = " AND `deleted` = 0";

		if(isset($_POST['blid'])) {
			try {
				use Glass\UserManager;
				$user = UserManager::getFromBLID($_POST['blid']);
				$extendedQuery = $extendedQuery . " AND `author` = '" . $db->sanitize($_POST['blid']) . "'";
			} catch(Exception $e) {
				echo("<p>User " . utf8_encode($_POST['blid']) . " not found.</p>");
			}
		}

		//One of the few time's we'll use a direct SQL query on a page
		$result = $db->query($baseQuery . $extendedQuery);

		echo("<h2>Search Results for ");
		echo("\"<u>" .utf8_encode($_POST['query']) . "</u>\"");

		if(isset($user) && $user) {
			echo(" by <a href=\"/user/view.php?id=" . $user->getID() . "\">" . utf8_encode($user->getUsername()) . "</a>");
		}
		echo("</h2><hr />");

		if($result->num_rows) {
			while($row = $result->fetch_object()) {
				echo "<p><strong><a href=\"addon.php?id=" . $row->id . "\">" . utf8_encode($row->name) . "</a></strong><br />";

				if(strlen($row->description) > 200) {
					$desc = substr($row->description, 0, 196) . " ...";
				} else {
					$desc = $row->description;
				}

				$Parsedown = new Parsedown();
				$Parsedown->setBreaksEnabled(true);
				$Parsedown->setMarkupEscaped(true);

				//may need escaping
				echo $Parsedown->text($desc);
				echo "</p><br />";
			}
		} else {
			echo "We couldn't find anything. Sorry about that.";
		}
		$result->close();
	}
?>
