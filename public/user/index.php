<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';
	session_start();

	if(!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
		header("Location: /login.php");
		die();
	}
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
	use Glass\UserManager;
	use Glass\AddonManager;
	use Glass\BuildManager;
	use Glass\BuildObject;
	use Glass\BoardObject;
	use Glass\NotificationManager;
	use Glass\NotificationObject;
	$userObject = UserManager::getCurrent();

	if($userObject === false) {
		header('Location: verifyAccount.php');
		die();
	}

?>
<div class="maincontainer">
	<span style="font-size: 1.5em;">Hey there, <b><?php echo $_SESSION['username']; ?></b></span>
	<table class="userhome">
		<tbody>
			<tr>
				<td style="width: 50%">
					<div class="tile">
						<h2>Recent Activity</h2>
						<?php
						$notifications = NotificationManager::getFromBLID($userObject->getBLID(), 0, 10); // TODO NotifcationManager::getFromUser(9789, 10);

						if($notifications !== false) {
							foreach($notifications as $noteId) {
								$noteObject = NotificationManager::getFromId($noteId);
								echo '<div style="background-color: #eee; border-radius: 15px; padding: 15px; margin: 5px;">';
								echo $noteObject->toHTML();
								echo '<br /><span style="font-size: 0.8em;">' . date("M jS Y, g:i A", strtotime($noteObject->getDate())) . '</span>';
								echo '</div>';
							}
						}
						?>
					</div>
				</td>
				<td>
					<div class="tile">

						<h2>My Content</h2>
						<div class="useraddon shadow-1" style="text-align:center; background-color: #ccffcc">
							<img style="width: 1.2em;" src="http://blocklandglass.com/img/icons32/inbox_upload.png" alt="New"/> <a href="/addons/upload/upload.php">Upload New Add-On</a>
						</div>
						<?php
						$addons = AddonManager::getFromBLID($userObject->getBLID(),0,9999);

						foreach($addons as $aid) {
							$ao = AddonManager::getFromId($aid);
							$board = $ao->getBoard();
							$html = "";
							if(!$ao->getApproved()) {
								$html = '<img style="width: 1.2em;" src="http://blocklandglass.com/img/icons32/hourglass.png" alt="Under Review"/> ';
							}
							?>
							<div class="useraddon">
								<?php echo $html ?><a href="/addons/addon.php?id=<?php echo $ao->getId(); ?>"><span style="font-size: 1.2em; font-weight:bold;"><?php echo $ao->getName(); ?></span></a>
								<br />
								<span style="font-size: 0.8em;">
									<a href="/addons/update.php?id=<?php echo $ao->getId(); ?>">Update</a> | <a href="/addons/manage.php?id=<?php echo $ao->getId(); ?>">Manage</a> | <a href="#">Delete</a>
								</span>
							</div>
							<?php
						}

						?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
