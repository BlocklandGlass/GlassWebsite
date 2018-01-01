<?php

require dirname(__DIR__) . '/../../private/autoload.php';
use Glass\UserManager;
use Glass\GroupManager;

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

          $lines = explode("\n", $content);
          foreach($lines as $line) {
            if(trim($line) == "")
              continue;

            $fields = explode("\t", $line);
            $time = $fields[0] ?? 0;
            $type = $fields[1] ?? "";

            $time /= 1000;
            $time = date("H:i:s", $time);

            $params = false;
            for($i = 2; $i < sizeof($fields); $i++) {
              if($params !== false)
                $params .= " --- " . $fields[$i];
              else
                $params = $fields[$i];
            }

            echo "<tr><td>$time</td><td>$type</td><td>$params</td></tr>";
          }

        ?>
      </tbody>
    </table>
    <?php } ?>
  </body>
</html>
