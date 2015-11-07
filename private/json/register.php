<?php
	if(!isset($_SESSION)) {
		session_start();
	}

	if(isset($_SESSION['loggedin'])) {
		$response = [
			"redirect" => "/index.php"
		];
	} else {
		if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['verify']) && isset($_POST['blid'])) {
			require_once(realpath(dirname(__DIR__) . "/class/AccountManager.php"));

			$email = $_POST['email'];
			$password = $_POST['password'];
			$password_check = $_POST['verify'];
			$blid = $_POST['blid'];
			//I don't think it is actually necessary to check csrf token for registration

			//if(isset($_POST['redirect'])) {
			//	$redirect = $_POST['redirect'];
			//	$response = AccountManager::register($email, $password, $password_check, $blid, $redirect);
			// } else {
				$response = AccountManager::register($email, $password, $password_check, $blid);
			// }
		} else {
			$response = [
				"message" => "Some form data was not received."
			];
		}
	}
	return $response;
?>
