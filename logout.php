<?php
	session_start();

	if(!isset($_SESSION['csrftoken'])) {
		$_SESSION['csrftoken'] = mt_rand();
	}

	if(isset($_POST['csrftoken']) && $_POST['csrftoken'] == $_SESSION['csrftoken']) {
		session_destroy();

		if(isset($_POST['redirect'])) {
			header("Location: " . $_POST['redirect']);
		} else {
			header("Location: " . "/index.php");
		}
		die();
	}
	echo("Cross site request forgery attempt blocked.  You have not been logged out.");
?>
