<h2>Board Management</h2>

<?php
  use Glass\DatabaseManager;
  use Glass\BoardManager;

	if(!$user->inGroup("Administrator")) {
    die('You do not have permission to access this area.');
  }

  if(isset($_POST['name']) && isset($_POST['icon']) && isset($_POST['desc'])) {
    if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
      throw new \Exception("Cross site request forgery attempt blocked");
    }
    
    $db = new DatabaseManager();
    $db->query("INSERT INTO `addon_boards` (`id`, `group`, `name`, `icon`, `description`) VALUES (NULL, '" . $db->sanitize($_POST['group']) . "', '" . $db->sanitize($_POST['name']) . "', '" . $db->sanitize($_POST['icon']) . "', '" . $db->sanitize($_POST['desc']) . "');");
  }
?>

<table style="width: 100%" class="listTable">
  <thead>
    <tr>
      <th></th>
      <th>Board</th>
      <th>Add-Ons</th>
      <th>Group</th>
      <th>Options</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $boards = BoardManager::getAllBoards();
    foreach($boards as $board) {
      echo "<tr>";
      echo "<td><img src=\"/img/icons16/" . $board->icon . ".png\"></td>";
      echo "<td>" . $board->getName() . "</td>";
      echo "<td>" . $board->getCount() . "</td>";
      echo "<td>" . $board->getGroup() . "</td>";
      echo "<td><a href=\"#\">Manage</a> | <a href=\"#\">Delete</a></td>";
      echo "</tr>";
    }
    ?>
  </tbody>
</table>
<hr />
<form action="?tab=board" method="post">
  <table class="formtable">
    <tbody>
      <tr><td class="center" colspan="2"><h3>Create Board</h3></td></tr>
      <tr><td>Board Name:</td><td><input type="text" name="name" id="name"></td></tr>
      <tr><td>Group Name:</td><td><input type="text" name="group" id="group"></td></tr>
      <tr><td>Icon:</td><td><input type="text" name="icon" id="icon"></td></tr>
      <tr><td>Description:</td><td><textarea name="desc" id="desc"></textarea></tr>
      <tr><td class="center" colspan="2"><input class="green" type="submit"></td></tr>
    </tbody>
  </table>
  <input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
</form>
