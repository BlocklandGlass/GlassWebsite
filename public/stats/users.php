<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\GroupManager;
	use Glass\UserManager;
	use Glass\UserLog;
	use Glass\StatUsageManager;

	$_PAGETITLE = "Current Users | Blockland Glass";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));

  $users = UserLog::getRecentlyActive();
?>
<style>
  .listTable {
    margin: 0 auto;
  }

  .maincontainer p {
    text-align: center;
  }
</style>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
  <div class="navcontainer darkgreen">
    <div class="navcontent">
      <ul>
        <li><a class="navbtn" href="/stats/servers.php">Current Servers</a></li>
        <li><a class="navbtn" href="/stats/users.php">Current Users</a></li>
      </ul>
    </div>
  </div>
  <p>This page displays a list of users on Blockland running Blockland Glass right now.<br>
  It is not indicative of the entire Glass or Blockland userbase.</p>
  <?php
  if(sizeof($users) > 0) {
    echo '
    <table class="listTable">
      <thead>
        <tr>
          <td>Username</td>
          <td>BL_ID</td>
          <td>Version</td>
        </tr>
      </thead>
      <tbody>';
          foreach($users as $u) {
            $username = utf8_encode(UserLog::getCurrentUsername($u->blid));
            echo "<tr><td><strong>" . $username . "</strong></td><td>" . $u->blid . "</td><td>" . StatUsageManager::getVersionUsed($u->blid, 11) . "</td></tr>";
          }
      echo '
      </tbody>
    </table>
    ';
  } else {
    echo '<p><strong>No users are currently online running Blockland Glass.</strong></p>';
  }
  ?>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/../private/footer.php"));
?>
