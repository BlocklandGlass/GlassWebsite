<?php
	$_PAGETITLE = "Glass | Review List";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	require_once(realpath(dirname(__DIR__) . "/../private/class/AddonManager.php"));
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
        <td style="padding: 10px;"><b>Version Info</b></td>
        <td><pre style="font-size: .5em"><?php echo json_encode($addon->getVersionInfo(), JSON_PRETTY_PRINT) ?></pre></td>
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
        <?php
        // TODO my plane is landing
        ?>
        </td>
      </tr>
    </tbody>
  </table>
  <hr />
  <form>
    Move to board:
  </form>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
