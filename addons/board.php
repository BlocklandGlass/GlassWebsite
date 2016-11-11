<?php
	require_once(realpath(dirname(__DIR__) . "/private/class/BoardManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/AddonManager.php"));
	//require_once(realpath(dirname(__DIR__) . "/private/class/AddonObject.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/UserManager.php"));
	//require_once(realpath(dirname(__DIR__) . "/private/class/UserHandler.php"));

	//TO DO: rewrite this page to use /private/json/getBoardAddonsWithUsers.php
	//	And probably an ajax page to go with it

	if(isset($_GET['id'])) {
		try {
			$boardObject = BoardManager::getFromId($_GET['id'] + 0);
		} catch(Exception $e) {
			//board doesn't exist
			header('Location: /addons');
			die("board doesnt exist");
		}
	} else {
		header('Location: /addons');
		die();
	}
	$_PAGETITLE = "Blockland Glass | " . $boardObject->getName();
	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<?php include(realpath(dirname(__DIR__) . "/private/searchbar.php")); ?>
	<h1 style="text-align:center"><?php echo $boardObject->getName(); ?></h1>
	<a href="/addons">Add-Ons</a> >> <a href="/addons/boards.php">Boards</a> >> <a href="#"><?php echo $boardObject->getName() ?></a>
	<div class="pagenav">
<?php
	if(isset($_GET['page'])) {
		//easy way to force an integer
		$page = $_GET['page'] + 0;
	} else {
		$page = 1;
	}
	$pages = ceil($boardObject->getCount()/10);

	if($pages >= 7) {
		if($page < 4) {
			for($i = 0; $i < 4; $i++) {
				if($i+1 == $page) {
					echo "[<a href=\"board.php?id=" . $boardObject->getID() . "&page=" . ($i+1) . "\">" . ($i+1) . "</a>] ";
				} else {
					echo "<a href=\"board.php?id=" . $boardObject->getID() . "&page=" . ($i+1) . "\">" . ($i+1) . "</a> ";
				}
			}
			echo " ... ";

			//TO DO: switch this over to ajax requests
			echo "<a href=\"?id=" . $boardObject->getID() . "&page=" . ($pages-1) . "\">" . $pages-1 . "</a>";
			echo "<a href=\"?id=" . $boardObject->getID() . "&page=" . $pages . "\">" . $pages . "</a>";
		} else if($pages-3 < $page) {
			echo "<a href=\"?id=" . $boardObject->getID() . "&page=1\">1</a>";
			echo "<a href=\"?id=" . $boardObject->getID() . "&page=2\">2</a>";
			echo " ... ";

			for($i = $pages-4; $i < $pages; $i++) {
				if($i+1 == $page) {
					echo "[<a href=\"board.php?id=" . $boardObject->getID() . "&page=" . ($i+1) . "\">" . ($i+1) . "</a>] ";
				} else {
					echo "<a href=\"board.php?id=" . $boardObject->getId() . "&page=" . ($i+1) . "\">" . ($i+1) . "</a> ";
				}
			}
		} else { ?>
			<a href="?id=<?php echo $boardObject->getID() ?>&page=1">1</a>
			<a href="?id=<?php echo $boardObject->getID() ?>&page=2">2</a>
			...
			<a href="?id=<?php echo $boardObject->getID() . "&page=" . ($page-1); ?>"><?php echo $page-1; ?></a>
			[<a href="?id=<?php echo $boardObject->getID() . "&page=" . $page; ?>"><?php echo $page; ?></a>]
			<a href="?id=<?php echo $boardObject->getID() . "&page=" . ($page+1); ?>"><?php echo $page+1; ?></a>
			...
			<a href="?id=<?php echo $boardObject->getID() . "&page=" . ($pages-1); ?>"><?php echo $pages-1; ?></a>
			<a href="?id=<?php echo $boardObject->getID() . "&page=" . $pages; ?>"><?php echo $pages; ?></a>
			<?php
		}
	} else {
		for($i = 0; $i < $pages; $i++) {
			if($i+1 == $page) {
				echo "[<a href=\"board.php?id=" . $boardObject->getID() . "&page=" . ($i+1) . "\">" . ($i+1) . "</a>] ";
			} else {
				echo "<a href=\"board.php?id=" . $boardObject->getID() . "&page=" . ($i+1) . "\">" . ($i+1) . "</a> ";
				}
			}
	}
?>
	</div>
	<div class="tile">
	<table class="boardtable">
		<tbody>
			<tr class="boardheader shadow-1">
				<td>Name</td>
				<td>Uploader</td>
				<td>Rating</td>
				<td>Downloads</td>
			</tr>
			<?php
				//$addons = $boardObject->getAddons(($page-1)*10, 10);
				$addonIDs = AddonManager::getFromBoardID($boardObject->getID(), ($page-1)*10, 10);
				//$addons = BoardManager::getAddonsFromBoardID($boardObject->getID(), ($page-1)*10, 10);

				foreach($addonIDs as $aid) {
					$addon = AddonManager::getFromID($aid); ?>
					<tr>
					<td style="width: 33%"><a href="addon.php?id=<?php echo $addon->getID(); ?>"><?php echo $addon->getName(); ?></a></td>
					<td style="font-size: 11pt"><?php
					$authors = $addon->getAuthorInfo();

					//This system should probably be rethought
					if(sizeof($authors) == 1) {
						//$uo = new UserHandler();
						//$uo->initFromId($authors[0]->id);
						$uo = UserManager::getFromBLID($authors[0]->blid);
						echo "<a href=\"/user/view.php?blid=" . $authors[0]->blid . "\">" . utf8_encode($uo->getName()) . "</a>";
					} else if(sizeof($authors) == 2) {
						//$uo = new UserHandler();
						//$uo->initFromId($authors[0]->id);
						$uo = UserManager::getFromBLID($authors[0]->blid);
						$uo2 = new UserHandler();
						$uo2->initFromId($authors[1]->id);
						$uo2 = UserManager::getFromBLID($authors[1]->blid);
						echo "<a href=\"#\">" . $uo->getName() . "</a>";
						echo " and ";
						echo "<a href=\"#\">" . $uo2->getName() . "</a>";
					} else {
						$count = sizeof($authors);
						//echo("DATA: ");
						//print_r($authors);

						foreach($authors as $num=>$author) {
							//$uo = new UserHandler();
							//$uo->initFromId($auth->id);
							$uo = UserManager::getFromBLID($author->blid);

							if($count-$num == 1) {
								echo "and <a href=\"#\">" . $uo->getName() . "</a>";
							} else {
								echo "<a href=\"#\">" . $uo->getName() . "</a>, ";
							}
						}
					} ?>
					</td>
					<td>
						<?php
							$rating = $addon->getRating();
							if($rating == 0) {
								echo "Unrated";
							} else {
								echo $rating;
							}
						?>
					</td>
					<td><?php echo ($addon->getDownloads("web") + $addon->getDownloads("ingame")); ?></td>
					</tr><?php
				}

				if(sizeof($addonIDs) == 0) {
					echo '<tr><td colspan="4">No Add-Ons!</td></tr>';
				}
				//TO DO: page number links should also appear at the bottom, probably inside of the grey footer
			?>
		</tbody>
	</table>
	</div>
</div>

<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
