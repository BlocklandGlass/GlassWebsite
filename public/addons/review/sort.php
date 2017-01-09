<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
  use Glass\AddonManager;
	use Glass\BoardManager;

  $bid = $_POST['board'] ?? false;
  if($bid !== false) {
    foreach($_POST as $key=>$val) {
      if(strpos($key, "addon_") === 0) {
        $aid = substr($key, 6);
        if(!isset($val)) {
          $val = true;
        }

        AddonManager::moveBoard($aid, $bid);
      }
    }
  }

  $oldList = [];
  $oldBoards = BoardManager::getGroup("");
  $oldBoards = BoardManager::getAllBoards();
  foreach($oldBoards as $board) {
    $addons = AddonManager::getFromBoardId($board->getId(), false, false);
    $oldList = array_merge($oldList, $addons);
  }
?>
<html>
  <head>
    <style>
      tr:first-of-type > td {
        font-weight: bold;
        min-width: 100px;
      }

      td {
        text-align: center;
      }
    </style>
  </head>
  <body>
    <h2>Add-On Recategorize</h2>
    <p>
      I'm not going to make this look pretty. Sorry.
    </p>
    <hr />
    <form action="sort.php" method="post">
      <table>
        <tr>
          <td>Checkbox</td>
          <td>Title</td>
          <td>Old Category</td>
        </tr>
        <?php
          foreach($oldList as $old) {
            $old = AddonManager::getFromId($old);
            echo '<tr>';
            echo '<td><input type="checkbox" name="addon_' . $old->getId() . '"/></td>';
            echo '<td>' . $old->getName() . '</td>';
            echo '<td>' . BoardManager::getFromId($old->getBoard())->getName() . '</td>';
            echo '</tr>';
          }
         ?>
      </table>
      <div>
        <p>
          Move selected to:
          <select name="board">
            <?php
              $boards = BoardManager::getAllBoards();
              foreach($boards as $board) {
                if($board->getGroup() == "")
                  continue;

                echo '<option value="' . $board->getId() . '">' . $board->getName() . '</option>';
              }
            ?>
          </select>
        </p>
        <input type="submit" />
      </div>
  </body>
</html>
