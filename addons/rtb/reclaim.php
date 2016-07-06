<?php
	require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserManager.php"));
  require_once(realpath(dirname(__DIR__) . "/../private/class/RTBAddonManager.php"));

	$_PAGETITLE = "Glass | RTB Reclaim";
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));

  $addonData = RTBAddonManager::getAddonFromId($_GET['id']);

  $ret = null;
  if(isset($_REQUEST['action'])) {
    if($_REQUEST['action'] == "reclaim") {
      $ret = RTBAddonManager::requestReclaim($_REQUEST['id'], $_REQUEST['aid']);
    }
  }
?>
<div class="maincontainer">
  <?php if($ret === true) {
    echo "<b>Your reclaim request has been submitted for approval</b>";
  } else if($ret === false) {
    echo "<b>Your reclaim request has failed</b>";
  }
  ?>
  <h1 style="text-align:center"><img src="/img/rtb_logo.gif"><br /><?php echo $addonData->title ?></h1>
  <hr />
  You can reclaim your old RTB add-ons and have them automatically imported and updated, reclaiming your old users and issuing them the latest version.<br />
  <br />
  Name of current add-on: <input type="text" id="addon" />
  <form method="post" action="">
    <input type="hidden" name="action" value="reclaim" />
    <div id="options">

    </div>
  </form>
</div>
<script type="text/javascript">
$("#addon").keyup(function() {
  $.ajax({
    url: "/ajax/searchAddonNames.php?owner=<?php echo UserManager::getCurrent()->getBlid(); ?>&query=" + $("#addon").val()
  }).done(function(data) {
    res = JSON.parse(data);
    var html = "";
    for(i = 0; i < res.length; i++) {
      html = html + "<b>" + res[i].name + "</b> <button name=\"aid\" type=\"submit\" value=\"" + res[i].id + "\">Reclaim</button><br />";
    }
    $("#options").html(html);
  })
});
</script>
<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
