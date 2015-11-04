<?php
require_once(realpath(dirname(__DIR__) . "/private/class/DatabaseManager.php"));
require_once(realpath(dirname(__DIR__) . "/private/lib/Parsedown.php"));

$_PAGETITLE = "Glass | Search Results";

include(realpath(dirname(__DIR__) . "/private/header.php"));
include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

//One of the few time's we'll use a direct SQL query on a page

$db = new DatabaseManager();
$result = $db->query("SELECT * FROM `addon_addons` WHERE `deleted` = 0 AND `name` LIKE '%" . $db->sanitize($_POST['query']) . "%'");
?>
<div class="maincontainer">
	<h2>Search Results for <u><?php htmlspecialchars($_POST['query']) . "\n"); ?></u></h2>
	<hr />
	<?php
	if($result->num_rows) {
		while($row = $result->fetch_object()) {
			echo "<p><b><a href=\"addon.php?id=" . $row->id . "\">" . htmlspecialchars($row->name) . "</a></b><br />";

			if(strlen($row->description) > 200) {
				$desc = substr($row->description, 0, 200) . " ...";
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
	?>
</div>

<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
