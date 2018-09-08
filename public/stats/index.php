<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\AddonManager;
	use Glass\CronStatManager;
	use Glass\GroupManager;
	use Glass\RTBAddonManager;
	use Glass\StatManager;
	use Glass\UserManager;
	use Glass\UserLog;
  use Glass\InstallationManager;

	$_PAGETITLE = "Statistics | Blockland Glass";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));

	$user = UserManager::getCurrent();

 	$csm = new CronStatManager();

	if($user != false && $user->inGroup("Administrator")) {
		if($_GET['clearCache'] ?? false != false) {
			if(InstallationManager::isWindows()) { // future: migrate to apcu entirely?
		    apcu_delete("stats_general");
		  } else {
		    apc_delete("stats_general");
		  }
		}
	}

  if(InstallationManager::isWindows()) { // future: migrate to apcu entirely?
    $stats = apcu_fetch("stats_general", $success);
  } else {
    $stats = apc_fetch("stats_general", $success);
  }

	if(!$success || !$stats) {
		$web = StatManager::getAllAddonDownloads("web")+0;
		$ingame = StatManager::getAllAddonDownloads("ingame")+0;
		$updates = StatManager::getAllAddonDownloads("updates")+0;
		$total = $web+$ingame+$updates;
		$data = $csm->getRecentBlocklandStats(24);

		$users_online_bl = StatManager::getMasterServerStats()['users'];
		$users_online_glass = sizeof(UserLog::getRecentlyActive());
		$users_total = UserLog::getUniqueCount();

		$content_count = AddonManager::getCount();
		$content_updates = AddonManager::getUpdateCount();
		$content_rtb = RTBAddonManager::getCount();
		$content_reclaims = RTBAddonManager::getReclaimedCount();
		$content_creators = AddonManager::getCreatorCount();

		$stats = new stdClass();

		$stats->web                = $web;
		$stats->ingame             = $ingame;
		$stats->updates            = $updates;
		$stats->total              = $total;
		$stats->data               = $data;

		$stats->users_online_bl    = $users_online_bl;
		$stats->users_online_glass = $users_online_glass;
		$stats->users_total        = $users_total;

		$stats->content_count      = $content_count;
		$stats->content_updates    = $content_updates;
		$stats->content_rtb        = $content_rtb;
		$stats->content_reclaims   = $content_reclaims;
		$stats->content_creators   = $content_creators;

    if(InstallationManager::isWindows()) {
      apcu_store("stats_general", $stats, 600);
    } else {
      apc_store("stats_general", $stats, 600);
    }
	} else {
		$web                = $stats->web;
		$ingame             = $stats->ingame;
		$updates            = $stats->updates;
		$total              = $stats->total;
		$data               = $stats->data;

		$users_online_bl    = $stats->users_online_bl;
		$users_online_glass = $stats->users_online_glass;
		$users_total        = $stats->users_total;

		$content_count      = $stats->content_count;
		$content_updates    = $stats->content_updates;
		$content_rtb        = $stats->content_rtb;
		$content_reclaims   = $stats->content_reclaims;
		$content_creators   = $stats->content_creators;
	}

?>
<style>
.list td {
  padding: 10px;
}

.list tr:nth-child(2n+1) td {
  background-color: #fafafa;
}

.list th {
  background-color: #2ecc71;
	padding: 5px 10px;
  color: #fff;
  font-weight: bold;
	font-size: inherit;
}

.list {
  margin: 0 auto;
	width: 100%;
	font-size: 0.8em
}

.maincontainer p {
  text-align: center;
}

form {
  text-align: center;
}

td {
	padding: 5px;
	text-align: center;
}

.flex-container {
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
}

.flex-container > div {
	background-color: #eee;
	padding: 10px;
	margin: 5px;
	flex-basis: calc(400px);
	flex-grow: 1;

	overflow: hidden;
}

.flex-container > div > h3 {
	margin: 0px 10px 10px 10px;
	border-bottom: 2px solid #ddd;
}

