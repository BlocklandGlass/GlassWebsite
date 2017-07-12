<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';
	require_once(realpath(dirname(__DIR__) . "/../private/header.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	use Glass\AddonManager;
?>
<div class="maincontainer">
  <div class="tile">
  	<p>
  		<h2>Open Beta</h2>
  		We currently have an open beta pending the release of <b>Glass v4.1.0</b>. This is not a entirely stable release and there will likely be issues. Please be sure to report bugs!
  	</p>
    <?php
    $glassAddonId = 11; //this needs to be changed before going live, or we need a "find addon by name"
    $id = "Open Beta 3";
    $class = "red";
    $ao = AddonManager::getFromId($glassAddonId);
    $version = "4.1.0-beta.3";
    ?>
    <div style="text-align: center">
      <?php
      echo '<a href="/beta/System_BlocklandGlass.zip" class="btn dlbtn ' . $class . '"><b>' . ucfirst($id) . '</b><span style="font-size:9pt"><br />v' . $version . '</span></a>';
      ?>
    </div>
  </div>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
