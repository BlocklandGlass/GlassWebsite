<?php
	require_once dirname(__DIR__) . "/private/class/GroupManager.php";
	require_once dirname(__DIR__) . "/private/class/UserManager.php";
	require_once dirname(__DIR__) . "/private/class/CronStatManager.php";
	require_once dirname(__DIR__) . "/private/class/StatUsageManager.php";

	$_PAGETITLE = "Glass | Add-On Stats";

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

	$user = UserManager::getCurrent();

  $addon = AddonManager::getFromId($_GET['id']);

  $csm = new CronStatManager();
  //$data = $csm->getRecentAddonUsage($addon->getId());

	$dist = StatUsageManager::getDistribution($addon->getId());
?>
<div class="maincontainer">
  <canvas id="myChart" style="width:400px;height:400px"></canvas>
  <script>
  var ctx = document.getElementById("myChart");
	var data = {
	    labels: <?php
				$ret = array("Stable");
				if(isset($dist[$addon->getVersion()])) {
					$vals = array($dist[$addon->getVersion()]);
				} else {
					$vals = array(0);
				}
				$col = array("#36A2EB");

				if($addon->hasBeta()) {
					$ret[] = "Beta";
					$vals[] = $dist[$addon->getBetaVersion()];
					$col[] = "#FFCE56";
				}

				foreach($dist as $ver=>$count) {
					if($ver == $addon->getVersion()) {
						continue;
					}

					if($addon->hasBeta()) {
						if($addon->getBetaVersion() == $ver) {
							continue;
						}
					}

					$ret[] = $ver;
					$vals[] = $count;
					$col[] = "#FFCE56";
				}
				echo json_encode($ret);
			?>,
	    datasets: [
	        {
	            data: <?php echo json_encode($vals) ?>,
	            backgroundColor: <?php echo json_encode($col) ?>,
	            hoverBackgroundColor: <?php echo json_encode($col) ?>
	        }]
	};

	var myDoughnutChart = new Chart(ctx, {
	    type: 'doughnut',
	    data: data,
			animation:{
        animateScale:true
    	},
	    options: {
				cutoutPercentage: 50
			}
	});
  </script>
</div>
