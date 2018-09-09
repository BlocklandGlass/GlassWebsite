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

if(!file_exists(dirname(__FILE__) . '/config.json')) {
  die('Config Missing');
}

$json = file_get_contents(dirname(__FILE__) . '/config.json');

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
    <title>Glass Live Logs | Blockland Glass</title>
  </head>
  <body>
    <h2>Glass Live Logs</h2>
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
      <input type="text" placeholder="Not finished." disabled />
      <input type="submit" value="Search" disabled />
    </form>
  </body>
</html>
