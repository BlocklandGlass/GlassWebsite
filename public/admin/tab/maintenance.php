<h1>Maintenance</h1>

<?php
  use Glass\GroupManager;

	if(!$user->inGroup("Administrator")) {
    die('You do not have permission to access this area.');
  }

  // warning/notice at top of tab

  // delete duplicate comments btn

  echo 'This feature is not available yet.';
?>