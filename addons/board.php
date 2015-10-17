<?php
require_once(realpath(dirname(__DIR__) . "/private/class/BoardManager.php"));
require_once(realpath(dirname(__DIR__) . "/private/class/AddonManager.php"));
require_once(realpath(dirname(__DIR__) . "/private/class/AddonObject.php"));
require_once(realpath(dirname(__DIR__) . "/private/class/UserManager.php"));
require_once(realpath(dirname(__DIR__) . "/private/class/UserHandler.php"));

if(isset($_GET['id'])) {
  try {
    $boardObject = BoardManager::getFromId($_GET['id']);
  } catch(Exception $e) {
    //board doesn't exist
    header('Location: /addons');
    die("board doesnt exist");
  }
} else {
  header('Location: /addons');
  die();
}


$_PAGETITLE = "Glass | " . $boardObject->getName();
require_once(realpath(dirname(__DIR__) . "/private/header.php"));
require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
  <h1 style="text-align:center"><?php echo $boardObject->getName(); ?></h1>
  <a href="/addons">Add-Ons</a> >> <a href="#"><?php echo $boardObject->getName() ?></a>
  <div class="pagenav">
    <?php
    if(isset($_GET['page'])) {
      $page = $_GET['page'];
    } else {
      $page = 1;
    }

    $pages = ceil($boardObject->getCount()/10);
    if($pages >= 7) {
      if($page < 4) {
        for($i = 0; $i < 4; $i++) {
          if($i+1 == $page) {
            echo "[<a href=\"board.php?id=" . $boardObject->getId() . "&page=" . ($i+1) . "\">" . ($i+1) . "</a>] ";
          } else {
            echo "<a href=\"board.php?id=" . $boardObject->getId() . "&page=" . ($i+1) . "\">" . ($i+1) . "</a> ";
          }
        }
        echo " ... ";
        ?>
        <a href="?id=<?php echo $boardObject->getId() . "&page=" . ($pages-1); ?>"><?php echo $pages-1; ?></a>
        <a href="?id=<?php echo $boardObject->getId() . "&page=" . $pages; ?>"><?php echo $pages; ?></a>
        <?php
      } else if($pages-3 < $page) {
        ?>
        <a href="?id=<?php echo $boardObject->getId() . "&page=1"; ?>">1</a>
        <a href="?id=<?php echo $boardObject->getId() . "&page=2"; ?>">2</a>
        <?php
        echo " ... ";
        for($i = $pages-4; $i < $pages; $i++) {
          if($i+1 == $page) {
            echo "[<a href=\"board.php?id=" . $boardObject->getId() . "&page=" . ($i+1) . "\">" . ($i+1) . "</a>] ";
          } else {
            echo "<a href=\"board.php?id=" . $boardObject->getId() . "&page=" . ($i+1) . "\">" . ($i+1) . "</a> ";
          }
        }
      } else {
      ?>
      <a href="?id=<?php echo $boardObject->getId(); ?>&page=1">1</a>
      <a href="?id=<?php echo $boardObject->getId(); ?>&page=2">2</a>
      ...
      <a href="?id=<?php echo $boardObject->getId() . "&page=" . ($page-1); ?>"><?php echo $page-1; ?></a>
      [<a href="?id=<?php echo $boardObject->getId() . "&page=" . $page; ?>"><?php echo $page; ?></a>]
      <a href="?id=<?php echo $boardObject->getId() . "&page=" . ($page+1); ?>"><?php echo $page+1; ?></a>
      ...
      <a href="?id=<?php echo $boardObject->getId() . "&page=" . ($pages-1); ?>"><?php echo $pages-1; ?></a>
      <a href="?id=<?php echo $boardObject->getId() . "&page=" . $pages; ?>"><?php echo $pages; ?></a>
      <?php
      }
    } else {
      for($i = 0; $i < $pages; $i++) {
        if($i+1 == $page) {
          echo "[<a href=\"board.php?id=" . $boardObject->getId() . "&page=" . ($i+1) . "\">" . ($i+1) . "</a>] ";
        } else {
          echo "<a href=\"board.php?id=" . $boardObject->getId() . "&page=" . ($i+1) . "\">" . ($i+1) . "</a> ";
        }
      }
    }
    ?>
  </div>
	<table class="boardtable">
    <tbody>
      <tr class="boardheader">
        <td>Name</td>
        <td>Author(s)</td>
        <td>Rating</td>
        <td>Downloads</td>
      </tr>
      <?php
      $addons = $boardObject->getAddons(($page-1)*10, 10);
			foreach($addons as $addon) {
        ?>
        <tr>
          <td style="width: 33%"><?php echo $addon->getName() ?></td>
          <td style="font-size: 11pt"><?php
            $authors = $addon->getAuthors();
            if(sizeof($authors) == 1) {
              $uo = new UserHandler();
              $uo->initFromId($authors[0]->id);
              echo "<a href=\"#\">" . $uo->getName() . "</a>";
            } else if(sizeof($authors) == 2) {
              $uo = new UserHandler();
              $uo->initFromId($authors[0]->id);
              $uo2 = new UserHandler();
              $uo2->initFromId($authors[1]->id);
              echo "<a href=\"#\">" . $uo->getName() . "</a>";
              echo " and ";
              echo "<a href=\"#\">" . $uo2->getName() . "</a>";
            } else {
              $count = sizeof($authors);
              foreach($authors as $num=>$auth) {
                $uo = new UserHandler();
                $uo->initFromId($auth->id);
                if($count-$num == 1) {
                  echo "and <a href=\"#\">" . $uo->getName() . "</a>";
                } else {
                  echo "<a href=\"#\">" . $uo->getName() . "</a>, ";
                }
              }
            }
            ?></td>
          <td>
            <image src="http://blocklandglass.com/icon/icons16/star.png" />
            <image src="http://blocklandglass.com/icon/icons16/star.png" />
            <image src="http://blocklandglass.com/icon/icons16/star.png" />
            <image src="http://blocklandglass.com/icon/icons16/star.png" />
            <image src="http://blocklandglass.com/icon/icons16/star.png" />
          </td>
          <td><?php echo ($addon->getDownloads(1) + $addon->getDownloads(2)); ?></td>
        </tr>
        <?php
      }
      ?>
      <tr class="boardheader">
        <td colspan="4"></td>
      </tr>
    </tbody>
  </table>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
