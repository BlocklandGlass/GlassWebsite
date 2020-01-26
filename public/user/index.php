<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';
  $_PAGETITLE = "Your Account | Blockland Glass";
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
<style>
  .status {
    background-color: black;
    color: black;
    padding: 4px;
    margin-left: 20px;
    border-radius: 0;
    text-align: center;
    display: inline-block;
    font-weight: bold;
  }

  .status.deleted {
    background-color: lightgray;
  }

  .status.approved {
    background-color: yellowgreen;
    color: white;
  }

  .status.rejected {
    background-color: coral;
  }

  .status.awaiting-review {
    background-color: gold;
  }
</style>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
  <div class="tile" style="font-size: 3rem;">
    Your Account
  </div>
	<div class="tile">
    <h2>Info</h2>
    <ul>
      <li><a href="view.php?blid=<?php echo $userObject->getBLID(); ?>">View your public profile.</a></li>
      <li><a href="sessions.php">View your account activity.</a></li>
    </ul>
	</div>
	<div class="tile">

		<h2 style="width: 50%; display:inline-block; float:left;">Uploaded Content</h2>
		<a class="btn blue" href="/addons/upload/upload.php" style="font-size: 1em; float:right; margin: 0; margin-bottom: 20px; padding: 10px 15px;">
			Upload New Add-On
		</a>
		<table class="listTable" style="width: 100%">
			<thead>
				<tr>
					<th></th>
					<th style="text-align: left !important">Title</th>
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

            echo '<td><img src="/img/icons32/' . $board->getIcon() . '.png"/></td>';

            if($ao->getDeleted()) {
              $status = '<div class="status deleted">Deleted</div>';
            } else if($ao->getApproved()) {
              $up = AddonManager::getUpdates($ao);

              if(count($up) > 0) {
                $up = $up[0];
              }

              if($up != null) {
                $version = $up->getVersion();

                if($up->isPending()) {
                  $status = '<div class="status awaiting-review">Pending Review (v' . $version . ')</div>';
                } else if($up->isRejected()) {
                  $status = '<div class="status rejected">Rejected (v' . $version . ')</div>';
                } else {
                  $status = '<div class="status approved">Approved (v' . $version . ')</div>';
                }
              } else {
                $status = '<div class="status approved">Approved</div>';
              }
            } else if($ao->isRejected()) {
              $status = '<div class="status rejected">Rejected</div>';
            } else {
              $status = '<div class="status awaiting-review">Pending Review</div>';
            }

            echo '<td style="text-align: left !important"><a href="/addons/addon.php?id=' . $ao->getId() . '"><span style="font-size: 1.2em; font-weight:bold;">' . $ao->getName() . '</span></a>' . $status . '</td>';

            echo '<td>' . ($ao->getDownloads('web')+$ao->getDownloads('ingame')) . '</td>';

            echo '
            <td style="font-size: 0.8em;">';

            if($ao->isRejected()) {
              // todo: resubmission
              // echo '<a href="#" class="btn small green">Resubmit</a>';
              echo '<a href="/addons/update.php?id=' . $ao->getId() . '" class="btn small green">Update</a>';
            } else {
              echo '<a href="/addons/update.php?id=' . $ao->getId() . '" class="btn small green">Update</a>';
            }

            echo '
              <a href="/addons/manage.php?id=' . $ao->getId() . '" class="btn small blue">Manage</a>
              <a href="/stats/addon.php?id=' . $ao->getId() . '" class="btn small purple">Stats</a>
              <a href="/addons/delete.php?id=' . $ao->getId() . '" class="btn small red">Delete</a>
            </td>
            ';

            echo '</tr>';
          }
        }
			?>

			</tbody>
		</table>
	</div>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
