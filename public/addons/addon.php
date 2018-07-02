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

  if($addonObject->getDeleted()) {
		include 'deleted.php';
		die();
	} else if($addonObject->isRejected()) {
		include 'rejected.php';
		die();
	} else if(!$addonObject->getApproved()) {
		include 'unapproved.php';
		die();
	}

	if(isset($_POST['comment'])) {
		CommentManager::submitComment($addonObject->getId(), UserManager::getCurrent()->getBLID(), $_POST['comment']);
	}

	$_PAGETITLE = "Blockland Glass | " . $addonObject->getName();
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
	border-bottom: 2px solid #ddd;
}

.addon-info .tile {
	padding: 15px;
}

</style>
<div class="maincontainer">
	<?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));

		echo "<span style=\"font-size: 0.8em; padding-left: 10px\"><a href=\"/addons/\">Add-Ons</a> >> ";
		echo "<a href=\"/addons/boards.php\">Boards</a> >> ";
		echo "<a href=\"board.php?id=" . $boardObject->getID() . "\">" . utf8_encode($boardObject->getName()) . "</a> >> ";
		echo "<a href=\"#\">" . htmlspecialchars($addonObject->getName()) . "</a></span>";

		if($current = UserManager::getCurrent()) {
			if($current->inGroup("Moderator")) {
				echo "<div class=\"tile\" style=\"background-color: #ed7669; padding: 10px; margin-top:10px; text-align:center\"><a href=\"moderate.php?id=" . $addonObject->getId() . "\">Moderation</a></div>";
			}
		}

	?>
	<div class="addon-info">
		<div class="addon-info-main">
			<div class="tile" style="margin-bottom: 5px;">
				<h2 style="margin-bottom: 0px;"><?php echo htmlspecialchars($addonObject->getName()) ?> </h2>
				<?php
			    $author = $addonObject->getAuthor();

					echo "Uploaded by <a href=\"/user/view.php?blid=" . $author->getBLID() . "\">" . htmlspecialchars(utf8_encode($author->getUsername())) . "</a>";
				?>
				<div style="margin-top: 15px; margin-bottom: 10px; display: inline-block; width: 100%;">
					<div class="addoninfoleft">
						<image style="height:1.5em" src="https://blocklandglass.com/img/icons32/tag.png" />
						<?php
							echo htmlspecialchars($boardObject->getName());
						?>
						<br />
						<image style="height:1.5em" src="https://blocklandglass.com/img/icons32/folder_vertical_zipper.png" />
						<?php
							echo $addonObject->getFilename();
						?>
						<br />
						<image style="height:1.5em" src="https://blocklandglass.com/img/icons32/time.png" />
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
				<h3>Recent Updates</h3>
				<?php
					$updates = ($addonObject->getUpdates());
					$updates = array_splice($updates, 0, 3, true);

					foreach($updates as $update) {
						?>
						<div style="background-color: #f5f5f5; padding: 10px; margin: 5px">
							<h4 style="padding: 0; margin: 0">Version <?php echo $update->getVersion();?></h4>
							<span style="font-size: 0.8em; color: #666"><?php echo date("F j, Y", strtotime($update->getTimeSubmitted())); ?></span>
						</div>
						<?php
					}

					if(sizeof($updates) == 0) {
						echo "<div style=\"text-align: center\"><i>No updates</i></div>";
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
						echo "<i>No bugs!</i>";
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
		echo '<a href="/addons/download.php?id=' . $addonObject->getId() . '&beta=0" class="btn dlbtn ' . $class . '"><strong>' . ucfirst($id) . '</strong><span style="font-size:9pt"><br />v' . $version . '</span></a>';
		if($addonObject->hasBeta()) {
			$id = "beta";
			$class = "red";
			echo '<a href="/addons/download.php?id=' . $addonObject->getId() . '&beta=1" class="btn dlbtn ' . $class . '"><strong>' . ucfirst($id) . '</strong><span style="font-size:9pt"><br />v' . $addonObject->getBetaVersion() . '</span></a>';
		}
		?>
	</div>
	<div class="screenshots" style="text-align:center;margin: 0 auto">
		<?php
		$screenshots = ScreenshotManager::getScreenshotsFromAddon($_GET['id']);
		if(sizeof($screenshots) > 0) {
			echo "<hr />";
		}
		foreach($screenshots as $sid) {
		  $ss = ScreenshotManager::getFromId($sid);
		  echo "<div style=\"padding: 5px; margin: 10px 10px; background-color: #eee; display:inline-block; width: 128px; vertical-align: middle\">";
		  echo "<a target=\"_blank\" href=\"/addons/screenshot.php?id=" . $sid . "\">";
		  echo "<img src=\"" . $ss->getThumbUrl() . "\" /></a>";
		  echo "</div>";
		}
		?>
	</div>
	<?php
		$deps = DependencyManager::getDependenciesFromAddonID($_GET['id']);
		if(sizeof($deps) > 0) {
			echo "<hr /><div style=\"text-align:center\">";
			echo "<strong>This add-on has some dependencies or add-ons that it requires to run:</strong><br/><br/>";
			foreach($deps as $did) {
				$dep = DependencyManager::getFromId($did);
				$rid = $dep->getRequired();
				$requiredAddon = AddonManager::getFromId($rid);
				echo "<div style=\"margin-bottom: 20px; padding: 10px; background-color: #ffbbbb; display: inline-block;\"><a href=\"addon.php?id=" . $requiredAddon->getId() . "\">" . $requiredAddon->getName() . "</a></div>";
			}
			echo "</div>";
		}
	?>
	<div class="tile">
		<div class="comments" id="commentSection">
			<form action="" method="post">
				<?php include(realpath(dirname(__DIR__) . "/ajax/getComments.php")); ?>
			</form>
		</div>
	</div>
</div>
<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
