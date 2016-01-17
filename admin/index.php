<?php
  include(realpath(dirname(__DIR__) . "/private/class/UserManager.php"));
  include(realpath(dirname(__DIR__) . "/private/class/GroupManager.php"));
  session_start();
	$user = UserManager::getCurrent();

	$_PAGETITLE = "Control Panel";
	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

  if(!GroupManager::getFromName("Administrator")) {
    GroupManager::createDefaultGroups();
  }

	if(!$user || !$user->inGroup("Administrator")) {
    header('Location: /login.php?redirect=' . urlencode("/admin/index.php"));
    return;
  }
?>

<div class="maincontainer">
	<div style="width: 200px; float: left; background-color: #ddd; border-radius: 15px">
		<ul class="sidenav">
			<li><a href="?tab=board">Boards</a></li>
			<li><a href="?tab=tag">Tags</a></li>
			<li><a href="?tab=user">Users</a></li>
			<li><a href="?tab=groups">Groups</a></li>
			<li><a href="?tab=bans">Bans</a></li>
    </ul>
	</div>
	<div style="width: 700px; padding: 15px; float: right;">
		<?php
      if(!isset($_GET['tab']) || !is_file(dirname(__FILE__) . "/tab/" . $_GET['tab'] . ".php")) {
        echo "Invalid tab";
      } else {
			  include(realpath(dirname(__FILE__) . "/tab/" . $_GET['tab'] . ".php"));
      }
		?>
	</div>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/private/footer.php"));
?>
