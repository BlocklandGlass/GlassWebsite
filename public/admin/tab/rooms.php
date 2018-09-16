<h1>Room Logs</h1>

<?php
	if(!$user->inGroup("Administrator") && !$user->inGroup("Moderator")) {
    die('You do not have permission to access this area.');
  }

  if(!file_exists(dirname(__FILE__) . '/../live/config.json')) {
    die('Config missing.');
  }

  $json = file_get_contents(dirname(__FILE__) . '/../live/config.json');

  $config = json_decode($json);
  if($config === false) {
    die('Config invalid.');
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

<h2>Rooms</h2>

<ul>
  <?php
    $rooms = scanRooms();
    foreach($rooms as $room) {
      $name = $config->rooms->$room ?? "Room $room";
      echo "<li><a href=\"?tab=room&id=$room\">$name</a></li>";
    }
  ?>
</ul>