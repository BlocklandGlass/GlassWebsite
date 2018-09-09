<?php
  use Glass\GroupManager;

	if(!$user->inGroup("Administrator")) {
    die('You do not have permission to access this area.');
  }

  if(!isset($_GET['id'])) {
    die('No group ID specified.');
  }

  $gid = $_GET['id'];
  $group = GroupManager::getFromID($gid);

  if(!$group) {
    die('Invalid group ID.');
  }

  $dirty = false;

  // if(isset($_POST['name'])) {
    // GroupManager::editGroupByGroupID($gid, "name", $_POST['name']);
  // }

  if(isset($_POST['icon'])) {
    GroupManager::editGroupByGroupID($gid, "icon", $_POST['icon']);
    $dirty = true;
  }

  if(isset($_POST['color'])) {
    GroupManager::editGroupByGroupID($gid, "color", $_POST['color']);
    $dirty = true;
  }

  if(isset($_POST['description'])) {
    GroupManager::editGroupByGroupID($gid, "description", $_POST['description']);
    $dirty = true;
  }

  if($dirty) {
    $group = GroupManager::getFromID($gid);
  }
?>

<h1>Group Management</h1>

<hr>

<form method="post">
  <table class="formtable">
    <tbody>
      <tr><td class="center" colspan="2"><h3>Edit Group</h3></td></tr>
      <tr><td>Name:</td><td><input type="text" name="name" id="name" value="<?php echo $group->name; ?>" disabled></td></tr>
      <tr><td>Icon:</td><td><input type="text" name="icon" id="icon" value="<?php echo $group->icon; ?>"></td></tr>
      <tr><td>Color:</td><td><input type="text" name="color" id="color" value="<?php echo $group->color; ?>"></td></tr>
      <tr><td>Description:</td><td><textarea name="desc" id="desc" value="<?php echo $group->description; ?>"></textarea></tr>
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