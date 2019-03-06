<h2>Room Logs</h2>

<?php
  use Glass\UserLog;

  if(!$user->inGroup("Administrator") && !$user->inGroup("Moderator")) {
    die('You do not have permission to access this area.');
  }

  $id = $_GET['id'] ?? false;

  if($id === false) {
    header('Location: /admin/?tab=rooms');
    return;
  }

  if(!file_exists(dirname(__FILE__) . '/../live/config.json')) {
    die('config.json is missing.');
  }

  $json = file_get_contents(dirname(__FILE__) . '/../live/config.json');

  $config = json_decode($json);
  if($config === false) {
    die('config.json is invalid.');
  }

  $path = $config->dir . 'room/' . $id . '/';

  $date = $_GET['date'] ?? false;
  if($date === false) {
    $date = date("Y-m-d");
  }

  $file = $path . $date . '.log';

  if(is_file($file))
    $content = file_get_contents($file);
  else
    $content = false;

  if($content !== false) {
    $datas = [];
    $blids = [];
    $lines = explode("\n", $content);

    // parse
    foreach($lines as $line) {
      if(trim($line) == "")
        continue;

      $fields = explode("\t", $line);
      $time = $fields[0] ?? 0;
      $type = $fields[1] ?? "";

      $time /= 1000;
      $time = date("H:i:s", $time);

      $data = new stdClass();
      $data->time = $time;
      $data->type = $type;
      $data->params = [];

      for($i = 2; $i < sizeof($fields); $i++) {
        $data->params[$i-2] = $fields[$i];
      }

      $datas[] = $data;

      // scan for blids
      if($type == "join" || $type == "exit" || $type == "msg") {
        $blids[] = $data->params[0];
      }
    }

    $blids = array_unique($blids);
    $usernames = UserLog::getUsernames($blids);

    // string building
    foreach($datas as $data) {
      $string = "";
      switch($data->type) {
        case "join":
          $blid = $data->params[0];
          $un = $data->params[1];
          $un2 = htmlspecialchars(utf8_encode($usernames[$blid]));
          if($un != $un2) {
            $string = $blid . " ($un, $un2) joined";
          } else {
            $string = $blid . " ($un) joined";
          }
          break;

        case "msg":
          $blid = $data->params[0];
          $msg = $data->params[1];
          $un2 = htmlspecialchars(utf8_encode($usernames[$blid]));
          $string = $blid . " ($un2): $msg";
          break;

        case "exit":
          $blid = $data->params[0];
          $un = $data->params[1];
          $un2 = htmlspecialchars(utf8_encode($usernames[$blid]));
          if($un != $un2) {
            $string = $blid . " ($un, $un2) exited";
          } else {
            $string = $blid . " ($un) exited";
          }
          break;

        case "sys":
          $string = $data->params[0];
          break;

        case "mod":
          $string = $data->params[0];
          break;

        case "bot":
          $msg = $data->params[0];
          $string = "$msg";
          break;

        default:
          $string = "Unhandled type.";
          break;
      }
      $data->string = $string;
    }
  }
?>

<style>
  table {
    background-color: white;
    width: 100%;
  }

  table th {
    font-weight: bold;
    padding: 5px;
  }

  table td {
    font-family: monospace;
    text-align: center;
  }

  table td:nth-child(3) {
    text-align: left;
    word-break: break-word;
  }

  table tr td.type {
    font-style: italic;
  }

  table tr td.params-msg {
    font-style: normal;
  }

  table tr td.params-join {
    color: rgb(0, 150, 0);
  }

  table tr td.params-exit {
    color: rgb(200, 0, 0);
  }

  table tr td.params-sys {
    font-weight: bold;
    color: rgb(0, 0, 150);
  }

  table tr td.params-mod {
    font-weight: bold;
    color: rgb(175, 100, 0);
  }

  table tr td.params-bot {
    color: rgb(150, 0, 150);
  }

  table tr td.params-unhandled {
    color: rgb(150, 150, 150);
  }
</style>

<h3>Room <?php echo "#" . $id; ?></h3>
<p>Displaying room log for the date of <?php echo $date; ?>.</p>

<?php
  $dateTime = strtotime($date);
  $yesterday = date('Y-m-d', $dateTime - (24 * 60 * 60));
  echo "<a href=\"?tab=room&id=$id&date=$yesterday\">↑ $yesterday</a><br>";
  $tomorrow = date('Y-m-d', $dateTime + (24 * 60 * 60));
  echo "<a href=\"?tab=room&id=$id&date=$tomorrow\">↓ $tomorrow</a>";
?>

<hr />
<?php
  if($content === false) {
    echo "<strong>No data found for $date.</strong>";
  } else {
?>
<table>
  <thead>
    <tr>
      <th>Time</th><th>Type</th><th>Log Entry</th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($datas as $data) {
        $time = $data->time;
        $type = $data->type;
        $string = $data->string;

        if($string == "Unhandled type.") {
          $class = "params-unhandled";
        } else {
          $class = "params-$type";
        }

        echo "<tr><td>$time</td><td>$type</td><td class=\"type $class\">$string</td></tr>";
      }
    ?>
  </tbody>
</table>
<?php } ?>

<hr>

<?php
  $tomorrow = date('Y-m-d', $dateTime + (24 * 60 * 60));
  echo "<a href=\"?tab=room&id=$id&date=$tomorrow\">↓ $tomorrow</a>";
?>