.stat-info {
	font-size: 12px;
	text-align: center;
	margin-top:10px;
	padding: 10px;
}
</style>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
	<div class="flex-container">
		<div>
			<h3>File Downloads</h3>
			<table class="list">
				<tbody>
					<tr>
						<th style="width: 30%">Type</th>
						<th>Downloads</th>
					</tr>
					<tr>
						<td><strong>Website:</strong></td>
						<td><?php echo number_format($web) ?></td>
					</tr>
					<tr>
						<td><strong>In-game:</strong></td>
						<td><?php echo number_format($ingame) ?></td>
					</tr>
					<tr>
						<td><strong>Updates:</strong></td>
						<td><?php echo number_format($updates) ?></td>
					</tr>
					<tr>
						<td><strong>Total:</strong></td>
						<td><?php echo number_format($total) ?></td>
					</tr>
				</tbody>
			</table>
			<div class="stat-info">
				<i>Blockland content is served through both the website and our in-game Mod Manager platform. We also provide in-game add-on updates.</i>
			</div>
		</div>
		<div>
			<h3>Blockland Activity</h3>
			<canvas id="myChart" style="height:400px; background-color:#fafafa"></canvas>
			<div class="stat-info">
				<i>All times are Eastern Standard Time. Displayed Blockland information is not fully accurate. Only users logged in on the hour exactly are recorded. Additionally, only users in servers are accounted for.</i>
			</div>
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
							responsive: true,
		          scales: {
		              yAxes: [{
		                  ticks: {
		                      beginAtZero:true
		                  }
		              }]
		          }
		      }
		  });

			let parentEventHandler = Chart.Controller.prototype.eventHandler;
			Chart.Controller.prototype.eventHandler = function () {
			    let ret = parentEventHandler.apply(this, arguments);

			    let x = arguments[0].x;
			    let y = arguments[0].y;
			    this.clear();
			    this.draw();
			    let yScale = this.scales['y-axis-0'];
			    this.chart.ctx.beginPath();
			    this.chart.ctx.moveTo(x, yScale.getPixelForValue(yScale.max));
			    this.chart.ctx.strokeStyle = "#ff0000";
			    this.chart.ctx.lineTo(x, yScale.getPixelForValue(yScale.min));
			    this.chart.ctx.stroke();

			    return ret;
			};
		  </script>
		</div>
		<div>
			<h3>Users</h3>
			<table class="list">
				<tbody>
					<tr>
						<th style="width: 40%">Type</th>
						<th>Users</th>
					</tr>
					<tr>
						<td><strong>Online Blockland:</strong></td>
						<td><?php echo number_format($users_online_bl); ?></td>
					</tr>
					<tr>
						<td><strong>Online Glass:</strong></td>
						<td><?php echo number_format($users_online_glass); ?></td>
					</tr>
					<tr>
						<td><strong>Total Glass:</strong></td>
						<td><?php echo number_format($users_total); ?></td>
					</tr>
				</tbody>
			</table>
			<div class="stat-info">
				<i>At times, the number of users online Glass may be higher than users online Blockland. Only users in Blockland servers are counted, and there may be more players using Glass than actually in servers.</i>
			</div>
		</div>
		<div style="">
			<h3>Content</h3>
			<table class="list">
				<tbody>
					<tr>
						<th style="width: 40%">Type</th>
						<th>Number</th>
					</tr>
					<tr>
						<td><strong>Glass Add-Ons:</strong></td>
						<td><?php echo number_format($content_count); ?></td>
					</tr>
					<tr>
						<td><strong>Updates Delivered:</strong></td>
						<td><?php echo number_format($content_updates); ?></td>
					</tr>
					<tr>
						<td><strong>RTB Add-Ons:</strong></td>
						<td><?php echo number_format($content_rtb); ?></td>
					</tr>
					<tr>
						<td><strong>RTB Add-Ons Reclaimed:</strong></td>
						<td><?php echo number_format($content_reclaims); ?></td>
					</tr>
					<tr>
						<td><strong>Content Creators:</strong></td>
						<td><?php echo number_format($content_creators); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
