<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
	use Glass\BoardManager;
	use Glass\AddonManager;
	//use Glass\AddonObject;
	use Glass\UserManager;
	//use Glass\UserHandler;

	//TO DO: rewrite this page to use /private/json/getBoardAddonsWithUsers.php
	//	And probably an ajax page to go with it

  use Glass\RTBAddonManager;

	$_PAGETITLE = "RTB Boards | Blockland Glass";
	include(realpath(dirname(__DIR__) . "/../../private/header.php"));

	$type = $_GET['name'] ?? "";
	$page = $_GET['page'] ?? 1;

	if(!is_numeric($page) || $page < 1) {
		$page = 1;
	}

	$pages = floor(RTBAddonManager::getTypeCount($type)/15);
	$addons = RTBAddonManager::getFromType($type, $page*15);
?>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
    include(realpath(dirname(__DIR__) . "/../../private/subnavigationbar.php"));
  ?>
  <h1 style="text-align:center"><img src="/img/rtb_logo.gif"><br /><?php echo $_GET['name']; ?></h1>
  <div style="display: inline; margin-top: 20px; margin-left: 20px;">
    <a href="/addons/">Add-Ons</a> >> <a href="/addons/rtb/">RTB Archive</a> >> <a href="#"><?php echo $_GET['name']; ?></a>
  </div>
	<div class="pagenav" style="margin-right: 20px">
		<?php
			if($pages >= 7) {
				if($page < 4) {
					for($i = 0; $i < 4; $i++) {
						if($i+1 == $page) {
							echo "[<a href=\"board.php?name=" . $type . "&page=" . ($i+1) . "\">" . ($i+1) . "</a>] ";
						} else {
							echo "<a href=\"board.php?name=" . $type . "&page=" . ($i+1) . "\">" . ($i+1) . "</a> ";
						}
					}
					echo " ... ";

					//TO DO: switch this over to ajax requests
					echo "<a href=\"?name=" . $type . "&page=" . ($pages-1) . "\">" . ($pages-1) . "</a>";
					echo "<a href=\"?name=" . $type . "&page=" . $pages . "\">" . $pages . "</a>";
				} else if($pages-3 < $page) {
					echo "<a href=\"?name=" . $type . "&page=1\">1</a> ";
					echo "<a href=\"?name=" . $type . "&page=2\">2</a> ";
					echo " ... ";

					for($i = $pages-4; $i < $pages; $i++) {
						if($i+1 == $page) {
							echo "[<a href=\"board.php?name=" . $type . "&page=" . ($i+1) . "\">" . ($i+1) . "</a>] ";
						} else {
							echo "<a href=\"board.php?name=" . $type . "&page=" . ($i+1) . "\">" . ($i+1) . "</a> ";
						}
					}
				} else { ?>
					<a href="?name=<?php echo $type; ?>&page=1">1</a>
					<a href="?name=<?php echo $type; ?>&page=2">2</a>
					...
					<a href="?name=<?php echo $type . "&page=" . ($page-1); ?>"><?php echo $page-1; ?></a>
					[<a href="?name=<?php echo $type . "&page=" . $page; ?>"><?php echo $page; ?></a>]
					<a href="?name=<?php echo $type . "&page=" . ($page+1); ?>"><?php echo $page+1; ?></a>
					...
					<a href="?name=<?php echo $type . "&page=" . ($pages-1); ?>"><?php echo $pages-1; ?></a>
					<a href="?name=<?php echo $type . "&page=" . $pages; ?>"><?php echo $pages; ?></a>
					<?php
				}
			} else {
				for($i = 0; $i < $pages; $i++) {
					if($i+1 == $page) {
						echo "[<a href=\"board.php?name=" . $type . "&page=" . ($i+1) . "\">" . ($i+1) . "</a>] ";
					} else {
						echo "<a href=\"board.php?name=" . $type . "&page=" . ($i+1) . "\">" . ($i+1) . "</a> ";
						}
					}
			}
		?>
	</div>

	<div class="tile">
		<table class="boardtable">
			<tbody>
				<tr class="boardheader">
					<td style="width: auto; text-align: left;">Name</td>
					<td style="text-align: center !important;">Author</td>
					<td style="width: 90px;">ID</td>
				</tr>
				<?php
					foreach($addons as $addon) {
						?>
						<tr>
						<td style="text-align: left; width: auto;"><a href="view.php?id=<?php echo $addon->id?>"><?php echo $addon->title ?></a></td>
						<td style="text-align: center; width: 40%"><?php echo $addon->author ?></td>
						<td style="width: 90px;"><?php echo $addon->id ?></td>
						</tr><?php
					}

					//TO DO: page number links should also appear at the bottom, probably inside of the grey footer
				?>
			</tbody>
		</table>
	</div>
</div>

<?php include(realpath(dirname(__DIR__) . "/../../private/footer.php")); ?>
