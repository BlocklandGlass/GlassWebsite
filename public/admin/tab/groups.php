<h2>Group Management</h2>

<?php
  use Glass\GroupManager;

	if(!$user->inGroup("Administrator")) {
    die('You do not have permission to access this area.');
  }
?>

<table style="width: 100%" class="listTable">
  <thead>
    <th></th>
    <th>Group</th>
    <th>Users</th>
    <th>Options</th>
  </thead>
  <tbody>
    <?php
      $groups = GroupManager::getGroups();
      foreach($groups as $group) {
        echo "<tr>";
        echo "<td><img src=\"/img/icons32/" . $group->icon . ".png\"></td>";
        echo "<td style=\"font-weight: bold; color: #" . $group->color . ";\">" . $group->name . "</td>";
        echo "<td>" . GroupManager::getMemberCountByID($group->id) . "</td>";
        echo "<td>";
        echo "<a href=\"?tab=group&id=" . $group->id . "\" class=\"btn small blue\">Manage</a>";
        // echo "<a href=\"#\" class=\"btn small red\">Remove</a>";
        echo "<input class=\"btn small red\" type=\"submit\" value=\"Remove\" disabled>";
        echo "</td>";
        echo "</tr>";
      }
    ?>
  </tbody>
</table>

<hr>

<form method="post">
  <table class="formtable">
    <tbody>
      <tr><td class="center" colspan="2"><h3>Create Group</h3></td></tr>
      <tr><td>Name:</td><td><input type="text" name="group" disabled></td></tr>
      <tr><td>Icon:</td><td><input type="text" name="icon" disabled></td></tr>
      <tr><td>Color:</td><td><textarea name="desc" disabled></textarea></tr>
      <tr><td>Description:</td><td><textarea name="desc" disabled></textarea></tr>
      <tr><td class="center" colspan="2"><input class="btn green" type="submit" value="Create" disabled></td></tr>
    </tbody>
  </table>
  <input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
  <?php
    if(isset($_POST['redirect'])) {
      echo("<input type=\"hidden\" name=\"redirect\" value=\"" . htmlspecialchars($_POST['redirect']) . "\">");
    }
  ?>
</form>