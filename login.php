<?php session_start(); ?>

<?php
	if(isset($_SESSION['loggedin'])) {
		//technically this should be an absolute url but this seems to work
		header("Location: /index.php");
		die();
	}

	function apply_custom_filter($input) {
		//the only characters allowed are a-z, A-Z, 0-9, '.', '/', '-', '_', ' '
		//there are more characters allowed in filepaths, but I will add those cases as they come up
		return preg_replace("/[^a-zA-Z0-9\.\-\/\_\ ]/", "", $input);
	}

	//check to see if log in was attempted/successfull

	if(isset($_POST['username']) && isset($_POST['password'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
		$safe_username = apply_custom_filter($username);

		if($username !== $safe_username) {
			$login_status = "Invalid username/password provided.  You may only use letters, numbers, spaces, periods, underscores, forward slashes, and dashes.";
		} else {
			require_once(realpath(dirname(__FILE__) . "/private/class/DatabaseManager.php"));
			$database = new DatabaseManager();

			$resource = $database->query("SELECT * FROM users WHERE username = '" . $database->sanitize($safe_username) . "'");

			if(!$resource) {
				$login_status = "An internal database error occurred";
			} else if($resource->num_rows === 0) {
				$login_status = "Incorrect username and/or password";
			}	else {
				while($row = $resource->fetch_object()) {
					$hash = $row->password;
					$salt = $row->salt;
					if($hash == hash("sha256", $password . $salt)) {
						$_SESSION['loggedin'] = 1;
						$_SESSION['uid'] = $row->id;
						$_SESSION['username'] = $row->username;
						header("Location: /index.php");
						$resource->close();
						die();
					}
				}
				$login_status = "Incorrect username and/or password";
			}
			$resource->close();
		}
	}
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
				<tr><td>Username:</td><td><input type="text" name="username" id="username"></td></tr>
				<tr><td>Password:</td><td><input type="password" name="password" id="password"></td></tr>
				<tr><td class="center" colspan="2"><input type="submit"></td></tr>
			</tbody>
		</table>
	</form>
	<p class="center">Don't have an account? <a href="register.php">Register</a></p>
</div>
<?php
	include(realpath(dirname(__FILE__) . "/private/footer.php"));
?>
