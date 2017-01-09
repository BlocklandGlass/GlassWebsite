<?php
use Glass\UserManager;
$user = UserManager::getFromBLID(9789);
UserManager::sendPasswordResetEmail($user);
//UserManager::email($user, "Password Reset", $body);
?>
