<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\AddonManager;

	//info is an array that either has the property "redirect" set, or has the following
	//	message - string
	//	addon - AddonObject
	//	user - UserObject
	$info = include(realpath(dirname(__DIR__) . "/../private/json/manageAddon.php"));

	if(isset($info['redirect'])) {
		header("Location: " . $info['redirect']);
		die();
	}

	$addonObject = AddonManager::getFromId($_GET['id']);

  if($addonObject->getDeleted()) {
    include(__DIR__ . "/../addons/deleted.php");
		die();
	} else if($addonObject->isRejected()) {
    include(__DIR__ . "/../addons/rejected.php");
    die();
  }

	$_PAGETITLE = "Manage Add-On | Blockland Glass";
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
?>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
    include(realpath(dirname(__DIR__) . "/../private/subnavigationbar.php"));
  ?>
	<div class="tile" style="font-size: 3rem;">
		Managing <strong><?php echo htmlspecialchars($addonObject->getName()) ?></strong>
	</div>
	<div class="tile" style="padding: 15px;">
		<?php
      if(!isset($_GET['tab']) || $_GET['tab'] == "") {
        echo "Select a tab above to continue.";
      } else {
        include(realpath(dirname(__FILE__) . "/manage/" . $_GET['tab'] . ".php"));
      }
		?>
	</div>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/../private/footer.php"));
?>
