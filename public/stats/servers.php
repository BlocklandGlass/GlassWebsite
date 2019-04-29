<?php
	require dirname(__DIR__) . '/../private/autoload.php';
	use Glass\GroupManager;
	use Glass\UserManager;
	use Glass\ServerTracker;

	$_PAGETITLE = "Current Servers | Blockland Glass";

	include(realpath(dirname(__DIR__) . "/../private/header.php"));

	$servers = ServerTracker::getActiveServers();
?>
<style>
  .listTable {
    margin: 0 auto;
  }

  .maincontainer p {
    text-align: center;
  }
</style>
<div class="maincontainer">
	<?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
  <div class="navcontainer darkgreen">
    <div class="navcontent">
      <ul>
        <li><a class="navbtn" href="/stats/servers.php">Current Servers</a></li>
        <li><a class="navbtn" href="/stats/users.php">Current Users</a></li>
      </ul>
    </div>
  </div>
  <p>This page displays a list of servers on Blockland running Blockland Glass right now.<br>
  It is not indicative of the entire Glass or Blockland serverbase.</p>
  <?php
   if(sizeof($servers) > 0) {
     foreach($servers as $s) {
      echo "<div class=\"tile\" style=\"width: 50%; margin: 0 auto; margin-bottom: 10px\"><h3 style=\"padding-bottom: 0; margin-bottom: 0\">" . utf8_encode($s->host) . "'s Server</h3>";
      $addr = $s->ip . ":" . $s->port;

      echo '<a href="blockland://' . $addr . '">Join (' . $addr . ')</a><hr />';

      $clients = json_decode($s->clients);
      $str = "";

      echo '<table class="listTable" style="width: 100%">'
          . '<thead>'
          . '<tr>'
          . '<th style="width: 30px;"> </th>'
          . '<th>Username</th>'
          . '<th>BL_ID</th>'
          . '<th>Glass</th>'
          . '</tr></thead><tbody>';

      if(sizeof($clients) > 0 && $clients[0]->name != "") {
        foreach($clients as $cl) {
          //$name = utf8_encode($cl->name);
          $name = $cl->name;

          if($cl->status == "")
            $cl->status = "-";

          echo '<tr>';
          echo '<td style="width: 30px; text-align: center">' . $cl->status . '</td>';
          echo '<td style="text-align: left">' . $name . '</td>';
          echo '<td>' . $cl->blid . '</td>';
          echo '<td>' . ($cl->version == "" ? "No" : "Yes") . '</td>';
          echo '</tr>';
        }
        echo "</tr>";
      } else {
        echo '<tr><td colspan="4" style="text-align:center">No users!</td></tr>';
      }
      echo '</tbody></table>';
      echo '</div>';
   }
  } else {
    echo '<p><strong>No servers are currently online running Blockland Glass.</strong></p>';
  }
  ?>
</div>
<?php
	include(realpath(dirname(__DIR__) . "/../private/footer.php"));
?>
