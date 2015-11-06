<?php session_start(); ?>

<?php
	if(isset($_SESSION['loggedin'])) {
		//technically this should be an absolute url but this seems to work anyway
		header("Location: /index.php");
		die();
	}

	//we give the session a unique csrf token so malicious links on other sites cannot take advantage of users
	if(!isset($_SESSION['csrftoken'])) {
		$_SESSION['csrftoken'] = rand();
	}
	require_once(realpath(dirname(__FILE__) . "/private/class/AccountManager.php"));

	//check to see if log in was attempted/successfull
	if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['csrftoken'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
		$csrftoken = $_POST['csrftoken'];

		if($csrftoken != $_SESSION['csrftoken']) {
			$login_status = "Cross Site Request Forgery Detected!";
		} else {
			if(isset($_POST['redirect'])) {
				$redirect = $_POST['redirect'];
				$login_status = AccountManager::login($username, $password, $redirect);
			} else {
				$login_status = AccountManager::login($username, $password);
			}
		}
		//if we get past here, then the login must have failed
	}


	//	$safe_username = apply_custom_filter($username);
    //
	//	if($username !== $safe_username) {
	//		$login_status = "Invalid username/password provided.  You may only use letters, numbers, spaces, periods, underscores, forward slashes, and dashes.";
	//	} else {
	//		require_once(realpath(dirname(__FILE__) . "/private/class/DatabaseManager.php"));
	//		$database = new DatabaseManager();
    //
	//		$resource = $database->query("SELECT * FROM users WHERE username = '" . $database->sanitize($safe_username) . "'");
    //
	//		if(!$resource) {
	//			$login_status = "An internal database error occurred";
	//		} else if($resource->num_rows === 0) {
	//			$login_status = "Incorrect username and/or password";
	//		}	else {
	//			while($row = $resource->fetch_object()) {
	//				$hash = $row->password;
	//				$salt = $row->salt;
	//				if($hash == hash("sha256", $password . $salt)) {
	//					$_SESSION['loggedin'] = 1;
	//					$_SESSION['uid'] = $row->id;
	//					$_SESSION['username'] = $row->username;
	//					header("Location: /index.php");
	//					$resource->close();
	//					die();
	//				}
	//			}
	//			$login_status = "Incorrect username and/or password";
	//		}
	//		$resource->close();
	//	}
	// }
	$_PAGETITLE = "Log In";
	include(realpath(dirname(__FILE__) . "/private/header.php"));
	include(realpath(dirname(__FILE__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<?php
	if(isset($_SESSION['justregistered']) && $_SESSION['justregistered'] == 1) {
		echo("<p class=\"center\">Thank you for registering!  Please log in to continue.</p>");
	}

	if(isset($login_status)) {
		echo("<p class=\"center\">An Error has occurred: " . $login_status . "</p>");
	}
	?>
	<form action="login.php" method="post">
		<table class="formtable">
			<tbody>
				<tr><td class="center" colspan="2"><h2>Log In</h2></td></tr>
				<tr><td>E-mail or BLID:</td><td><input type="text" name="username" id="username"></td></tr>
				<tr><td>Password:</td><td><input type="password" name="password" id="password"></td></tr>
				<tr><td class="center" colspan="2"><input type="submit"></td></tr>
			</tbody>
		</table>
		<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
		<?php
			if(isset($_POST['redirect'])) {
				echo("<input type=\"hidden\" name=\"redirect\" value=\"" . htmlspecialchars($_POST['redirect']) . "\">");
			}
		?>
	</form>
	<p class="center">Don't have an account? <a href="register.php">Register</a></p>
</div>
<?php
	include(realpath(dirname(__FILE__) . "/private/footer.php"));
?>
