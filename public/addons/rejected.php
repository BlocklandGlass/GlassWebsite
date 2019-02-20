<?php
	require dirname(__DIR__) . '/../private/autoload.php';
  use Glass\BoardManager;

  if(!isset($boardObject)) {
    $boardObject = BoardManager::getFromId($addonObject->getBoard());
  }

  // $_PAGETITLE = $addonObject->getName() . " - " . $boardObject->getName() . " | Blockland Glass";
  $_PAGETITLE = "Rejected Add-On | Blockland Glass";
  $_PAGEDESCRIPTION = "This add-on was rejected by the add-on moderation team and is no longer available.";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
?>
<div class="maincontainer">
    <?php
      include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
    ?>
    <div class="reviewbody">
      <?php
        echo "<span style=\"font-size: 0.8em;\"><a href=\"/addons/\">Add-Ons</a> >> ";
        // echo "<a href=\"/addons/boards.php\">Boards</a> >> ";
        echo "<a href=\"/addons/boards.php\">Boards</a>";
        echo "</span>";
        // echo "<a href=\"board.php?id=" . $boardObject->getID() . "\">" . utf8_encode($boardObject->getName()) . "</a> >> ";
        // echo "<a href=\"#\">" . htmlspecialchars($addonObject->getName()) . "</a></span>";

        // echo "<h2>" . htmlspecialchars($addonObject->getName()) . "</h2>";
      ?>
      <h2>Rejected Add-On</h2>
      <div style="margin-bottom: 15px; display: inline-block; width: 100%; font-size: 0.8em; background-color: coral; text-align:center; padding: 10px; font-size: 1em">
        <span style="float: left"><img src="/img/icons32/cancel.png"></span>
        <strong>This add-on was rejected by the add-on moderation team and is no longer available.</strong><br />
      </div>
    </div>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/../private/footer.php"));
?>
