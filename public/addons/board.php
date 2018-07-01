<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\BoardManager;
	use Glass\AddonManager;
	//use Glass\AddonObject;
	use Glass\UserManager;
	//use Glass\UserHandler;

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
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
?>
<div class="maincontainer">
	<?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
    include(realpath(dirname(__DIR__) . "/../private/searchbar.php"));
  ?>
	<h1 style="text-align:center"><?php echo $boardObject->getName(); ?></h1>
	<div style="margin-left: 20px; display: inline-block;">
		<a href="/addons">Add-Ons</a> >> <a href="/addons/boards.php">Boards</a> >> <a href="#"><?php echo $boardObject->getName() ?></a>
	</div>
	<div class="pagenav" style="margin-right: 20px;">
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
			echo "<a href=\"?id=" . $boardObject->getID() . "&page=" . ($pages-1) . "\">" . ($pages-1) . "</a> ";
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
			<tr class="boardheader">
				<td>Name</td>
				<td style="text-align:center !important">Uploader</td>
				<td>Version</td>
				<td>Downloads</td>
			</tr>
			<?php
				$addonIDs = AddonManager::getFromBoardID($boardObject->getID(), ($page-1)*10, 10);

				foreach($addonIDs as $aid) {
					$addon = AddonManager::getFromID($aid); ?>
					<tr>
					<td style="width: 33%; text-align:left">
						<a href="addon.php?id=<?php echo $addon->getID(); ?>"><?php echo htmlspecialchars($addon->getName()); ?></a>
						<p style="padding: 0; margin: 0; font-size: 0.8em; color: #666;">
							<?php echo htmlspecialchars($addon->getSummary()); ?>
						</p>
					</td>
					<td style="font-size: 11pt; text-align:center">
						<a href="/user/view.php?blid=<?php echo $addon->getBLID() ?>">
							<?php echo htmlspecialchars(utf8_encode($addon->getAuthor()->getUsername()));	?>
						</a>
					</td>
					<td>
						<?php echo htmlspecialchars($addon->getVersion()); ?>
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

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
