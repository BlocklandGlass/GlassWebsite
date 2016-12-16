<?php
use Glass\UserLog;

echo "Current: " . UserLog::getCurrentUsername($_GET['blid']);

if(isset($_GET['username'])) {
  UserLog::addEntry($_GET['blid'], $_GET['username'], $_SERVER['REMOTE_ADDR']);
}
