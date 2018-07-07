<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';
	session_start();

	if(!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
		header("Location: /login.php");
		die();
	}
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	use Glass\UserManager;
	use Glass\AddonManager;
	use Glass\BoardManager;
	use Glass\NotificationManager;
	use Glass\NotificationObject;
	$userObject = UserManager::getCurrent();

	if($userObject === false) {
		header('Location: verifyAccount.php');
		die();
	}

?>

<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
	<div class="tile">

		<h2 style="width: 50%; display:inline-block; float:left;">Your Content</h3>
		<a class="btn green" href="/addons/upload/upload.php" style="font-size: 1em; float:right; margin: 0; margin-bottom: 20px; padding: 10px 15px;">
			Upload New Add-On
		</a>
		<table class="listTable" style="width: 100%">
			<thead>
				<tr>
					<th></th>
					<th style="text-align: left !important">Title</th>
					<th>Status</th>
					<th>Downloads</th>
					<th>Options</th>
				</tr>
			</thead>
			<tbody>

			<?php
				$aids = AddonManager::getFromBLID($userObject->getBLID(),["approved"=>false, "deleted"=>false]);
				foreach($aids as $aid) {
					$addons[] = AddonManager::getFromId($aid);
				}

        if(empty($addons)) {
          echo '<tr><td colspan="5" style="text-align: center">No uploaded content.</td></tr>';
        } else {
          usort($addons, function($a, $b) {
            if($a->getDeleted()) {
              $statA = 1;
            } else if($a->getApproved()) {
              $statA = 2;
            } else {
              $statA = 3;
            }

            if($b->getDeleted()) {
              $statB = 1;
            } else if($b->getApproved()) {
              $statB = 2;
            } else {
              $statB = 3;
            }

            if($statA > $statB) {
              return -1;
            } else if($statA < $statB) {
              return 1;
            }

            return strtotime($b->getUploadDate())-strtotime($a->getUploadDate());
          });

          foreach($addons as $ao) {
            $board = BoardManager::getFromId($ao->getBoard());
            echo '<tr>';
            if(!$ao->getApproved()) {
              echo '<td><img style="width: 1.2em;" src="https://blocklandglass.com/img/icons32/hourglass.png" alt="Under Review"/></td>';
            } else {
              echo '<td><img style="width: 1.2em;" src="https://blocklandglass.com/img/icons32/' . $board->getIcon() . '.png"/></td>';
            }

            echo '<td style="text-align: left !important"><a href="/addons/addon.php?id=' . $ao->getId() . '"><span style="font-size: 1.2em; font-weight:bold;">' . $ao->getName() . '</span></a></td>';

            if($ao->getDeleted()) {
              echo '<td>Deleted</td>';
            } else if($ao->getApproved()) {
              echo '<td>Approved</td>';
            } else if($ao->isRejected()) {
              echo '<td>Rejected</td>';
            } else {
              echo '<td>Under Review</td>';
            }

            echo '<td>' . ($ao->getDownloads('web')+$ao->getDownloads('ingame')) . '</td>';

            ?>
            <td style="font-size: 0.8em;">
              <a href="/addons/update.php?id=<?php echo $ao->getId(); ?>">Update</a> |
              <a href="/addons/manage.php?id=<?php echo $ao->getId(); ?>">Manage</a> |
              <a href="/stats/addon.php?id=<?php echo $ao->getId(); ?>">Stats</a> |
              <a href="/addons/delete.php?id=<?php echo $ao->getId(); ?>">Delete</a>
            </td>
            <?php
            echo '</tr>';
          }
        }
			?>

			</tbody>
		</table>
	</div>

	<div class="tile" style="text-align: center">
		<a href="sessions.php">View Account Activity</a>
	</div>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
