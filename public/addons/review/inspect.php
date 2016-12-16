<?php
	$_PAGETITLE = "Blockland Glass | Inspect Update";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	use Glass\AddonManager;
	use Glass\BoardManager;
	use Glass\UserManager;
	use Glass\UserLog;

	$user = UserManager::getCurrent();
	if(!$user || !$user->inGroup("Reviewer")) {
    header('Location: /addons');
    return;
  }

  $addon = AddonManager::getFromID($_REQUEST['id']);
  $manager = UserManager::getFromBLID($addon->getManagerBLID());
?>
<div class="maincontainer">
	<style>
	.code {
		font-size: 0.8em;
		background-color: #eee;
	  padding: 5px;
	  width: 50%;
	  vertical-align : top;
	  white-space    : pre;
	  font-family    : monospace;
	}
	</style>
  <h2><?php echo $addon->getName(); ?></h2>
  <p>Uploaded <?php echo date("M jS Y, g:i A", strtotime($addon->getUploadDate())); ?> by <?php echo '<a href="/user/view.php?blid=' . $manager->getBlid() . '"?>' . $manager->getName() . '</a>'; ?></p>
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
      <!--
      <tr>
        <td style="padding: 10px;"><b>Tags</b></td>
        <td>

        </td>
      </tr>
      -->
			<tr>
        <td colspan="2" style="font-size:0.8em">
					<?php
			    $file = realpath(dirname(__DIR__) . '/../addons/files/local/' . $addon->getId() . '.zip');
					$zip = new ZipArchive();
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
		<input type="hidden" name="aid" value="<?php echo $addon->getId() ?>" />
		<table style="width:100%">
			<tbody>
				<tr>
					<td style="vertical-align:top; width: 50%; background-color: #CCFFCC; padding: 10px; border-top-left-radius: 10px;">
				    <b>Approve to board:</b><br/>
						<select name="board" value="none">
				    	<option value="" disabled selected>Choose One</option>
				    	<option value="" disabled></option>
							<?php
								$boards = BoardManager::getAllBoards();
								foreach($boards as $board) {
									echo '<option value="' . $board->getId() . '">' . $board->getName() . '</option>';
								}
							?>
						</select>
					</td>
					<td style="padding: 10px; background-color: #FFCCCC; border-top-right-radius: 10px; width: 50%">
						<b>Rejection Reason</b><br />
						<textarea style="width: 400px; height: 150px; font-size: 0.8em; margin: 0 auto;" name="reason"></textarea>
					</td>
				</tr>
				<tr>
					<td style="background-color: #CCFFCC; text-align: center; border-bottom-left-radius: 10px;">
						<input type="submit" name="action" value="Approve" />
					</td>
					<td style="background-color: #FFCCCC; text-align: center; border-bottom-right-radius: 10px;">
						<input type="submit" name="action" value="Reject" />
					</td>
				</tr>
			</tbody>
		</table>
  </form>
</div>

<?php
	//TO DO:
	//add script to bottom of page to prevent refresh on search

	include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
