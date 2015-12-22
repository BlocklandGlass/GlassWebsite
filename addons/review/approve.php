<?php
require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
var_dump($_POST);
if(isset($_POST['action'])) {
  if($_POST['action'] == "Approve") {
    // approve
    AddonManager::approveAddon($_POST['aid'], $_POST['board']);
    header('Location: /addons/addon.php?id=' . $_POST['aid']);
  } else if($_POST['action'] == "Reject") {
    // reject
  }
}
?>
