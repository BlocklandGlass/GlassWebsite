<?php
	$_PAGETITLE = "Glass | Inspect Update";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/BoardManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserLog.php"));

	$user = UserManager::getCurrent();
	if(!$user || !$user->inGroup("Reviewer")) {
    header('Location: /addons');
    return;
  }

  $addon = AddonManager::getFromID($_REQUEST['id']);
  $update = AddonManager::getUpdates($addon)[0];
  $manager = UserManager::getFromBLID($addon->getManagerBLID());
  $diffData = $update->getDiff();
?>
<style>
.diff {
  width: 960px;
  border: 1px solid rgb(245,245,245);
}

.diff td {
  padding-left: 2px;
  width: 50%;
  font-size: 0.6em;
  vertical-align : top;
  white-space    : pre;
  white-space    : pre-wrap;
  font-family    : monospace;
}

.diff td:first-child {
  border-right: 1px solid rgb(240, 240, 240);
}

.diffDeleted {
  cursor: default;
  border: 1px solid rgb(255,192,192);
  background: rgb(255,224,224);
}

.diffInserted {
  cursor: default;
  border: 1px solid rgb(192,255,192);
  background: rgb(224,255,224);
}

.diffBlank {
  cursor: default;
  border: 1px solid rgb(240, 240, 240);
}

.diffUnmodified {
  cursor: default;
  color: rgb(120, 120, 120);
  background: rgb(250,250,250);
}

.diffUnmodified span {
  display: inline-block;
  width:100%;
}

.diffUnmodified span:hover {
  color: #000;
  background: #fff;
}

</style>
<div class="maincontainer">
  <h2><?php echo $addon->getName(); ?></h2>
  <p><span style="font-weight:bold;padding: 2px; border: 1px solid rgb(192,192,255); background: rgb(224,224,255); border-radius: 2px;">v<?php echo $addon->getVersion();?></span> -> <span style="font-weight:bold;padding: 2px; border: 1px solid rgb(192,255,192); background: rgb(224,255,224); border-radius: 2px;">v<?php echo $update->getVersion();?></span></p>
  <hr />
  <table>
    <tbody>
      <tr>
        <td style="padding: 10px;"><b>Change-Log</b></td>
        <td><?php echo $update->getChangeLog(); ?></td>
      </tr>
      <tr>
        <td style="padding: 10px;"><b>Removed Files</b></td>
        <td><?php foreach($diffData['removed'] as $file) { echo $file . '<br />';} ?></td>
      </tr>
      <tr>
        <td style="padding: 10px;"><b>New Files</b></td>
        <td><?php foreach($diffData['added'] as $file) { echo $file . '<br />';} ?></td>
      </tr>
      <tr>
        <td style="padding: 10px;"><b>Changed Files</b></td>
        <td></td>
      </tr>
      <tr>
        <td colspan="2"><?php foreach($diffData['changes'] as $file=>$table) { echo $file . "<br />" . $table . "<hr />";} ?></td>
      </tr>
    </tbody>
  </table>
  <form action="approveUpdate.php" method="post">
		<input type="hidden" name="aid" value="<?php echo $addon->getId() ?>" />
		<input type="submit" name="action" value="Approve" />
		<input type="submit" name="action" value="Reject" />
  </form>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
