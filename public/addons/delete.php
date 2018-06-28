<?php
	require dirname(__DIR__) . '/../private/autoload.php';
  use Glass\UserManager;
  use Glass\AddonManager;

	$id = $_REQUEST['id'] ?? false;
	$user = UserManager::getCurrent();
	$addonObject = AddonManager::getFromId($id);
	if($user === false || $addonObject === false || ($addonObject->getManagerBLID() !== $user->getBlid() && !$user->inGroup("Administrator"))) {
		header("Location: /login.php");
		die();
	}

  if($addonObject->getDeleted()) {
    include 'deleted.php';
		die();
	} else if($addonObject->isRejected()) {
    include 'rejected.php';
    die();
  }

	$_PAGETITLE = "Blockland Glass | Delete Add-On";
	include(realpath(dirname(__DIR__) . "/../private/header.php"));

  $confirm = $_REQUEST['confirm'] ?? false;
  if($confirm !== false && $_SESSION['deleteConfirm'] ?? false === $id) {
    if(AddonManager::deleteAddon($addonObject)) {
      ?>
      <div class="maincontainer">
        <div class="tile">
          <div style="text-align:center;">
            <h1 style="color:red; padding: 10px; margin: 0;">"<?php echo htmlspecialchars($addonObject->getName()) ?>" has been deleted</h1><br>
            <a href="/">Back to home.</a>
          </div>
        </div>
      </div>
      <?php
    } else {
      ?>
      <div class="maincontainer">
        <div class="tile">
          <h1>There was an error deleting <?php echo htmlspecialchars($addonObject->getName()) ?></h1>
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
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
  <div class="tile">
    <h1>Delete <?php echo htmlspecialchars($addonObject->getName()) ?></h1>
    <p>
      Are you sure you want to delete <a href="/addons/addon.php?id=<?php echo $id; ?>"><?php echo htmlspecialchars($addonObject->getName()); ?></a>?<br>
      <span style="color:red">This action cannot be undone.</span>
    </p>
    <div style="text-align:center">
      <a class="btn red" style="font-size: 1em" href="?id=<?php echo $id ?>&confirm=1">Permanently Delete "<?php echo htmlspecialchars($addonObject->getName()) ?>"</a>
    </div>
  </div>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/../private/footer.php"));
?>
