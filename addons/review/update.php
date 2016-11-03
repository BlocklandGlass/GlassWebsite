<?php
	$_PAGETITLE = "Blockland Glass | Inspect Update";

	require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/BoardManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserLog.php"));




	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));

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
table {
	width: 100%
}

td {
	vertical-align: top;
	font-size: 1em;
}

.diff {
  width: 100%;
  border: 1px solid rgba(245,245,245, 0.2);
}

.diff td {
  padding-left: 2px;
  width: 50%;
  font-size: 0.8em;
  vertical-align : top;
  white-space    : pre;
  white-space    : pre-wrap;
  font-family    : monospace;
	text-align: left;
}

.diff td:first-child {
  border-right: 1px solid rgba(140, 140, 140, 0.5);
}

.diffDeleted {
  cursor: default;
  border: 1px solid rgb(255,192,192);
  background: rgba(255,224,224,0.4);
}

.diffInserted {
  cursor: default;
  border: 1px solid rgb(192,255,192);
  background: rgba(224,255,224,0.4);
}

.diffBlank {
  cursor: default;
  /*border: 1px solid rgb(240, 240, 240);*/
}

.diffUnmodified {
  cursor: default;
  /*background: rgba(250,250,250,0.4);*/
}

.diffUnmodified span {
  display: inline-block;
  width:100%;
}

.scroll {
	display: block;
	height: 300px;
	overflow-y: scroll;
	background-color: #eee;
	border: 1px solid #bbb;
	padding: 5px;
	margin: 5px;
}

.source {
	display: none;
	height: 0;
	background-color: #333;
	padding: 10px;
	color: #fff;
}

</style>
<script type="text/javascript">
var tog = [];
function showChanges(id) {
	if(!tog[id]) {
		document.getElementById("source" + id).style.display = "block";
		document.getElementById("source" + id).style.height = "auto";
		tog[id] = true;
	} else {
		document.getElementById("source" + id).style.display = "none";
		document.getElementById("source" + id).style.height = "0";
		tog[id] = false;
	}
}
</script>
<!-- <div id="fileCompare" class="overlay">


  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
	<br />

  <div class="overlay-content">
    <table class="file-compare">
			<tr><td colspan="2"><?php //foreach($diffData['changes'] as $file=>$table) { echo $file . "<br />" . $table . "<hr />";} ?></td></tr>
		</table>
  </div>

</div> -->
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
        <td class="scroll"><?php //foreach($diffData['removed'] as $file) { echo $file . '<br />';} ?></td>
      </tr>
      <tr>
        <td style="padding: 10px;"><b>New Files</b></td>
        <td class="scroll"><?php //foreach($diffData['added'] as $file) { echo $file . '<br />';} ?></td>
      </tr>
		</tbody>
	</table>
	<table>
		<tbody>
      <tr>
        <td style="padding: 10px;"><b>Changed Files</b></td>
        <td></td>
      </tr>
      <tr>
        <td colspan="2" style="font-size:0.7em">
					<?php
					$idx = 0;
					/*foreach($diffData['changes'] as $file=>$table) {
						echo $file . " <button onclick=\"javascript:showChanges($idx);\">View Changes</button>";
						echo "<br /><div class=\"source\" id=\"source$idx\">" . $table . "</div>";
						$idx++;
					}*/
					?>
				</td>
      </tr>
    </tbody>
  </table>
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

	include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
