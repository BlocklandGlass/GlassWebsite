<?php
require_once(realpath(dirname(__DIR__) . "/private/header.php"));
require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
require_once(realpath(dirname(__DIR__) . "/private/class/UserManager.php"));
$failed = false;
try {
  $userObject = UserManager::getFromId($_GET['id']);
} catch (Exception $e) {
  $failed = true;
}
?>
<div class="maincontainer">
  <?php
  if($failed) {
  ?>
  <h3>Uh-Oh</h3>
  <p>Whoever you're looking for either never existed or deleted their account.</p>
  <?php
  } else {
  ?>
	<h3><?php echo $userObject->getName(); ?></h3>
  <p><b>Last Seen:</b>
  <br /><b>BL ID:</b> <?php echo $userObject->getBLID(); ?>
  <br /><b>Add-Ons:</b>
</p>
  <?php } ?>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
