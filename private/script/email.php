<?php
require_once dirname(__DIR__) . '/class/UserManager.php';
$user = UserManager::getFromBLID(9789);
UserManager::email($user, "Password Reset", "We see you've forgotten your password. Click <a href=\"http://blocklandglass.com\">here</a> to reset.");
?>
