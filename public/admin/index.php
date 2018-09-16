<?php
	require dirname(__DIR__) . '/../private/autoload.php';
  use Glass\UserManager;
  use Glass\GroupManager;
	
	$user = UserManager::getCurrent();

	$_PAGETITLE = "Control Panel | Blockland Glass";
	include(realpath(dirname(__DIR__) . "/../private/header.php"));

  if(!GroupManager::getFromName("Administrator")) {
    GroupManager::createDefaultGroups(9789); // need to be able to change this during installation
  }

	if(!$user || (!$user->inGroup("Administrator") && !$user->inGroup("Moderator"))) {
    header('Location: /login.php?redirect=' . urlencode("/admin/index.php"));
    return;
  }
?>

<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
	<div class="tile" style="width: 185px; float: left;">
		<ul class="sidenav">
			<li><a href="?tab=boards">Boards</a></li>
      <hr>
			<li><a href="?tab=groups">Groups</a></li>
			<li><a href="?tab=users">Users</a></li>
      <hr>
      <li><a href="?tab=rooms">Room Logs</a></li>
    </ul>
	</div>
	<div class="tile" style="width: 1010px; padding: 15px; float: right;">
		<?php
      if(!isset($_GET['tab'])) {
        echo "<h1>Control Panel</h1>";
        echo "Select a tab on the left to continue.";
      } else {
        $path = dirname(__FILE__) . "/tab/" . $_GET['tab'] . ".php";
        if(is_file($path)) {
          include($path);
        } else {
          echo "Invalid tab.";
        }
      }
		?>
	</div>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/../private/footer.php"));
?>
