<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
	$_PAGETITLE = "Blockland Glass | Inspect Update";

	use Glass\AddonManager;
	use Glass\BoardManager;
	use Glass\UserManager;
	use Glass\UserLog;




	include(realpath(dirname(__DIR__) . "/../../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));

  $addon = AddonManager::getFromID($_REQUEST['id']);
  $update = AddonManager::getUpdates($addon)[0];
  $manager = UserManager::getFromBLID($addon->getManagerBLID());

	$user = UserManager::getCurrent();
	$owner = false;
	if($user->getBlid() == $addon->getManagerBLID()) {
		$owner = true;
	} else if(!$user || !$user->inGroup("Reviewer")) {
		header('Location: /addons');
		return;
	}

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
	<div class="tile">
	  <h2><?php echo $addon->getName(); ?></h2>
	  <p>
			<span style="font-weight:bold;padding: 2px; border: 1px solid rgb(192,192,255); background: rgb(224,224,255); border-radius: 2px;">v<?php echo $addon->getVersion();?></span> -> <span style="font-weight:bold;padding: 2px; border: 1px solid rgb(192,255,192); background: rgb(224,255,224); border-radius: 2px;">v<?php echo $update->getVersion();?></span>
		</p>
	  <hr />
	  <table style="width: 100%">
	    <tbody>
	      <tr>
	        <td style="padding: 10px"><b>Change-Log</b></td>
	        <td style="width: 80%">
						<div style="width: 90%; padding: 5px; margin: 0; font-size: 0.9em; background-color: rgba(255,255,255,0.8); max-height: 300px; y-overflow:scroll;" disabled="1"><?php
						$cl = $update->getChangeLog() ?? "";
						if(strlen(trim($cl)) == 0) {
							echo 'No Change-Log';
						} else {
							echo htmlspecialchars($cl);
						}
						?></div>
					</td>
	      </tr>
	      <tr>
	        <td style="padding-left: 10px; vertical-align: top"><b>New Files</b></td>
	        <td style="color: green" class="monospace">
						<?php
							$new = $update->getNewFiles();
							if(isset($new['status']) && $new['status'] == "error") {
								echo "Error opening ZipArchive(s):<br>New File: " . $new['new'] . "<br>Old File: " . $new['old'];
							} else {
								foreach($new as $newFile) {
									echo '+ ' . $newFile . '<br />';
								}
							}
						?>
					</td>
	      </tr>
	      <tr>
					<td style="padding-left: 10px; vertical-align: top"><b>Removed Files Files</b></td>
	        <td style="color: red" class="monospace">
						<?php
							$new = $update->getRemovedFiles();
							if(isset($new['status']) && $new['status'] == "error") {
								echo "Error opening ZipArchive(s):<br>New File: " . $new['new'] . "<br>Old File: " . $new['old'];
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
			Work-in-progress code viewer: <a href="code.php?id=<?php echo $addon->getId() ?>">Code Viewer</a>
		</p>
		<hr />
		<b>Changed Files</b>
		<br />
		WIP
	</div>
	<div class="tile">
		<p>
			<a class="btn blue" href="download.php?file=<?php echo $update->getFileBin() ?>">Download</a>
		</p>
	</div>
  <form action="approveUpdate.php" method="post">
		<input type="hidden" name="aid" value="<?php echo $addon->getId() ?>" />
		<?php if($owner) { ?>
		<input type="submit" name="action" value="Cancel Update" />
		<?php }
		if($user->inGroup("Reviewer")) { ?>
		<input type="submit" name="action" value="Approve" />
		<input type="submit" name="action" value="Reject" />
		<?php } ?>
  </form>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../../private/footer.php")); ?>
