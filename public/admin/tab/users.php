<?php
	if(!$user->inGroup("Administrator")) {
    die('You do not have permission to access this area.');
  }

  echo "This feature is not available yet.";
?>