<?php
require_once(realpath(dirname(__DIR__) . "/private/class/DatabaseManager.php"));

$_PAGETITLE = "Glass | Search Results";

require_once(realpath(dirname(__DIR__) . "/private/header.php"));
require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

//One of the few time's we'll use a direct SQL query on a page

$db = new DatabaseManager();
$result = $db->query("SELECT * FROM `addon_addons` WHERE `name` LIKE '%" . $db->sanitize($_POST['query']) . "%'");
?>
<div class="maincontainer">
	<h2>Search Results for <u><?php echo $_POST['query']; ?></u></h2>
	<hr />
	<?php
	while($row = $result->fetch_object()) {
		echo "<p><b><a href=\"addon.php?id=$row->id\">$row->name</a></b><br />";
		if(strlen($row->description) > 200) {
			$desc = substr($row->description, 200) . "...";
		} else {
			$desc = $row->description;
		}
		echo $desc . "</p>";
	}
	?>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
