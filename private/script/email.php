<?php
require_once dirname(__DIR__) . '/class/UserManager.php';
$user = UserManager::getFromBLID(9789);
UserManager::email($user, "Test", "This is a test email");
?>
