<?php
require_once(realpath(dirname(__DIR__) . "/../private/header.php"));
require_once(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
use Glass\UserManager;
use Glass\GroupManager;
use Glass\UserLog;

$cap = 15;
$group = GroupManager::getFromName("Beta");

$user = UserManager::getCurrent();

$closed = true;

if($user === false) {
  if($closed === false) {
    $message = "Please log-in to join!";
  } else {
    $message = "We are not currently looking for testers.";
  }
} else if($user->inGroup("Reviewer") || $user->inGroup("Moderator") || $user->inGroup("Administrator")) {
  $message = "Welcome back, reviewer, moderator, and/or administrator! You can find the latest download <a href=\"/api/beta/System_BlocklandGlass.zip\">here</a>!";
} else if($user->inGroup("Beta")) {
  $message = "Welcome back, tester! You can find the latest download <a href=\"/api/beta/System_BlocklandGlass.zip\">here</a>!";
} else {
  if($closed === true) {
    $message = "We are not currently looking for testers.";
  } else if($group->getMemberCount() >= $cap) {
    $message = "The private testing is currently full. Try again later.";
  } else if(isset($_POST['submit']) && $_POST['submit'] == "Join") {
    $res = GroupManager::addBLIDToGroupID($user->getBlid(), $group->getId());
    if($res) {
      $message = "Welcome to Glass Live private testing! You can download the latest version <a href=\"/api/beta/System_BlocklandGlass.zip\">here</a>";
    } else {
      $message = "Error joining";
    }
  } else {
    $message = "There's currently <b>" . ($cap-$group->getMemberCount()) . '</b> spots left!<br />'
    . '<form action="" method="post" /><input type="submit" value="Join" name="submit"/></form>';
  }
}

?>
<style>
.list td {
  padding: 10px;
}

.list tr:nth-child(2n+1) td {
  background-color: #ddd;
}

.list tr:first-child td {
  background-color: #777;
  color: #fff;
  font-weight: bold;
}

.list tr td:first-child {
  border-radius: 10px 0 0 10px;
}

.list tr td:last-child {
  border-radius: 0 10px 10px 0;
}

.list {
  margin: 0 auto;
}

.maincontainer p {
  text-align: center;
}

form {
  text-align: center;
}

</style>
<div class="maincontainer">
  <?php echo "<p>$message</p>"; ?>
  <hr />
	<table class="list">
    <tbody>
      <tr>
        <td> </td>
        <td>Username</td>
        <td>BL_ID</td>
      </tr>
      <?php

      $members = GroupManager::getMembersByID($group->getId());
      foreach($members as $mem) {
        echo "<tr><td><img src=\"/img/icons32/user_orange.png\" /></td>";
        echo "<td><b>" . UserLog::getCurrentUsername($mem) . "</b></td>";
        echo "<td>" . $mem . "</td></tr>";
      }

      ?>
    </tbody>
  </table>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
