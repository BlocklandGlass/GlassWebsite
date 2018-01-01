<?php

require dirname(__DIR__) . '/../../private/autoload.php';
use Glass\UserManager;
use Glass\GroupManager;

$user = UserManager::getCurrent();

if(!$user || !$user->inGroup("Administrator")) {
  header('Location: /login.php?redirect=' . urlencode("/admin/live/"));
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

function scanRooms() {
  global $config;
  $dir = $config->dir . 'room/';

  $res = scandir($dir);

  $rooms = [];
  foreach($res as $path) {
    if(!is_numeric($path))
      continue;

    $rooms[] = $path;
  }

  return $rooms;
}

?>
<!doctype html>
<html>
  <head>
    <title>Glass Live Logs</title>
  </head>
  <body>
    <h2>Glass Live Archives</h2>
    <hr />
    <h3>Rooms</h3>

    <ul>
      <?php

      $rooms = scanRooms();
      foreach($rooms as $room) {
        $name = $config->rooms->$room ?? "Room $room";
        echo "<li><a href=\"room.php?id=$room\">$name</a></li>";
      }

      ?>
    </ul>

    <hr />
    <h3>User Lookup</h3>
    Enter BL_ID:
    <form>
      <input type="text" />
      <input type="submit" value="Search"/>
    </form>
  </body>
</html>
