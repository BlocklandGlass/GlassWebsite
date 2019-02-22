<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
	$_PAGETITLE = "Inspect Update | Blockland Glass";

	use Glass\AddonManager;
	use Glass\BoardManager;
	use Glass\UserManager;
	use Glass\UserLog;

	include(realpath(dirname(__DIR__) . "/../../private/header.php"));

  $addonObject = AddonManager::getFromID($_REQUEST['id']);
  $update = AddonManager::getUpdates($addonObject)[0];
  $manager = UserManager::getFromBLID($addonObject->getManagerBLID());

	$user = UserManager::getCurrent();
	$owner = false;
	if($user->getBlid() == $addonObject->getManagerBLID()) {
		$owner = true;
	} else if(!$user || !$user->inGroup("Reviewer")) {
		header('Location: /addons');
		return;
	}

  // if($addonObject->getDeleted()) {
    // include(__DIR__ . "/../deleted.php");
		// die();
	// } else if($addonObject->isRejected()) {
    // include(__DIR__ . "/../rejected.php");
    // die();
  // }

  //$diffData = $update->getDiff();
?>
<style>
.monospace {
	font-family: "Lucida Console", Monaco, monospace;
	font-size: 0.9em;
}
td {
	padding: 5px;
}
</style>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
  ?>
	<div class="tile">
	  <h2><?php echo '<a href="/addons/addon.php?id=' . $addonObject->getId() . '">' . $addonObject->getName() . '</a>'; ?></h2>
    <p>
      <?php
        if($owner) {
          echo '
          Your update is currently pending review by the add-on moderation team.<br>
          You can monitor the status of your update on your user page.
          ';
        }
      ?>
    </p>
	  <p>
			<span style="font-weight:bold;padding: 2px; border: 1px solid rgb(192,192,255); background: rgb(224,224,255); border-radius: 2px;">v<?php echo $addonObject->getVersion();?></span> -> <span style="font-weight:bold;padding: 2px; border: 1px solid rgb(192,255,192); background: rgb(224,255,224); border-radius: 2px;">v<?php echo $update->getVersion();?></span>
		</p>
	  <hr />
	  <table style="width: 100%">
	    <tbody>
	      <tr>
	        <td style="padding: 10px"><strong>Change-Log</strong></td>
	        <td style="width: 80%">
						<div style="width: 90%; padding: 5px; margin: 0; font-size: 0.9em; background-color: rgba(255,255,255,0.8); max-height: 300px; y-overflow:scroll;" disabled="1"><?php
						$cl = $update->getChangeLog() ?? "";
						if(strlen(trim($cl)) == 0) {
							echo 'No change-log provided.';
						} else {
							echo htmlspecialchars($cl);
						}
						?></div>
					</td>
	      </tr>
	      <tr>
	        <td style="padding-left: 10px; vertical-align: top"><strong>New Files</strong></td>
	        <td style="color: green" class="monospace">
						<?php
							$new = $update->getNewFiles();
							if(isset($new['status']) && $new['status'] == "error") {
								echo "Error opening Zip Archive(s):<br>New File: " . $new['new'] . "<br>Old File: " . $new['old'];
							} else {
								foreach($new as $newFile) {
									echo '+ ' . $newFile . '<br />';
								}
							}
						?>
					</td>
	      </tr>
	      <tr>
					<td style="padding-left: 10px; vertical-align: top"><strong>Removed Files</strong></td>
	        <td style="color: red" class="monospace">
						<?php
							$new = $update->getRemovedFiles();
							if(isset($new['status']) && $new['status'] == "error") {
								echo "Error opening Zip Archive(s):<br>New File: " . $new['new'] . "<br>Old File: " . $new['old'];
							} else {
								foreach($new as $newFile) {
									echo '- ' . $newFile . '<br />';
								}
							}
						?>
					</td>
	      </tr>
			</tbody>
		</table>
	</div>
	<div class="tile">
		<p>
			<a class="btn blue" href="download.php?file=<?php echo $update->getFileBin() ?>">Download</a>
		</p>
	</div>
  <div class="tile">
    <form action="approveUpdate.php" method="post">
      <input type="hidden" name="aid" value="<?php echo $addonObject->getId() ?>" />
      <?php if($owner) { ?>
      <input class="btn yellow" type="submit" name="action" value="Cancel Update" />
      <?php }
      if($user->inGroup("Reviewer")) {
        if($addonObject->getDeleted() || $addonObject->isRejected()) {
      ?>
      <input class="btn red" type="submit" name="action" value="Reject" />
      <?php } else { ?>
      <input class="btn green" type="submit" name="action" value="Approve" />
      <input class="btn red" type="submit" name="action" value="Reject" />
      <?php }
      }
      ?>
    </form>
  </div>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../../private/footer.php")); ?>
