<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\AddonManager;
	use Glass\GroupManager;
	use Glass\UserManager;

	use Glass\CronStatManager;
	use Glass\StatUsageManager;
	use Glass\StatManager;

	use Glass\SemVer;

	$_PAGETITLE = "Add-On Stats | Blockland Glass";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));

	$user = UserManager::getCurrent();

  $addonObject = AddonManager::getFromId($_GET['id']);

  if(!$user || !$user->inGroup("Administrator")) {
    if($addonObject->getDeleted()) {
      include(__DIR__ . "/../addons/deleted.php");
      die();
    } else if($addonObject->isRejected()) {
      include(__DIR__ . "/../addons/rejected.php");
      die();
    } else if(!$addonObject->getApproved()) {
      include(__DIR__ . "/../addons/unapproved.php");
      die();
    }
  }

  $csm = new CronStatManager();
  //$data = $csm->getRecentAddonUsage($addonObject->getId());

	$dist = StatUsageManager::getDistribution($addonObject->getId());

	$downloadData = StatManager::getHourlyDownloads($addonObject->getId(), 24);
	$downloadData[date("Y-m-d H:i:s")] = StatManager::getStatistics($addonObject->getId());
?>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
	<div class="tile" style="width:calc(100%-15px); font-size: 1.8em">
		Statistics <strong><?php echo htmlspecialchars($addonObject->getName()) ?></strong>
	</div>
	<div class="tile" style="width: calc(50% - 40px); float:left; display: inline-block">
		<strong>Version Usage Chart</strong>
		<hr />
  	<canvas id="myChart" style="width:100%;height:300px"></canvas>
	</div>
	<div class="tile" style="width: calc(50% - 40px); float:right; display: inline-block">
		<strong>Version Usage List</strong>
		<hr />
  	<table class="listTable" style="width: 100%">
			<thead>
				<tr><th>Version</th><!--<th>Release Date</th>--><th>Users</th></tr>
			</thead>
			<tbody>
				<?php
					$ret = array($addonObject->getVersion() . " (stable)");
					if(isset($dist[$addonObject->getVersion()]) && $dist[$addonObject->getVersion()] !== null) {
						$vals = array($dist[$addonObject->getVersion()]);
					} else {
						$vals = array(0);
					}

					foreach($dist as $ver=>$count) {
						if($ver == $addonObject->getVersion()) {
							continue;
						}

						$ret[] = $ver;
						$vals[] = $count;
					}


					$downloadsByVersion = array();
					foreach($ret as $i=>$ver) {
						$downloadsByVersion[$ver] = $vals[$i];
					}

					uksort($downloadsByVersion, function($keyA, $keyB) {
						$wordA = explode(" ", $keyA);
						$wordB = explode(" ", $keyB);
						$verA = new SemVer($wordA[0]);
						$verB = new SemVer($wordB[0]);
						return -($verA->compare($verB));
					});

					foreach($downloadsByVersion as $ver=>$count) {
						echo "<tr>";
						echo "<td>" . $ver . "</td>";
						echo "<td>" . $count . "</td>";
						echo "</tr>";
					}
				?>
			</tbody>
		</table>
	</div>
	<div class="tile" style="padding-top: 10px; display: inline-block; width: calc(100% - 40px)">
		<strong>Downloads</strong>
		<hr />
  	<canvas id="downloads_chart" style=""></canvas>
	</div>
  <script>
  var ctx = document.getElementById("myChart");
	var data = {
	    labels: <?php
				$ret = array("Stable");
				if(isset($dist[$addonObject->getVersion()]) && $dist[$addonObject->getVersion()] !== null) {
					$vals = array($dist[$addonObject->getVersion()]);
				} else {
					$vals = array(0);
				}
				$col = array("#55acee");

				if($addonObject->hasBeta()) {
					$ret[] = "Beta";
					if(isset($dist[$addonObject->getBetaVersion()]) && $addonObject->getBetaVersion() != null) {
						$vals[] = $dist[$addonObject->getBetaVersion()];
					} else {
						$vals[] = 0;
					}
					$col[] = "#2ecc71";
				}

				foreach($dist as $ver=>$count) {
					if($ver == $addonObject->getVersion()) {
						continue;
					}

					if($addonObject->hasBeta()) {
						if($addonObject->getBetaVersion() == $ver) {
							continue;
						}
					}

					$ret[] = $ver;
					$vals[] = $count;
					$col[] = "#e74c3c";
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
	var ctx = document.getElementById("downloads_chart");
  var myChart = new Chart(ctx, {
      type: 'line',
      data: {
          labels: <?php
					$res = array();
					foreach($downloadData as $time=>$dat) {
						date_default_timezone_set('US/Eastern');
						$res[] = date("g:ia", strtotime($time));
					}
					$json = json_encode($res);
					echo ($json == false ? "[]" : $json);
					?>,
          datasets: [{
            label: "Downloads",
            fill: true,
            lineTension: 0.1,
            backgroundColor: "rgba(131, 195, 243, 0.3)",
            borderColor: "#83c3f3",
            borderCapStyle: 'round',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "#83c3f3",
            pointBackgroundColor: "#55acee",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(75,192,192,1)",
            pointHoverBorderColor: "rgba(220,220,220,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 5,
            pointHitRadius: 10,
            data: <?php
            $res = array();
            foreach($downloadData as $time=>$dat) {
              $res[] = $dat->ingameDownloads + $dat->webDownloads;
            }
            echo json_encode($res);
            ?>,
          }]
      },
      options: {
          scales: {
              yAxes: [{
                  ticks: {
                      beginAtZero:false,
											stepSize: 1
                  }
              }]
          }
      }
  });
  </script>
</div>
