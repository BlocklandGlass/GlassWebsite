<?php
	$_PAGETITLE = "Glass | Inspect Update";

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

  $diffData = $update->getDiff();
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

.overlay {
	visibility: hidden;
  display: block;
	margin: 20px;
	padding: 20px;
	border-radius: 15px;

  position: absolute; /* Stay in place */
  z-index: 1; /* Sit on top */
  left: 0;
  top: 0;
  background-color: rgb(0,0,0); /* Black fallback color */
  background-color: rgba(0,0,0, 0); /* Black w/opacity */
  transition: 0.5s; /* 0.5 second transition effect to slide in or slide down the overlay (height or width, depending on reveal) */
}

/* Position the content inside the overlay */
.overlay-content {
	color: #fff;
  position: relative;
  text-align: center; /* Centered text/links */
  margin-top: 30px; /* 30px top margin to avoid conflict with the close button on smaller screens */
}

/* The navigation links inside the overlay */
.overlay a {
    padding: 8px;
    text-decoration: none;
    font-size: 36px;
    color: #818181;
    display: block; /* Display block instead of inline */
    transition: 0.3s; /* Transition effects on hover (color) */
}

/* When you mouse over the navigation links, change their color */
.overlay a:hover, .overlay a:focus {
    color: #f1f1f1;
}

/* Position the close button (top right corner) */
.closebtn {
    position: absolute;
    top: 0;
    right: 45px;
    font-size: 60px !important; /* Override the font-size specified earlier (36px) for all navigation links */
}

/* When the height of the screen is less than 450 pixels, change the font-size of the links and position the close button again, so they don't overlap */
@media screen and (max-height: 450px) {
    .overlay a {font-size: 20px}
    .closebtn {
        font-size: 40px !important;
        top: 15px;
        right: 35px;
    }
}

</style>
<script type="text/javascript">
/* Open when someone clicks on the span element */
function openNav() {
    document.getElementById("fileCompare").style.visibility = "visible";
    document.getElementById("fileCompare").style.backgroundColor = "rgba(0,0,0, 0.8)";
		window.scrollTo(0, 0);
}

/* Close when someone clicks on the "x" symbol inside the overlay */
function closeNav() {
    document.getElementById("fileCompare").style.backgroundColor = "rgba(0,0,0, 0)";
		document.getElementById("fileCompare").style.visibility = "hidden";
}
</script>
<div id="fileCompare" class="overlay">

  <!-- Button to close the overlay navigation -->
  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>

  <!-- Overlay content -->
  <div class="overlay-content">
    <table class="file-compare">
			<tr><td colspan="2"><?php foreach($diffData['changes'] as $file=>$table) { echo $file . "<br />" . $table . "<hr />";} ?></td></tr>
		</table>
  </div>

</div>
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
		</tbody>
	</table>
	<table>
		<tbody>
      <tr>
        <td style="padding: 10px;"><b>Changed Files</b></td>
        <td><button onclick="openNav()">View Fullscreen</button></td>
      </tr>
      <tr>
        <td colspan="2" style="font-size:0.7em"><?php foreach($diffData['changes'] as $file=>$table) { echo $file . "<br />" . $table . "<hr />";} ?></td>
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
