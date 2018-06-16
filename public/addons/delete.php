<?php
	require dirname(__DIR__) . '/../private/autoload.php';
  use Glass\UserManager;
  use Glass\AddonManager;

	$id = $_REQUEST['id'] ?? false;
	$user = UserManager::getCurrent();
	$addon = AddonManager::getFromId($id);
	if($user === false || $addon === false || ($addon->getManagerBLID() !== $user->getBlid() && !$user->inGroup("Administrator"))) {
		header("Location: /login.php");
		die();
	}
	$_PAGETITLE = "Blockland Glass | Delete Add-On";
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));

  $confirm = $_REQUEST['confirm'] ?? false;
  if($confirm !== false && $_SESSION['deleteConfirm'] ?? false === $id) {
    if(AddonManager::deleteAddon($addon)) {
      ?>
      <div class="maincontainer">
        <div class="tile">
          <h1 style="color:red; text-align:center; padding: 10px; margin: 0;">"<?php echo htmlspecialchars($addon->getName()) ?>" has been deleted</h1>
        </div>
      </div>
      <?php
    } else {
      ?>
      <div class="maincontainer">
        <div class="tile">
          <h1>There was an error deleting <?php echo htmlspecialchars($addon->getName()) ?></h1>
        </div>
      </div>
      <?php
    }
    die();
  } else {
    $_SESSION['deleteConfirm'] = $id;
  }
?>

<div class="maincontainer">
  <div class="tile">
    <h1>Delete <?php echo htmlspecialchars($addon->getName()) ?>?</h1>
    <p>
      Are you sure you want to delete <a href="/addons/addon.php?id=<?php echo $id; ?>"><?php echo htmlspecialchars($addon->getName()); ?></a>?
      <span style="color:red">This action cannot be undone.</span>
    </p>
    <div style="text-align:center">
      <a class="btn red" style="font-size: 1em" href="?id=<?php echo $id ?>&confirm=1">Permanently Delete "<?php echo htmlspecialchars($addon->getName()) ?>"</a>
    </div>
  </div>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/../private/footer.php"));
?>
