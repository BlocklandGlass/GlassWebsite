<?php
	require_once dirname(__DIR__) . "/private/class/GroupManager.php";
	require_once dirname(__DIR__) . "/private/class/UserManager.php";
	require_once dirname(__DIR__) . "/private/class/CronStatManager.php";

	$_PAGETITLE = "Glass | Add-Ons";

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

	$user = UserManager::getCurrent();

  $csm = new CronStatManager();
  $data = $csm->getRecentBlocklandStats(24);
?>
<div class="maincontainer">
	All times US Eastern Time. <i>Displayed Blockland information is not fully accurate. Only users logged in on the hour exactly are recorded. Additionally, only users in servers are accounted for.</i>
  <canvas id="myChart" style="width:400px;height:400px"></canvas>
  <script>
  var ctx = document.getElementById("myChart");
  var myChart = new Chart(ctx, {
      type: 'line',
      data: {
          labels: <?php
					$res = array();
					foreach($data as $time=>$dat) {
						date_default_timezone_set('US/Eastern');
						$res[] = date("g:ia", strtotime($time . " UTC"));
					}
					echo json_encode($res);
					?>,
          datasets: [{
            label: "Players",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(75,192,192,0.4)",
            borderColor: "rgba(75,192,192,1)",
            borderCapStyle: 'round',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(75,192,192,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(75,192,192,1)",
            pointHoverBorderColor: "rgba(220,220,220,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: <?php
            $res = array();
            foreach($data as $time=>$dat) {
              $res[] = $dat->users;
            }
            echo json_encode($res);
            ?>,
          },{
            label: "Servers",
            fill: false,
            lineTension: 0,
            backgroundColor: "rgba(255,0,0,0.4)",
            borderColor: "rgba(255,0,0,0.4)",
            borderCapStyle: 'round',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(255,0,0,0.4)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(255,0,0,0.4)",
            pointHoverBorderColor: "rgba(255,0,0,0.4)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: <?php
            $res = array();
            foreach($data as $time=>$dat) {
              $res[] = $dat->servers;
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
