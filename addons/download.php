<?php
require_once(realpath(dirname(__DIR__) . "/private/class/AddonManager.php"));
require_once(realpath(dirname(__DIR__) . "/private/class/StatManager.php"));

$id = $_REQUEST['id'];
$addonObject = AddonManager::getFromId($id);
if($addonObject) {
  StatManager::downloadAddon($addonObject);
  $branchId["stable"] = 1;
  $branchId["unstable"] = 2;
  $branchId["development"] = 3;
  if(isset($_REQUEST['branch'])) {
    $bid = $branchId[$branch];
  } else {
    $bid = 1;
  }
  header('Location: http://cdn.blocklandglass.com/builds/6.bls');
  //header('Location: http://cdn.blocklandglass.com/addons/' . $id . "_" . $bid . ".zip");
} else {
  header('Status: 404');
  header('Location: /error.php');
}
?>
