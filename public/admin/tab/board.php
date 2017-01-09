<?php
if(($_adminAuthed ?? false) != true)
  die();

use Glass\DatabaseManager;
use Glass\BoardManager;

if(isset($_POST['name']) && isset($_POST['icon']) && isset($_POST['desc'])) {
  $db = new DatabaseManager();
  $db->query("INSERT INTO `addon_boards` (`id`, `group`, `name`, `icon`, `description`) VALUES (NULL, '" . $db->sanitize($_POST['group']) . "', '" . $db->sanitize($_POST['name']) . "', '" . $db->sanitize($_POST['icon']) . "', '" . $db->sanitize($_POST['desc']) . "');");
}

?>
<table style="width: 100%">
  <tbody>
    <tr>
      <th style="width: 50%">Board</th>
      <th style="">Add-Ons</th>
      <th style="">Group</th>
    </tr>
    <?php
    $boards = BoardManager::getAllBoards();
    foreach($boards as $board) {
      echo "<tr>";
      echo "<td>" . $board->getName() . "</td>";
      echo "<td>" . $board->getCount() . "</td>";
      echo "<td>" . $board->getGroup() . "</td>";
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
    <tr><td class="center" colspan="2"><input type="submit"></td></tr>
  </tbody>
</table>
<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
<?php
  if(isset($_POST['redirect'])) {
    echo("<input type=\"hidden\" name=\"redirect\" value=\"" . htmlspecialchars($_POST['redirect']) . "\">");
  }
?>
</form>
