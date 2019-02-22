<?php
  require dirname(__DIR__) . '/../private/autoload.php';

  use Glass\BoardManager;
  use Glass\AddonManager;
  use Glass\AddonObject;
  use Glass\BugManager;
  use Glass\CommentManager;
  use Glass\ScreenshotManager;
  use Glass\DependencyManager;
  use Glass\UserManager;
  use Glass\UserLog;
  require_once(realpath(dirname(__DIR__) . "/../private/lib/Parsedown.php"));

  //to do: use ajax/json to build data for page
  //this php file should just format the data nicely
  if(isset($_GET['id'])) {
    $addonObject = AddonManager::getFromId($_GET['id'] + 0);
    if($addonObject) {
      $boardObject = BoardManager::getFromID($addonObject->getBoard());
    } else {
      include 'notfound.php';
      die();
    }
  } else {
    header('Location: /addons');
    die();
  }

  $current = UserManager::getCurrent();

  if(!$current || ($current && !$current->inGroup("Administrator"))) {
    if($addonObject->getDeleted()) {
      include 'deleted.php';
      die();
    }
  }

  if(!$current || (!$current->inGroup("Reviewer") && $addonObject->getManagerBLID() != $current->getBLID())) {
    if($addonObject->isRejected()) {
      include 'rejected.php';
      die();
    } else if(!$addonObject->getApproved()) {
      include 'unapproved.php';
      die();
    }
  }

  if(isset($_POST['comment']) && strlen(trim($_POST['comment'])) > 0) {
    CommentManager::submitComment($addonObject->getId(), UserManager::getCurrent()->getBLID(), $_POST['comment']);
  }

  $_PAGETITLE = $addonObject->getName() . " - " . $boardObject->getName() . " | Blockland Glass";
  $_PAGEDESCRIPTION = $addonObject->getDescription();

  include(realpath(dirname(__DIR__) . "/../private/header.php"));
?>
<style>
  .addon-info {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: stretch;
  }

  .addon-info-main, .add-info-side {
    margin: 0;
    padding: 0;
  }

  .addon-info-main {
    order: 1;
    min-width: 400px;
    min-width: 60%;
    width: 60%;
    flex-grow: 3;
    flex-shrink: 3;

    display: flex;
    flex-direction: column;
    align-items: stretch;
  }

  .addon-info-main > div:nth-of-type(2) {
    flex-grow: 1;
  }

  .addon-info-side {
    order: 3;
    width: 250px;
    flex-grow: 1;
    flex-shrink: 1;

    overflow: hidden;
  }

  .addon-info-side > div {
    overflow: hidden;
    word-wrap: break-word;
  }

  .addon-info h3 {
    margin: 0px 5px 10px 5px;
  }

  .addon-info .tile {
    padding: 15px;
  }

  .image-preview {
    display: inline-block;
    text-align: center;

    -webkit-transition: all 0.5s ease;
    -moz-transition: all 0.5s ease;
    -o-transition: all 0.5s ease;
    transition: all 0.5s ease;

    cursor: pointer;
  }

  .image-preview:hover {
    background-color: #ffffff;
  }

  .image-preview img {
    -webkit-transition: all 0.5s ease;
    -moz-transition: all 0.5s ease;
    -o-transition: all 0.5s ease;
    transition: all 0.5s ease;
  }

  .image-preview:hover img {
    opacity: 0.5;
  }

  #image-viewer {
    -webkit-transition: all 0.5s ease;
    -moz-transition: all 0.5s ease;
    -o-transition: all 0.5s ease;
    transition: all 0.5s ease;

    display: none;

    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
    z-index: 9999;
  }

  #image-view {
    position: absolute;
    left: 50%;
    top: 50%;

    transform: translate(-50%, -50%);

    max-width: 90%;
    max-height: 90%;
  }
