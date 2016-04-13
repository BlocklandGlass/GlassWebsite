<?php
	$_PAGETITLE = "Glass | Inspect Update";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/BoardManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/UserLog.php"));

  $addon = AddonManager::getFromID($_REQUEST['id']);
  $manager = UserManager::getFromBLID($addon->getManagerBLID());
?>
<div class="maincontainer">
  <h2><?php echo $addon->getName(); ?></h2>
  <p>Uploaded <?php echo date("D \a\\t g:i a", strtotime($addon->getUploadDate())); ?> by <?php echo '<a href="/user/view.php?blid=' . $manager->getBlid() . '"?>' . $manager->getName() . '</a>'; ?></p>
  <hr />
  <table>
    <tbody>
      <tr>
        <td style="padding: 10px;"><b>Filename</b></td>
        <td><?php echo $addon->getFilename() ?></td>
      </tr>
      <tr>
        <td style="padding: 10px;"><b>Description</b></td>
        <td><?php echo $addon->getDescription() ?></td>
      </tr>
      <tr>
        <td style="padding: 10px;"><b>Version</b></td>
        <td><pre style="font-size: .5em"><?php echo $addon->getVersion(); ?></pre></td>
      </tr>
      <tr>
        <td style="padding: 10px;"><b>Authors</b></td>
        <td>
        <?php
        foreach($addon->getAuthorInfo() as $author) {
          $name = UserLog::getCurrentUsername($author->blid);
          if($name == false) {
            $name = "Blockhead" . $author->blid;
          }
          echo "$name - <i>" . $author->role . "</i><br />";
        }
        ?>
        </td>
      </tr>
      <tr>
        <td style="padding: 10px;"><b>Tags</b></td>
        <td>

        </td>
      </tr>
    </tbody>
  </table>
  <hr />
  <form action="approve.php" method="post">
		<input type="hidden" name="aid" value="<?php echo $addon->getId() ?>" />
    Move to board:
		<select name="board">
			<?php
				$boards = BoardManager::getAllBoards();
				foreach($boards as $board) {
					echo '<option value="' . $board->getId() . '">' . $board->getName() . '</option>';
				}
			?>
		</select>
		<input type="submit" name="action" value="Approve" />
		<input type="submit" name="action" value="Reject" />
  </form>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
