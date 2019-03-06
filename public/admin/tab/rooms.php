<h2>Room Logs</h2>

<?php
	if(!$user->inGroup("Administrator") && !$user->inGroup("Moderator")) {
    die('You do not have permission to access this area.');
  }

  if(!file_exists(dirname(__FILE__) . '/../live/config.json')) {
    die('config.json is missing.');
  }

  $json = file_get_contents(dirname(__FILE__) . '/../live/config.json');

  $config = json_decode($json);
  if($config === false) {
    die('config.json is invalid.');
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

<h3>Rooms</h3>

<ul>
  <?php
    $rooms = scanRooms();
    foreach($rooms as $room) {
      $name = $config->rooms->$room ?? "Room #$room";
      echo "<li><a href=\"?tab=room&id=$room\">$name</a></li>";
    }
  ?>
</ul>