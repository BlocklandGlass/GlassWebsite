<?php

require dirname(__DIR__) . '/../../private/autoload.php';
use Glass\UserManager;
use Glass\GroupManager;
use Glass\UserLog;

$user = UserManager::getCurrent();

$allowed = $user->inGroup("Administrator") || $user->inGroup("Moderator");

if(!$user || !$allowed) {
  header('Location: /login.php?redirect=' . urlencode("/admin/live/"));
  return;
}

$id = $_GET['id'] ?? false;

if($id === false) {
  header('Location: /admin/live/');
  return;
}

$json = file_get_contents(dirname(__FILE__) . '/config.json');
if($json === false) {
  die('Config Missing');
}

$config = json_decode($json);
if($config === false) {
  die('Config Invalid');
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
    if($type == "join" || $type == "leave" || $type == "msg") {
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
        $string = $blid . " ($un, " . $usernames[$blid] . ") joined";
        break;

      case "msg":
        $blid = $data->params[0];
        $msg = $data->params[1];
        $string = $blid . " (" . $usernames[$blid] . "): $msg";
        break;

      case "exit":
        $blid = $data->params[0];
        $un = $data->params[1];
        $string = $blid . " ($un, " . $usernames[$blid] . ") exited";
        break;

      default:
        $string = "soon (tm)";
        break;
    }
    $data->string = $string;
  }
}

?>
<html>
  <head>
    <title>Room Log</title>
    <style>
      th {
        font-weight: bold;
        padding: 0 20px;
      }

      td {
        font-family: monospace;
      }

      .params-join {
        font-style: italic;
        color: rgb(0, 150, 0);
      }

      .params-exit {
        font-style: italic;
        color: rgb(200, 0, 0);
      }
    </style>
  </head>
  <body>
    <a href="/admin/live/"><< Back</a>
    <h2>Room Log - Room <?php echo $id; ?></h2>
    <h3><?php echo $date; ?></h3>

    <?php
      $dateTime = strtotime($date);
      $yesterday = date('Y-m-d', $dateTime - (24 * 60 * 60));
      echo "<a href=\"?id=$id&date=$yesterday\">$yesterday</a>";
      echo "<br />";
      $tomorrow = date('Y-m-d', $dateTime + (24 * 60 * 60));
      echo "<a href=\"?id=$id&date=$tomorrow\">$tomorrow</a>";
    ?>

    <hr />
    <?php
      if($content === false) {
        echo "<b>No data for $date</b>";
      } else {
    ?>
    <table>
      <thead>
        <tr>
          <th>Time</th><th>Type</th><th>Params</th>
        </tr>
      </thead>
      <tbody>
        <?php

          foreach($datas as $data) {
            $time = $data->time;
            $type = $data->type;
            $string = $data->string;

            $class = "params-$type";
            echo "<tr><td>$time</td><td>$type</td><td class=\"$class\">$string</td></tr>";
          }

        ?>
      </tbody>
    </table>
    <?php } ?>
  </body>
</html>
