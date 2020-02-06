<?php
require dirname(__DIR__) . '/../../private/autoload.php';

use Glass\UserManager;
use Glass\GroupManager;
use Glass\InstallationManager;

$user = UserManager::getCurrent();

if($user === false || !$user->inGroup("Administrator")) {
  header('Location: /');
  die();
}

if(!isset($_POST['csrftoken']) || $_POST['csrftoken'] != $_SESSION['csrftoken']) {
  throw new \Exception("Cross site request forgery attempt blocked");
}

die('This feature is not yet available.');