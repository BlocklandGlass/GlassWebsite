<?php
	require dirname(__DIR__) . '/../private/autoload.php';
  use Glass\UserManager;
  use Glass\GroupManager;
  session_start();
	$user = UserManager::getCurrent();

	$_PAGETITLE = "Blockland Glass | Control Panel";
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));

  if(!GroupManager::getFromName("Administrator")) {
    GroupManager::createDefaultGroups();
  }

	if(!$user || !$user->inGroup("Administrator")) {
    header('Location: /login.php?redirect=' . urlencode("/admin/index.php"));
    return;
  } else {
    $_adminAuthed = true;
  }
?>

<div class="maincontainer">
	<div class="tile" style="width: 185px; float: left;">
		<ul class="sidenav">
			<li><a href="?tab=board">Boards</a></li>
			<li><a href="?tab=user">Users</a></li>
			<li><a href="?tab=groups">Groups</a></li>
			<li><a href="?tab=bans">Bans</a></li>
    </ul>
	</div>
	<div class="tile" style="width: 685px; padding: 15px; float: right;">
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
	include(realpath(dirname(__DIR__) . "/../private/footer.php"));
?>