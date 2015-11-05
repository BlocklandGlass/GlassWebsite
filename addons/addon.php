<?php
	require_once(realpath(dirname(__DIR__) . "/private/class/BoardManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/AddonObject.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/UserHandler.php"));
	require_once(realpath(dirname(__DIR__) . "/private/lib/Parsedown.php"));

	if(isset($_GET['id'])) {
		try {
			$addonObject = AddonManager::getFromId($_GET['id']);
			$boardObject = $addonObject->getBoard();
		} catch(Exception $e) {
			//board doesn't exist
			header('Location: /addons');
			die("addon doesnt exist");
		}
	} else {
		header('Location: /addons');
		die();
	}

	$_PAGETITLE = "Glass | " . $addonObject->getName();

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<?php
		echo "<span style=\"font-size: 9pt;\"><a href=\"/addons/\">Add-Ons</a> >> ";
		echo "<a href=\"board.php?id=" . $boardObject["id"] . "\">" . htmlspecialchars($boardObject["name"]) . "</a> >> ";
		echo "<a href=\"#\">" . htmlspecialchars($addonObject->getName()) . "</a></span>";
		echo "<h2>" . htmlspecialchars($addonObject->getName()) . "</h2>";
		//<span style="font-size: 9pt;"><a href="/addons/">Add-Ons</a> >> <a href="board.php?id=<?php echo $boardObject->getId() ? >"><?php echo htmlspecialchars($boardObject->getName()); ? ></a> >> <a href="#"><?php echo htmlspecialchars($addonObject->getName()); ? ></a></span>
		//	<h2><?php echo $addonObject->getName(); ? ></h2>
	?>
	<p>
		<image src="http://blocklandglass.com/icon/icons32/user.png" /> By <?php
		$authors = $addonObject->getAuthors();

		if(sizeof($authors) == 1) {
			$uo = new UserHandler();
			$uo->initFromId($authors[0]->id);
			echo "<a href=\"#\">" . htmlspecialchars($uo->getName()) . "</a>";
		} else if(sizeof($authors) == 2) {
			$uo = new UserHandler();
			$uo->initFromId($authors[0]->id);
			$uo2 = new UserHandler();
			$uo2->initFromId($authors[1]->id);
			echo "<a href=\"#\">" . htmlspecialchars($uo->getName()) . "</a>";
			echo " and ";
			echo "<a href=\"#\">" . htmlspecialchars($uo2->getName()) . "</a>";
		} else {
			$count = sizeof($authors);
			foreach($authors as $num=>$auth) {
				$uo = new UserHandler();
				$uo->initFromId($auth->id);

				if($count-$num == 1) {
					echo "and <a href=\"#\">" . htmlspecialchars($uo->getName()) . "</a>";
				} else {
					echo "<a href=\"#\">" . htmlspecialchars($uo->getName()) . "</a>, ";
				}
			}
		}
		?>
		<br />
		<image src="http://blocklandglass.com/icon/icons32/<?php echo $boardObject["icon"] ?>.png" /> <?php echo htmlspecialchars($boardObject["name"]) ?>
	</p>
	<p>
		<?php
			$Parsedown = new Parsedown();
			$Parsedown->setBreaksEnabled(true);
			$Parsedown->setMarkupEscaped(true);

			//this might need to be escaped as well, not too sure what goes on underneath
			echo $Parsedown->text($addonObject->getDescription());
		?>
	</p>
	<div style="text-align: center">
		<a href="http://blocklandglass.com/addon.php?id=<?php echo $addonObject->getId(); ?>" class="btn dlbtn green"><b>Stable</b><span style="font-size:9pt"><br />v1.1.0</span></a>
		<a href="http://blocklandglass.com/addon.php?id=<?php echo $addonObject->getId(); ?>" class="btn dlbtn yellow"><b>Unstable</b><span style="font-size:9pt"><br />v1.1.0-alpha.1</span></a>
		<a href="http://blocklandglass.com/addon.php?id=<?php echo $addonObject->getId(); ?>" class="btn dlbtn red"><b>Development</b><span style="font-size:9pt"><br />v1.1.0-alpha.6</span></a><br />
	</div>
	<hr />
	<a href="displayTest.php">Script Breakdown</a><br />
	<a href="scriptDisplay.php">Script Analysis</a>
	<hr />
	<div class="comments">
	<?php include(realpath(dirname(__DIR__) . "/api/getComments.php")); ?>
	</div>
</div>

<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
