<?php

// Glass Live room logs have been moved to the admin control panel.

$id = (isset($_GET['id']) ? ("&id=" . $_GET['id']) : "");
$date = (isset($_GET['date']) ? ("&date=" . $_GET['date']) : "");

header("Location: /admin/?tab=room" . $id . $date);