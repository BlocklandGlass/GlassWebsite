<?php
$_PAGETITLE = "Glass | Add-Ons";
require_once(realpath(dirname(__DIR__) . "/private/header.php"));

require_once(realpath(dirname(__DIR__) . "/private/class/BoardManager.php"));

//LEGACY CODE, EXAMPLE ONLY
//require_once('../../html/class/BoardManager.php');
?>

<div class="navcontainer">
  <div class="navcontent">
    <!-- temporary nav -->
	  <b><a href="/">Blockland Glass</a></b>
  </div>
</div>
<div class="maincontainer">
	<table class="addontable">
    <tbody>
      <?php
      $boards = BoardManager::getAllBoards();
			usort($boards, function($a, $b) {
			    return strcmp($a->getName(), $b->getName());
			});
			$subcat = array();
			foreach($boards as $board) {
				$subcat[$board->getSubCategory()][] = $board;
			}
      foreach($subcat as $subName=>$sub) {
				echo "<tr>
					<th colspan=\"3\"><b>$subName</b></th>
				</tr>";
				foreach($sub as $board) {
					echo "<tr><td><image src=\"http://blocklandglass.com/icon/icons32/" . $board->getImage() . ".png\" /></td><td><a href=\"http://blocklandglass.com/board.php?id=" . $board->getId() . "\">   " . $board->getName() . "</a></td><td>" . $board->getCount() . "</td></tr>";
				}
			}
      ?>
    </tbody>
  </table>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
