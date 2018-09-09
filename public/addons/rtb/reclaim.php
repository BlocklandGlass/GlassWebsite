<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
	use Glass\AddonManager;
	use Glass\UserManager;
  use Glass\RTBAddonManager;

	$_PAGETITLE = "RTB Reclaim | Blockland Glass";
	include(realpath(dirname(__DIR__) . "/../../private/header.php"));

  $user = UserManager::getCurrent();
  if($user == false) {
    header('Location: /login.php');
    die();
  }

  $addonData = RTBAddonManager::getAddonFromId($_GET['id']);

  if($addonData->glass_id != 0 || $addonData->approved == 1) {
    die('Add-on already imported.');
  }

  $ret = null;
  if(isset($_REQUEST['action'])) {
    if($_REQUEST['action'] == "reclaim") {
      $ret = RTBAddonManager::requestReclaim($_REQUEST['id'], $_REQUEST['aid']);
    }
  }
?>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
  ?>
	<div class="tile">
	  <?php if($ret === true) {
	    echo "<strong>Your reclaim request has been submitted for approval.</strong>";
	  } else if($ret === false) {
	    echo "<strong>Your reclaim request has failed.</strong>";
	  }
	  ?>
	  <h1 style="text-align:center"><img src="/img/rtb_logo.gif"><br /><?php echo $addonData->title ?></h1>
	  <hr />
	  You can reclaim your old RTB add-ons and have them automatically imported and updated, reclaiming your old users and issuing them the latest version.<br />
	  <br />
	  Name of Current Add-On on Glass: <input type="text" id="addon" />
	  <form method="post" action="">
	    <input type="hidden" name="action" value="reclaim" />
	    <div id="options">

	    </div>
	  </form>
	</div>
</div>
<script type="text/javascript">
$("#addon").keyup(function() {
  $.ajax({
    url: "/ajax/searchAddonNames.php?owner=<?php echo UserManager::getCurrent()->getBlid(); ?>&query=" + $("#addon").val()
  }).done(function(data) {
    res = JSON.parse(data);
    var html = "";
    for(i = 0; i < res.length; i++) {
      html = html + "<strong>" + res[i].name + "</strong> <button name=\"aid\" type=\"submit\" value=\"" + res[i].id + "\">Reclaim</button><br />";
    }
    $("#options").html(html);
  })
});
</script>
<?php include(realpath(dirname(__DIR__) . "/../../private/footer.php")); ?>
