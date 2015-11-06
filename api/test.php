<?php
require_once dirname(__DIR__) . '/private/class/UserLog.php';

echo "Current: " . UserLog::getCurrentUsername($_GET['blid']);

if(isset($_GET['username'])) {
  UserLog::addEntry($_GET['blid'], $_GET['username'], $_SERVER['REMOTE_ADDR']);
}
