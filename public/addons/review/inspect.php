<?php
  require dirname(__DIR__) . '/../../private/autoload.php';

	$_PAGETITLE = "Blockland Glass | Inspect Add-On";

	include(realpath(dirname(__DIR__) . "/../../private/header.php"));
	use Glass\AddonManager;
	use Glass\BoardManager;
	use Glass\UserManager;
	use Glass\UserLog;

	$user = UserManager::getCurrent();
	if(!$user || !$user->inGroup("Reviewer")) {
    header('Location: /addons');
    return;
  }

  $addonObject = AddonManager::getFromID($_REQUEST['id']);

  if($addonObject->getDeleted()) {
    include(__DIR__ . "/../deleted.php");
		die();
	} else if($addonObject->isRejected()) {
    include(__DIR__ . "/../rejected.php");
    die();
  } else if($addonObject->getApproved()) {
    include(__DIR__ . "/../approved.php");
    die();
  }

  $manager = UserManager::getFromBLID($addonObject->getManagerBLID());
?>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
  ?>
  <div class="reviewbody">
    <h2><?php echo $addonObject->getName(); ?></h2>
    <p>Uploaded <?php echo date("M jS Y, g:i A", strtotime($addonObject->getUploadDate())); ?> by <?php echo '<a href="/user/view.php?blid=' . $manager->getBlid() . '"?>' . $manager->getName() . '</a>'; ?><br>
    The current date & time is: <?php echo date("M jS Y, g:i A"); ?></p>
    <hr />
    <table>
      <tbody>
        <tr>
          <td style="padding: 10px;"><strong>Filename</strong></td>
          <td><?php echo $addonObject->getFilename() ?></td>
        </tr>
        <tr>
          <td style="padding: 10px;"><strong>Description</strong></td>
          <td><?php echo $addonObject->getDescription() ?></td>
        </tr>
        <tr>
          <td style="padding: 10px;"><strong>Version</strong></td>
          <td><pre style="font-size: .5em"><?php echo $addonObject->getVersion(); ?></pre></td>
        </tr>
        <tr>
          <td style="padding: 10px;"><strong>Author</strong></td>
          <td>
          <?php
            echo $addonObject->getAuthor()->getUsername();
          ?>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="font-size:0.8em">
            <?php
            $file = realpath(dirname(__DIR__) . '/../addons/files/local/' . $addonObject->getId() . '.zip');
            $zip = new \ZipArchive();
            $res = $zip->open($file);
            if($res === TRUE) {
              for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);
                if(strpos($fileName, ".gui") !== false || strpos($fileName, ".cs") !== false) {
                  $str = $zip->getFromIndex($i);
                  echo "$fileName<br /><div class=\"code\">" . $str . "</div><hr />";
                }
              }
            }
            ?>
          </td>
        </tr>
      </tbody>
    </table>
    <hr />
    <form action="approve.php" method="post">
      <input type="hidden" name="aid" value="<?php echo $addonObject->getId() ?>" />
      <table style="width:100%">
        <tbody>
          <tr>
            <td style="vertical-align:top; width: 50%; background-color: #CCFFCC; padding: 10px;">
              <strong>Approve to board:</strong><br/>
              <select name="board" value="A">
                <option value="" disabled >Choose One</option>
                <option value="" disabled></option>
                <?php
                  $boards = BoardManager::getAllBoards();
                  foreach($boards as $board) {
                    if($board->getId() == $addonObject->getBoard()) {
                      echo 'selected!';
                      $selected = true;
                    } else {
                      $selected = false;
                    }

                    echo '<option value="' . $board->getId() . '"' . ($selected ? ' selected' : '') .'>' . $board->getName() . '</option>';
                  }
                ?>
              </select>
            </td>
            <td style="padding: 10px; background-color: #FFCCCC; width: 50%">
              <strong>Rejection Reason</strong><br />
              <textarea style="width: 400px; height: 150px; font-size: 0.8em; margin: 0 auto;" name="reason" placeholder="Rejection reasons not available." disabled></textarea>
            </td>
          </tr>
          <tr>
            <td style="background-color: #CCFFCC; text-align: center;">
              <input type="submit" name="action" value="Approve" />
            </td>
            <td style="background-color: #FFCCCC; text-align: center;">
              <input type="submit" name="action" value="Reject" />
            </td>
          </tr>
          <tr>
            <td colspan="2" style="background-color: #eee; text-align: center;">
              <a style="btn blue" href="download.php?file=aws_sync/<?php echo $addonObject->getId() ?>">Download</a>
            </td>
          </tr>
        </tbody>
      </table>
    </form>
  </div>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../../private/footer.php")); ?>
