<?php
	require_once dirname(__DIR__) . '/autoload.php';
	use Glass\UserManager;

	if(!isset($_SESSION)) {
		session_start();
	}

	if(isset($_SESSION['loggedin'])) {
		$response = [
			"redirect" => "/index.php"
		];
	} else {
		if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['verify']) && isset($_POST['blid'])) {
			$email = $_POST['email'];
			$password = $_POST['password'];
			$password_check = $_POST['verify'];
			$blid = $_POST['blid'];
			//I don't think it is actually necessary to check csrf token for registration

			$response = UserManager::register($email, $password, $password_check, $blid);
		} else {
			$response = [
				"message" => "Form Incomplete."
			];
		}
	}
	return $response;
?>
