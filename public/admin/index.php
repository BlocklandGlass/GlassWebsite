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
  <div class="navcontainer darkgreen">
    <div class="navcontent">
      <ul>
        <li><a class="navbtn" href="?tab=maintenance">Maintenance</a></li>
        <li><a class="navbtn" href="?tab=boards">Boards</a></li>
        <li><a class="navbtn" href="?tab=groups">Groups</a></li>
        <li><a class="navbtn" href="?tab=users">Users</a></li>
        <li><a class="navbtn" href="?tab=rooms">Room Logs</a></li>
      </ul>
    </div>
  </div>
  <div class="tile" style="font-size: 3rem;">
    Control Panel
  </div>
  <div class="tile">
    <?php
      if(!isset($_GET['tab'])) {
        echo "Select a tab above to continue.";
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
