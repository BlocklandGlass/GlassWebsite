<?php
	require_once(realpath(dirname(__DIR__) . "/private/class/BoardManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/AddonObject.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/UserLog.php"));
//	require_once(realpath(dirname(__DIR__) . "/private/class/UserHandler.php"));
	require_once(realpath(dirname(__DIR__) . "/private/lib/Parsedown.php"));

	//to do: use ajax/json to build data for page
	//this php file should just format the data nicely
	if(isset($_GET['id'])) {
		try {
			$addonObject = AddonManager::getFromId($_GET['id'] + 0);
			$boardObject = BoardManager::getFromID($addonObject->getBoard());
		} catch(Exception $e) {
			//board doesn't exist
			header('Location: /addons');
			die("addon doesnt exist");
		}
	} else {
		header('Location: /addons');
		die();
	}

	if(!$addonObject->getApproved()) {
		include 'unapproved.php';
		die();
	}

	$_PAGETITLE = "Blockland Glass | " . $addonObject->getName();

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<?php
		echo "<span style=\"font-size: 9pt;\"><a href=\"/addons/\">Add-Ons</a> >> ";
		echo "<a href=\"/addons/boards.php\">Boards</a> >> ";
		echo "<a href=\"board.php?id=" . $boardObject->getID() . "\">" . utf8_encode($boardObject->getName()) . "</a> >> ";
		echo "<a href=\"#\">" . $addonObject->getName() . "</a></span>";

		echo "<h2 style=\"margin-bottom: 0px;\">Moderating: <i>" . $addonObject->getName() . "</i></h2>";
    $authors = $addonObject->getAuthorInfo();

		echo "Uploaded by ";

    if(sizeof($authors) == 1) {
      //$uo = new UserHandler();
      //$uo->initFromId($authors[0]->id);
      $name = UserLog::getCurrentUsername($authors[0]->blid);
      echo "<a href=\"/user/view.php?blid=" . $authors[0]->blid . "\">" . utf8_encode($name) . "</a>";
    } else if(sizeof($authors) == 2) {
      //we cant use UserHandler here because we may not have accounts for all

      $name1 = UserLog::getCurrentUsername($authors[0]->blid);
      if($name1 === false) {
        $name1 = "Blockhead" . $authors[0]->blid;
      }
      $name2 = UserLog::getCurrentUsername($authors[1]->blid);
      if($name2 === false) {
        $name2 = "Blockhead" . $authors[1]->blid;
      }
      echo "<a href=\"/user/view.php?blid=" . $authors[0]->blid . "\">" . utf8_encode($name1) . "</a>";
      echo " and ";
      echo "<a href=\"/user/view.php?blid=" . $authors[1]->blid . "\">" . utf8_encode($name2) . "</a>";
    } else {
      var_dump($authors);
      $count = sizeof($authors);
      foreach($authors as $num=>$author) {
        //$uo = new UserHandler();
        //$uo->initFromId($auth->id);
        $uo = UserManager::getFromBLID($author->blid);

        if($count-$num == 1) {
          echo "and <a href=\"#\">" . utf8_encode($uo->getName()) . "</a>";
        } else {
          echo "<a href=\"#\">" . utf8_encode($uo->getName()) . "</a>, ";
        }
      }
    }
	?>
	<div style="margin-top: 15px; margin-bottom: 15px; display: inline-block; width: 100%; font-size: 0.8em">
		<div class="addoninfoleft">
			<image style="height:1.5em" src="http://blocklandglass.com/img/icons32/tag.png" />
			<?php
			echo utf8_encode($boardObject->getName());
			?>
			<br />
			<image style="height:1.5em" src="http://blocklandglass.com/img/icons32/folder_vertical_zipper.png" />
			<?php
			echo $addonObject->getFilename();
			?>
			<br />
			<image style="height:1.5em" src="http://blocklandglass.com/img/icons32/time.png" />
			<?php echo date("M jS Y, g:i A", strtotime($addonObject->getUploadDate())); ?>
		</div>
		<div class="addoninforight">
			<?php
			echo ($addonObject->getDownloads("web") + $addonObject->getDownloads("ingame"));
			?>
			 <image style="height:1.5em" src="http://blocklandglass.com/img/icons32/inbox_download.png" /><br />
		</div>
	</div>
	<hr />
	<p>
		<?php
			$Parsedown = new Parsedown();
			$Parsedown->setBreaksEnabled(true);
			$Parsedown->setMarkupEscaped(true);

			//External links appearing in the description should open in a new tab and switch to that tab instead of replacing the current one
			echo $Parsedown->text($addonObject->getDescription());
		?>
	</p>
	<hr />
  <table class="formtable">
    <tbody>
      <tr>
        <td><b>Title</b></td>
        <td><input type="text" name="title" value="<?php echo htmlspecialchars($addonObject->getName()) ?>"/></td>
      </tr>
      <tr>
        <td colspan="2">
          <input type="submit" value="Update" />
        </td>
      </tr>
    </tbody>
  </table>
	<hr />
</div>
<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
