<?php
	require dirname(__DIR__) . '/../private/autoload.php';
  $_PAGETITLE = "Add-On Not Found | Blockland Glass";
  $_PAGEDESCRIPTION = "This add-on does not exist or has been removed.";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
?>
<div class="maincontainer">
    <?php
      include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
    ?>
    <div class="navcontainer darkgreen">
      <div class="navcontent">
        <?php
          include(realpath(dirname(__DIR__) . "/../private/searchbar.php"));
        ?>
      </div>
    </div>
    <div class="reviewbody">
      <?php
        echo "<span style=\"font-size: 0.8em;\"><a href=\"/addons/\">Add-Ons</a>";
        echo "<a href=\"#\"></a></span>";
        echo "<h2>Add-On Not Found</h2>";
      ?>
      <div style="display: block; background-color: lightgray; text-align: center; padding: 10px; overflow: hidden;">
        <div style="float: left;"><img src="/img/icons32/box_search.png"></div>
        <strong style="vertical-align: middle;">This add-on does not exist or has been removed by request.</strong>
      </div>
    </div>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/../private/footer.php"));
?>
