<?php
	require dirname(__DIR__) . '/../private/autoload.php';

  use Glass\GroupManager;
  use Glass\UserManager;

  if(!isset($_GET['name'])) {
    header('Location: /');
    die();
  }

  $group = GroupManager::getFromName($_GET['name']);

  if(!$group) {
    header('Location: /');
    die();
  }

	$_PAGETITLE = $group->name . " Group | Blockland Glass";
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
?>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
	<div class="tile" style="font-size: 3rem;">
		<?php
      echo "<span style=\"color: #" . $group->color . ";\">" . $group->name . "</span><br>";
      echo "<div style=\"font-size: 1rem;\">" . $group->description . "</div>";
    ?>
	</div>
	<div class="tile">
    <p>The following users are part of this group:</p>
    <ul>
      <?php
        $users = GroupManager::getUsersFromGroupID($group->id);

        foreach($users as $blid) {
          $user = UserManager::getFromBlid($blid);
          $blid = $user->getBLID();
          echo "<li>";
          echo "<a href=\"/user/view.php?blid=" . $blid . "\">" . $user->getName() . "</a>";
          echo "</li>";
        }
      ?>
    </ul>
	</div>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/../private/footer.php"));
?>