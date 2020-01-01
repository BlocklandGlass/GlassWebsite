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

  if(isset($_POST['form']) && $_POST['form'] == "edituser") {
    if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
      throw new \Exception("Cross site request forgery attempt blocked");
    }

    if(isset($_POST['blid']) && isset($_POST['action'])) {
      $blid = $_POST['blid'];
      $action = $_POST['action'];
      
      if($action == "Make Leader") {
        // do nothing
      } else if($action == "Remove") {
        if($group->leader == $blid) {
          die('The active leader of a group cannot be removed.');
        }

        GroupManager::removeBLIDFromGroupID($blid, $gid);
        $dirty = true;
      }
    }
  }

  if(isset($_POST['form']) && $_POST['form'] == "editgroup") {
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
  }

  if(isset($_POST['form']) && $_POST['form'] == "adduser") {
    if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
      throw new \Exception("Cross site request forgery attempt blocked");
    }

    if(isset($_POST['blid'])) {
      GroupManager::addBLIDToGroupID($_POST['blid'], $gid);
      $dirty = true;
    }
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
        echo "<td>" . $blid . "</td>";
        echo "<td>";
        if($group->leader != $blid) {
          echo "<form method=\"post\">";
          echo "<input class=\"btn small blue\" type=\"submit\" name=\"action\" value=\"Make Leader\" disabled>";
          echo "<input class=\"btn small red\" type=\"submit\" name=\"action\" value=\"Remove\">";
          echo "<input type=\"hidden\" name=\"form\" value=\"edituser\">";
          echo "<input type=\"hidden\" name=\"blid\" value=\"" . $blid . "\">";
          echo "<input type=\"hidden\" name=\"csrftoken\" value=\"" . $_SESSION['csrftoken'] . "\">";
          echo "</form>";
        }
        echo "</td>";
        echo "</tr>";
      }
    ?>
    <tr>
      <td></td>
      <td></td>
      <td>
        <form method="post">
          <input type="text" name="blid" value="" placeholder="BL_ID" style="width: 120px; display: inline-block; margin: 0">
          <input class="btn small green" type="submit" value="Add">
          <input type="hidden" name="form" value="adduser">
          <input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
        </form>
      </td>
    </tr>
  </tbody>
</table>

<hr>

<form method="post">
  <table class="formtable">
    <tbody>
      <tr><td class="center" colspan="2"><h3>Edit Group</h3></td></tr>
      <tr><td>Name:</td><td><input type="text" name="name" value="<?php echo $group->name; ?>" disabled></td></tr>
      <tr><td>Icon:</td><td><input type="text" name="icon" value="<?php echo $group->icon; ?>"></td></tr>
      <tr><td>Color:</td><td><input style="background-color: #<?php echo $group->color; ?>" type="text" name="color" value="<?php echo $group->color; ?>"></td></tr>
      <tr><td>Description:</td><td><textarea name="desc"><?php echo $group->description; ?></textarea></tr>
      <tr><td class="center" colspan="2"><input class="btn yellow" type="submit" value="Edit"></td></tr>
    </tbody>
  </table>
  <input type="hidden" name="form" value="editgroup">
  <input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
</form>