<?php
use Glass\GroupManager;

$group = GroupManager::getFromName("Beta");
if($group === false) {
  GroupManager::createGroupWithLeaderBLID("Beta", "Closed beta", "ff0000", "gear", 9789);
  $group = GroupManager::getFromName("Beta");
}

if(isset($_POST['action'])) {
  if($_POST['action'] == "add") {
    $res = GroupManager::addBLIDToGroupID($_POST['blid'], $group->getId());
    if($res) {
      echo "Added " . $_POST['blid'] . "<hr />";
    } else {
      echo "Failed to add " . $_POST['blid'] . "<hr />";
    }
  }
}

$members = GroupManager::getMembersByID($group->getId());
foreach($members as $mem) {
  echo $mem . "<br />";
}
?>
<hr />
<form action="" method="post">
  <input type="hidden" name="action" value="add" />
  <input type="text" name="blid" />
  <input type="submit" value="Add" />
</form>
