<h2>Group Management</h2>

<?php
  use Glass\UserManager;
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

  if(isset($_POST['icon']) && $group->icon != $_POST['icon']) {
    if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
      throw new \Exception("Cross site request forgery attempt blocked");
    }

    GroupManager::editGroupByGroupID($gid, "icon", $_POST['icon']);
    $dirty = true;
  }

  if(isset($_POST['color']) && $group->color != $_POST['color']) {
    if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
      throw new \Exception("Cross site request forgery attempt blocked");
    }

    GroupManager::editGroupByGroupID($gid, "color", $_POST['color']);
    $dirty = true;
  }

  if(isset($_POST['desc']) && $group->description != $_POST['desc']) {
    if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
      throw new \Exception("Cross site request forgery attempt blocked");
    }

    GroupManager::editGroupByGroupID($gid, "desc", $_POST['desc']);
    $dirty = true;
  }

  if($dirty) {
    $group = GroupManager::getFromID($gid);
  }
?>

<h2><?php echo "<span style=\"color: #" . $group->color . ";\">" . $group->name . "</span>"; ?></h2>

<table style="width: 100%" class="listTable">
  <thead>
    <th>User</th>
    <th>BL_ID</th>
    <th>Options</th>
  </thead>
  <tbody>
    <?php
      $users = GroupManager::getUsersFromGroupID($gid);

      foreach($users as $blid) {
        $user = UserManager::getFromBlid($blid);
        $blid = $user->getBLID();
        echo "<tr>";
        echo "<td><a href=\"/user/view.php?blid=" . $blid . "\">" . $user->getName() . "</a></td>";
        echo "<td>" . $user->getBLID() . "</td>";
        echo "<td><a href=\"#\">Make Leader</a> | <a href=\"#\">Remove</a></td>";
        echo "</tr>";
      }
    ?>
  </tbody>
</table>

<hr>

<form method="post">
  <table class="formtable">
    <tbody>
      <tr><td class="center" colspan="2"><h3>Edit Group</h3></td></tr>
      <tr><td>Name:</td><td><input type="text" name="name" id="name" value="<?php echo $group->name; ?>" disabled></td></tr>
      <tr><td>Icon:</td><td><input type="text" name="icon" id="icon" value="<?php echo $group->icon; ?>"></td></tr>
      <tr><td>Color:</td><td><input style="background-color: #<?php echo $group->color; ?>" type="text" name="color" id="color" value="<?php echo $group->color; ?>"></td></tr>
      <tr><td>Description:</td><td><textarea name="desc" id="desc"><?php echo $group->description; ?></textarea></tr>
      <tr><td class="center" colspan="2"><input class="yellow" type="submit"></td></tr>
    </tbody>
  </table>
  <input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
</form>