</style>
<script type="text/javascript">
  $(function(){
    $(".image-preview").click(function() {
      var id = $(this).attr('ssid');
      $("#image-viewer").show();
      $("#image-view").attr('src', 'http://cdn.blocklandglass.com/screenshots/' + id);
    });

    $("#image-viewer").click(function(evt) {
      if(evt.target == this || evt.target.id == 'image-view') {
        $(this).hide();
      }
    })
  })
</script>
<div id="image-viewer">
  <img id="image-view" class="center" />
</div>
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
  <?php
    echo "<span style=\"font-size: 0.8em; margin-left: 5px; padding: 5px;\"><a href=\"/addons/\">Add-Ons</a> >> ";
    echo "<a href=\"/addons/boards.php\">Boards</a> >> ";
    echo "<a href=\"board.php?id=" . $boardObject->getID() . "\">" . utf8_encode($boardObject->getName()) . "</a> >> ";
    echo "<a href=\"#\">" . htmlspecialchars($addonObject->getName()) . "</a></span>";

    $author = $addonObject->getAuthor();

    if($addonObject->getDeleted()) {
      echo '
      <div class="tile" style="background-color: lightgray; padding: 10px; margin-top: 10px; text-align: center;">
        <strong style="font-size: 2rem;">Deleted Add-On</strong><br>
        This add-on is not available to the public because it has been deleted.<br>
        <strong>Only administrators can view this page.</strong>
      </div>
      ';
    } else if($addonObject->isRejected()) {
      echo '
      <div class="tile" style="background-color: coral; padding: 10px; margin-top: 10px; text-align: center;">
        <strong style="font-size: 2rem;">Rejected Add-On</strong><br>
        This add-on is not available to the public because it has been rejected by the add-on moderation team.<br>
        <strong>Only the add-on moderation team and the add-on uploader can view this page.</strong>
      </div>
      ';
    } else if(!$addonObject->getApproved()) {
      echo '
      <div class="tile" style="background-color: gold; padding: 10px; margin-top: 10px; text-align: center;">
        <strong style="font-size: 2rem;">Unapproved Add-On</strong><br>
        This add-on has not been inspected by the add-on moderation team.<br>
        <strong>Only the add-on moderation team and the add-on uploader can view this page.</strong>
      </div>
      ';
    }

    if($current) {
      if((($current->inGroup("Reviewer") && $addonObject->getApproved()) || $addonObject->getManagerBLID() == $current->getBLID()) && !$addonObject->isRejected() && !$addonObject->getDeleted()) {
        echo '<div class="tile" style="padding: 10px; margin-top: 10px; text-align: center;"><a href="manage.php?id=' . $addonObject->getId() . '">Manage This Add-On</a></div>';
      }
    }

  ?>
  <div class="addon-info">
    <div class="addon-info-main">
      <div class="tile" style="margin-bottom: 5px;">
        <h2 style="margin-bottom: 0px;"><?php echo htmlspecialchars($addonObject->getName()) ?> </h2>
        <?php
          echo "Uploaded by <a href=\"/user/view.php?blid=" . $author->getBLID() . "\">" . htmlspecialchars(utf8_encode($author->getUsername())) . "</a>";
        ?>
        <div style="margin-top: 15px; margin-bottom: 10px; display: inline-block; width: 100%;">
          <div class="addoninfoleft">
            <image style="height:1.5em" src="https://blocklandglass.com/img/icons32/category.png" />
            <?php
              echo htmlspecialchars($boardObject->getName());
            ?>
            <br />
            <image style="height:1.5em" src="https://blocklandglass.com/img/icons32/folder_vertical_zipper.png" />
            <?php
              echo $addonObject->getFilename();
            ?>
            <br />
            <image style="height:1.5em" src="https://blocklandglass.com/img/icons32/date.png" />
            <?php echo date("M jS Y, g:i A", strtotime($addonObject->getUploadDate())); ?>
            <br />
          </div>
          <div class="addoninforight">
            <?php
            echo ($addonObject->getDownloads("web") + $addonObject->getDownloads("ingame"));
            ?>
             <image style="height:1.5em" src="https://blocklandglass.com/img/icons32/inbox_download.png" /><br />
            <br />
          </div>
        </div>
      </div>
      <div class="tile">
        <h3>Description</h3>
        <p>
          <?php
            $Parsedown = new Parsedown();
            $Parsedown->setBreaksEnabled(true);
            $Parsedown->setMarkupEscaped(true);

            //External links appearing in the description should open in a new tab and switch to that tab instead of replacing the current one
            echo $Parsedown->text($addonObject->getDescription());
          ?>
        </p>
      </div>
    </div>
    <div class="addon-info-side">
      <div class="tile" style="margin-bottom: 10px;">
        <h3>Updates</h3>
        <?php
          $updates = $addonObject->getUpdates();
          $updates = array_splice($updates, 0, 3, true);
          $updateCount = 0;

          if(sizeof($updates) > 0) {
            foreach($updates as $update) {
              if($update->isApproved()) {
                $updateCount++;
                ?>
                <div style="background-color: #f5f5f5; padding: 10px; margin: 5px">
                  <h4 style="padding: 0; margin: 0">Version <?php echo $update->getVersion();?></h4>
                  <span style="font-size: 0.8em; color: #666"><?php echo date("F j, Y", strtotime($update->getTimeSubmitted())); ?></span>
                </div>
                <?php
              }
            }
          }

          if($updateCount == 0) {
            echo "<div style=\"text-align: center\"><i>No updates approved.</i></div>";
          }
        ?>
      </div>
      <div class="tile">
        <h3>Bugs</h3>
        <?php
          $bugs = BugManager::getAddonBugsOpen($addonObject->getId());
          $bug_count = sizeof($bugs);
          $bugs = array_splice($bugs, 0, 3, true);

          foreach($bugs as $bug) {
            ?>
            <div style="background-color: #f5f5f5; padding: 10px; margin: 5px">
              <a href="/addons/bugs/view.php?id=<?php echo $bug->id; ?>">
                <h4 style="padding: 0; margin: 0"><?php echo htmlspecialchars($bug->title); ?></h4>
              </a>
              <span style="font-size: 0.8em; color: #666"><?php echo date("F j, Y", strtotime($bug->timestamp)); ?></span>
            </div>
            <?php
          }

          echo '<div style="text-align: center">';
          if($bug_count == 0) {
            echo "<i>No bugs reported.</i>";
          } else if($bug_count > 3) {
            ?>
              <a href="bugs/?id=<?php echo $addonObject->getId()?>">View more on the Bug Tracker</a>
            <?php
          }
          echo '</div>';
        ?>

      </div>
    </div>
  </div>
  <div style="text-align: center">
    <?php
    $version = $addonObject->getVersion();
    $id = "stable";
    $class = "green";

    $url = ($addonObject->getApproved() ? "/addons/download.php?id=" : "/addons/review/download.php?file=aws_sync/");
    // $url = "/addons/download.php?id=";

    if($addonObject->getApproved() || $current->inGroup("Reviewer")) {
      // echo '<a href="' . $url . $addonObject->getId() . '" class="btn dlbtn ' . $class . '"><strong>' . ucfirst($id) . '</strong><span style="font-size:9pt"><br />v' . $version . '</span></a>';
      echo '<a href="' . $url . $addonObject->getId() . '" class="btn dlbtn ' . $class . '"><strong>Download</strong><span style="font-size:9pt"><br />v' . $version . '</span></a>';
    }

    // unfinished, hasn't been made available for everybody.

    // echo '<a href="/addons/download.php?id=' . $addonObject->getId() . '&beta=0" class="btn dlbtn ' . $class . '"><strong>' . ucfirst($id) . '</strong><span style="font-size:9pt"><br />v' . $version . '</span></a>';
    // if($addonObject->hasBeta()) {
      // $id = "beta";
      // $class = "red";
      // echo '<a href="/addons/download.php?id=' . $addonObject->getId() . '&beta=1" class="btn dlbtn ' . $class . '"><strong>' . ucfirst($id) . '</strong><span style="font-size:9pt"><br />v' . $addonObject->getBetaVersion() . '</span></a>';
    // }
    ?>
  </div>
  <div class="screenshots" style="text-align:center;margin: 0 auto">
    <?php
    $screenshots = ScreenshotManager::getScreenshotsFromAddon($_GET['id']);
    foreach($screenshots as $sid) {
      $ss = ScreenshotManager::getFromId($sid);
      echo "<div class=\"image-preview\" style=\"padding: 5px; margin: 10px 10px; background-color: #eee; display:inline-block; width: 128px; vertical-align: middle\" ssid=\"" . $sid . "\">";
      //echo "<a target=\"_blank\" href=\"/addons/screenshot.php?id=" . $sid . "\">";
      echo "<img src=\"" . $ss->getThumbUrl() . "\" /></a>";
      echo "</div>";
    }
    ?>
  </div>
  <?php
    $deps = DependencyManager::getDependenciesFromAddonID($_GET['id']);
    if(sizeof($deps) > 0) {
      echo "<div class=\"tile\" style=\"text-align:center\">";
      echo "<strong>This add-on has some dependencies or add-ons that it requires to run:</strong><br/><br/>";
      foreach($deps as $did) {
        $dep = DependencyManager::getFromId($did);
        $rid = $dep->getRequired();
        $requiredAddon = AddonManager::getFromId($rid);
        echo "<div style=\"margin-bottom: 10px; padding: 10px; background-color: #ffbbbb; display: inline-block;\"><a href=\"addon.php?id=" . $requiredAddon->getId() . "\">" . $requiredAddon->getName() . "</a></div>";
      }
      echo "</div>";
    }

    if(!$addonObject->getApproved()) {
      echo '
      <div class="tile" style="background-color: #ffcccc; padding: 10px; margin-top: 10px;">
        <strong style="font-size: 1.5rem;">Review Discussion</strong><br>
        The comments section is available for cross-communication between the add-on uploader and the mod reviewers.<br>
        All comments will be cleared if the add-on is approved.
      </div>
      ';
    }
  ?>

  <div class="tile">
    <div class="comments" id="commentSection">
      <form action="" method="post">
        <?php include(realpath(dirname(__DIR__) . "/ajax/getComments.php")); ?>
      </form>
    </div>
  </div>

  <?php
    if($current && $current->inGroup("Reviewer") && $addonObject->approved == 0) {
      echo '
      <div class="tile" style="background-color: #ffcccc; text-align: center;" >
        <strong style="font-size: 1.5rem;">Approval</strong><br>
        <form action="/addons/review/approve.php" method="post"><br>
          Approve to board:<br>
          <select name="board">
          <option value="" disabled>Choose One</option>
          <option value="" disabled></option>
          ';
          $boards = BoardManager::getAllBoards();
          foreach($boards as $board) {
            if($board->getId() == $addonObject->getBoard()) {
              $selected = true;
            } else {
              $selected = false;
            }

            echo '<option value="' . $board->getId() . '"' . ($selected ? ' selected' : '') .'>' . $board->getName() . '</option>';
          }
          echo '
          </select><br><br>
          Please sign below before approving or rejecting this add-on:<br>
          <input type="checkbox" id="confirm" name="confirmed" value="1" />
          <label for="confirm"><strong>I have inspected this add-on and I am ready to make a decision.</strong></label><br><br>
          <table style="width: 100%;">
            <tr>
              <td>
                <input class="btn green" type="submit" name="action" value="Approve" />
              </td>
              <td>
                <input class="btn red" type="submit" name="action" value="Reject" />
              </td>
            </tr>
          </table>
          <input type="hidden" name="aid" value="' . $_GET['id'] . '">
          <input type="hidden" name="reason" value="Rejection reasons not available.">
        </form>
      </div>
      ';
    }
  ?>
</div>
<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
