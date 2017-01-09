<?php
	require_once dirname(__DIR__) . '/autoload.php';
	use Glass\UserManager;
	if(!isset($_SESSION)) {
		session_start();
	}
	//we give the session a unique csrf token so malicious links on other sites cannot take advantage of users
	if(!isset($_SESSION['csrftoken'])) {
		$_SESSION['csrftoken'] = mt_rand();
	}

	if(isset($_SESSION['loggedin'])) {
		$response = [
			"redirect" => "/index.php"
		];
	} else {
		if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['csrftoken'])) {
			$username = $_POST['username'];
			$password = $_POST['password'];
			$csrftoken = $_POST['csrftoken'];

			if($csrftoken != $_SESSION['csrftoken']) {
				$response = [
					"message" => "Cross Site Request Forgery Detected!"
				];
			} else {
				if(isset($_POST['redirect'])) {
					$redirect = $_POST['redirect'];
					$response = UserManager::login($username, $password, $redirect);
				} else {
					$response = UserManager::login($username, $password);
				}
			}
		} else {
			if(isset($_POST['justregistered']) && $_POST['justregistered'] == 1) {
				$response = [
					"message" => "Thank you for registering!  Please log in to continue."
				];
			} else {
				$response = [
					"message" => "Form incomplete."
				];
			}
		}
	}
	return $response;
?>
