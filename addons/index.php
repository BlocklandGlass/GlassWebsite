<?php
$_PAGETITLE = "Glass | Add-Ons";
require_once(realpath(dirname(__DIR__) . "/private/header.php"));

//LEGACY CODE, EXAMPLE ONLY
require_once('../../html/class/BoardManager.php');
?>

<div class="navcontainer">
  <div class="navcontent">
    <!-- temporary nav -->
	  <b><a href="/">Blockland Glass</a></b>
  </div>
</div>
<div class="maincontainer">
	<table class="addontable">
    <thead>
      <tr>
        <td>i</td>
        <td>Category</td>
        <td>Submissions</td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>image</td>
        <td><a href="#">Weapons</a></td>
        <td>0</td>
      </tr>
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
					<th width=\"670px\" colspan=\"2\">" . $subName . "</th>
				</tr>";
				foreach($sub as $board) {
					echo "<tr><td><a href=\"board.php?id=" . $board->getId() . "\"><image src=\"icon/icons16/" . $board->getImage() . ".png\" />   " . $board->getName() . "</a></td><td style=\"float: right; text-align:center;\">" . $board->getCount() . "</td></tr>";
				}
			}
      ?>
    </tbody>
  </table>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
