<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\AddonManager;
	use Glass\GroupManager;
	use Glass\UserManager;

	use Glass\CronStatManager;
	use Glass\StatUsageManager;
	use Glass\StatManager;

	use Glass\SemVer;

	$_PAGETITLE = "Blockland Glass | Add-On Stats";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));

	$user = UserManager::getCurrent();

  $addon = AddonManager::getFromId($_GET['id']);

  $csm = new CronStatManager();
  //$data = $csm->getRecentAddonUsage($addon->getId());

	$dist = StatUsageManager::getDistribution($addon->getId());

	$downloadData = StatManager::getHourlyDownloads($addon->getId(), 24);
	$downloadData[date("Y-m-d H:i:s")] = StatManager::getStatistics($addon->getId());
?>
<div class="maincontainer">
	<div class="tile">
		<h2><a href="/addons/addon.php?id=<?php echo $addon->getId(); ?>"><?php echo $addon->getName(); ?></a></h2>Statistics
	</div>
	<div class="tile" style="width: calc(50% - 40px); float:left; display: inline-block">
		<b>Version Usage Chart</b>
		<hr />
  	<canvas id="myChart" style="width:100%;height:300px"></canvas>
	</div>
	<div class="tile" style="width: calc(50% - 40px); float:right; display: inline-block">
		<b>Version Usage List</b>
		<hr />
  	<table class="listTable" style="width: 100%">
			<thead>
				<tr><th>Version</th><!--<th>Release Date</th>--><th>Users</th></tr>
			</thead>
			<tbody>
				<?php
					$ret = array($addon->getVersion() . " (stable)");
					if(isset($dist[$addon->getVersion()]) && $dist[$addon->getVersion()] !== null) {
						$vals = array($dist[$addon->getVersion()]);
					} else {
						$vals = array(0);
					}

					foreach($dist as $ver=>$count) {
						if($ver == $addon->getVersion()) {
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
						return $verA->compare($verB);
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
		<b>Downloads</b>
		<hr />
  	<canvas id="downloads_chart" style=""></canvas>
	</div>
  <script>
  var ctx = document.getElementById("myChart");
	var data = {
	    labels: <?php
				$ret = array("Stable");
				if(isset($dist[$addon->getVersion()]) && $dist[$addon->getVersion()] !== null) {
					$vals = array($dist[$addon->getVersion()]);
				} else {
					$vals = array(0);
				}
				$col = array("#55acee");

				if($addon->hasBeta()) {
					$ret[] = "Beta";
					if(isset($dist[$addon->getBetaVersion()]) && $addon->getBetaVersion() != null) {
						$vals[] = $dist[$addon->getBetaVersion()];
					} else {
						$vals[] = 0;
					}
					$col[] = "#2ecc71";
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
                      beginAtZero:true
                  }
              }]
          }
      }
  });
  </script>
</div>
