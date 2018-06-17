<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\GroupManager;
	use Glass\UserManager;
	use Glass\CronStatManager;
	use Glass\StatManager;

	$_PAGETITLE = "Blockland Glass | Statistics";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));

	$user = UserManager::getCurrent();

	$web = StatManager::getAllAddonDownloads("web")+0;
	$ingame = StatManager::getAllAddonDownloads("ingame")+0;
	$updates = StatManager::getAllAddonDownloads("updates")+0;
	$total = $web+$ingame+$updates;

  $csm = new CronStatManager();
  $data = $csm->getRecentBlocklandStats(24);
?>
<style>
.list td {
  padding: 10px;
}

.list tr:nth-child(2n+1) td {
  background-color: #ddd;
}

.list tr:first-child td {
  background-color: #777;
  color: #fff;
  font-weight: bold;
}

.list tr td:first-child {
  border-radius: 10px 0 0 10px;
}

.list tr td:last-child {
  border-radius: 0 10px 10px 0;
}

.list {
  margin: 0 auto;
}

.maincontainer p {
  text-align: center;
}

form {
  text-align: center;
}
</style>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php")); #636
  ?>
	<style>
		td {
			padding: 5px;
			font-size: 0.8em
		}
	</style>
	<table class="list">
		<tbody>
			<tr>
				<td>Type</td>
				<td>Downloads</td>
			</tr>
			<tr><td><b>Web:</b></td><td><?php echo $web ?></td></tr>
			<tr><td><b>In-game:</b></td><td><?php echo $ingame ?></td></tr>
			<tr><td><b>Update:</b></td><td><?php echo $updates ?></td></tr>
			<tr><td><b>Total:</b></td><td><?php echo $total ?></td></tr>
			<!--<tr><td><b>Since Glass 2:</b></td><td><?php echo $total-38535; ?></td></tr>-->
		</tbody>
	</table>
	<hr />
	<i>All times US Eastern Time. Displayed Blockland information is not fully accurate. Only users logged in on the hour exactly are recorded. Additionally, only users in servers are accounted for.</i>
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
