<?php
require_once dirname(__DIR__) . '/class/UserManager.php';
$user = UserManager::getFromBLID(9789);
UserManager::sendPasswordResetEmail($user);
//UserManager::email($user, "Password Reset", $body);
?>
