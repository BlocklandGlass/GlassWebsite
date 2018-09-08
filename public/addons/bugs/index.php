<?php
	require dirname(__DIR__) . '/../../private/autoload.php';

  use Glass\AddonManager;
  use Glass\BugManager;
  use Glass\UserLog;

	require_once(realpath(dirname(__DIR__) . "/../../private/lib/Parsedown.php"));

	if(isset($_GET['id'])) {
		try {
			$addonObject = AddonManager::getFromId($_GET['id'] + 0);

      if($addonObject->getDeleted()) {
        include(__DIR__ . "/../deleted.php");
        die();
      } else if($addonObject->isRejected()) {
        include(__DIR__ . "/../rejected.php");
        die();
      }
		} catch(Exception $e) {
			//board doesn't exist
			header('Location: /addons');
			die("addon doesnt exist");
		}
	} else {
		header('Location: /addons');
		die();
	}

  $bugs = BugManager::getAddonBugs($addonObject->getId());
  $open = [];
  $closed = [];
  foreach($bugs as $bug) {
    if($bug->open)
      $open[] = $bug;
    else
      $closed[] = $bug;
  }


	$_PAGETITLE = "Bugs | Blockland Glass";

	include(realpath(dirname(__DIR__) . "/../../private/header.php"));
?>

<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
  ?>
  <span style="font-size: 0.8em; padding-left: 10px">
    <a href="/addons/addon.php?id=<?php echo $addonObject->getId();?>"><?php echo $addonObject->getName(); ?></a> >> <strong>Bugs</strong>
  </span>
  <div class="tile">
    <div style="text-align: right;">
      <a href="new.php?id=<?php echo $addonObject->getId() ?>" class="btn blue" style="margin: 0; padding: 5px 10px; font-size: 0.8em">Open New Issue</a>
    </div>
    <h3>Open Bugs</h3>
		<table style="width: 100%" class="listTable">
	    <thead>
	      <tr>
          <th style="width: 10%">Points</th>
          <th style="width: 35%">Title</th>
          <th style="width: 30%">Submitter</th>
          <th style="width: 25%">Submitted</th>
        </tr>
	    </thead>
      <tbody>
        <?php

        foreach($open as $bug) {

          ?>

          <tr>
            <td style="text-align: center">
              <?php
								$pts = BugManager::getVotes($bug->id);
								$color = '#bbb';
								if($pts > 0) {
									$color = 'rgba(84, 217, 140, 0.5)';
								} else if($pts < 0) {
									$color = 'rgba(237, 118, 105, 0.5)';
								}
								echo '<span style="color: '. $color . '">' . $pts . '</span>';
							?>
            </td>
            <td style="text-align: left">
              <a href="/addons/bugs/view.php?id=<?php echo $bug->id ?>"><?php echo htmlspecialchars($bug->title); ?></a>
            </td>
            <td>
              <?php
                $name = UserLog::getCurrentUsername($bug->blid);
                if($name)
                  echo $name;
                else
                  echo "Blockhead" . $bug->blid
              ?>
            </td>
            <td>
              <?php echo $bug->timestamp; ?>
            </td>
          </tr>

          <?php

        }

        if(sizeof($open) == 0) {
          echo '<tr><td colspan="4" style="text-align: center">No open bugs.</td></tr>';
        }

        ?>
      </tbody>
    </table>

    <h3>Closed Bugs</h3>
		<table style="width: 100%" class="listTable">
	    <thead>
	      <tr>
          <th style="width: 10%">Points</th>
          <th style="width: 35%">Title</th>
          <th style="width: 30%">Submitter</th>
          <th style="width: 25%">Submitted</th>
        </tr>
	    </thead>
      <tbody>
        <?php

        foreach($closed as $bug) {

          ?>

          <tr>
            <td style="text-align: center">
              <?php
								$pts = BugManager::getVotes($bug->id);
								$color = '#bbb';
								if($pts > 0) {
									$color = 'rgba(84, 217, 140, 0.5)';
								} else if($pts < 0) {
									$color = 'rgba(237, 118, 105, 0.5)';
								}
								echo '<span style="color: '. $color . '">' . $pts . '</span>';
							?>
            </td>
            <td style="text-align: left">
              <a href="/addons/bugs/view.php?id=<?php echo $bug->id ?>"><?php echo htmlspecialchars($bug->title); ?></a>
            </td>
            <td>
              <?php
                $name = UserLog::getCurrentUsername($bug->blid);
                if($name)
                  echo $name;
                else
                  echo "Blockhead" . $bug->blid
              ?>
            </td>
            <td>
              <?php echo $bug->timestamp; ?>
            </td>
          </tr>

          <?php

        }

        if(sizeof($closed) == 0) {
          echo '<tr><td colspan="4" style="text-align: center">No closed bugs.</td></tr>';
        }

        ?>
      </tbody>
    </table>
  </div>
</div>
