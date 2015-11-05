<?php
	//require this one since we need to make sure session_start() is called
	require(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/AddonManager.php"));
	//require_once(realpath(dirname(__DIR__) . "/private/class/BoardObject.php"));
	$userObject = UserManager::getFromId($_SESSION['uid']);
?>
<div class="maincontainer">
	<span style="font-size: 1.5em;">Hey there, <b><?php echo $_SESSION['username']; ?></b></span>
	<table class="userhome">
		<tbody>
			<tr>
				<td style="width: 50%">
					<p>
						<h3>Recent Activity</h3>
						<div style="background-color: #eee; border-radius: 15px; padding: 15px; margin: 5px;"><a href="#">Jincux</a> commented on <a href="#">Blockland Glass</a><br /><span style="font-size: 0.8em;">Yesterday, 4:20pm</span></div>
						<div style="background-color: #eee; border-radius: 15px; padding: 15px; margin: 5px;">You were promoted to <b>Administrator</b><br /><span style="font-size: 0.8em;">Yesterday, 11:48am</span></div>
					</p>
				</td>
				<td>
					<p>
						<h3>My Add-Ons</h3>
						<?php
						$addons = AddonManager::getFromAuthor($userObject);
						foreach($addons as $ao) {
							$board = $ao->getBoard();
							?>
							<div class="useraddon">
								<a href="/addons/addon.php?id=<?php echo $ao->getId(); ?>"><img style="width: 1.2em;" src="http://blocklandglass.com/icon/icons32/<?php echo $board["icon"] ?>.png" /> <span style="font-size: 1.2em; font-weight:bold;"><?php echo $ao->getName(); ?></span></a>
								<br />
								<span style="font-size: 0.8em;">
									<a href="#">Update</a> | <a href="#">Edit</a> | <a href="#">Repository</a> | <a href="#">Delete</a>
								</span>
							</div>
							<?php
						} ?>
						<div class="useraddon">
							<a href="#"><img style="width: 1.2em;" src="http://blocklandglass.com/icon/icons32/wrench.png" /> <span style="font-size: 1.2em; font-weight:bold;">Blockland Glass</span></a>
							<br />
							<span style="font-size: 0.8em;">
								<a href="#">Update</a> | <a href="#">Edit</a> | <a href="#">Repository</a> | <a href="#">Delete</a>
							</span>
						</div>
						<div class="useraddon">
							<a href="#"><img style="width: 1.2em;" src="http://blocklandglass.com/icon/icons32/gun.png" /> <span style="font-size: 1.2em; font-weight:bold;">Weapon Pack</span></a>
							<br />
							<span style="font-size: 0.8em;">
								<a href="#">Update</a> | <a href="#">Edit</a> | <a href="#">Repository</a> | <a href="#">Delete</a>
							</span>
						</div>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
