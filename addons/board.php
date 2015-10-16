<?php
require_once(realpath(dirname(__DIR__) . "/private/class/BoardManager.php"));

if(isset($_GET['id'])) {
  try {
    $boardObject = BoardManager::getFromId($_GET['id']);
  } catch(Exception $e) {
    //board doesn't exist
    //header('Location: /addons');
    die("board doesnt exist");
  }
} else {
  header('Location: /addons');
  die();
}

$_PAGETITLE = "Glass | Board Name";
require_once(realpath(dirname(__DIR__) . "/private/header.php"));
require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
  <a href="/addons">Add-Ons</a> >> <a href="#"><?php echo $boardObject->getName() ?></a>
  <div class="pagenav">
    <a href="#">1</a>
    <a href="#">2</a>
    ...
    <a href="#">5</a>
    <a href="#">6</a>
    <a href="#">7</a>
    ...
    <a href="#">11</a>
    <a href="#">12</a>
  </div>
	<table class="boardtable">
    <tbody>
      <tr class="boardheader">
        <td colspan="4"><?php echo $boardObject->getName(); ?></td>
      </tr>
      <tr class="boardheader">
        <td>Name</td>
        <td>Author(s)</td>
        <td>Rating</td>
        <td>Downloads</td>
      </tr>
      <tr>
        <td style="width: 50%">My Add-On</td>
        <td><a href="#">Jincux</a> and <a href="#">Nexus</a></td>
        <td>
          <image src="http://blocklandglass.com/icon/icons16/star.png" />
          <image src="http://blocklandglass.com/icon/icons16/star.png" />
          <image src="http://blocklandglass.com/icon/icons16/star.png" />
          <image src="http://blocklandglass.com/icon/icons16/star.png" />
          <image src="http://blocklandglass.com/icon/icons16/star.png" />
        </td>
        <td>413</td>
      </tr>
      <?php
      /*
      $addons = $boardObject->getAddons();
			foreach($addons as $addon) {

      }
      */
      ?>
      <tr class="boardheader">
        <td colspan="4"></td>
      </tr>
    </tbody>
  </table>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
