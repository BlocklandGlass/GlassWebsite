<?php session_start(); ?>

<?php
	if(isset($_SESSION['loggedin'])) {
		header("Location: /index.php");
		die();
	}

	function apply_custom_filter($input) {
		//the only characters allowed are a-z, A-Z, 0-9, '.', '/', '-', '_', ' '
		//there are more characters allowed in filepaths, but I will add those cases as they come up
		return preg_replace("/[^a-zA-Z0-9\.\-\/\_\ ]/", "", $input);
	}

	//check to see if log in was attempted/successfull

	if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['verify']) && $_POST['blid']) {
		$username = $_POST['username'];
		$password = $_POST['password'];
		$password_check = $_POST['verify'];
		$blid = trim($_POST['blid']);
		$safe_username = apply_custom_filter($username);

		if($username !== $safe_username || strlen($safe_username) < 3 || strlen($safe_username) > 20) {
			$status_message = "Invalid username provided.  You must use 3-20 characters and may only use letters, numbers, spaces, periods, underscores, forward slashes, and dashes.";
		}	else if($password !== $password_check) {
			$status_message = "Your passwords do not match.";
		} else if(strlen($password) < 4) {
			$status_message = "Your password must be at least 4 characters";
		} else if(!is_numeric($blid)) {
			$status_message = "Invalid BL_ID";
		} else {
			require_once(realpath(dirname(__FILE__) . "/private/class/DatabaseManager.php"));
			$database = new DatabaseManager();

			//if($database
			//	$status_message = "That username is already taken.  Please try another.";
			//can never be too safe
			$resource = $database->query("SELECT * FROM `users` WHERE `username` = '" . $database->sanitize($safe_username) . "' OR `blid`='" . $database->sanitize($blid) . "'");

			if(!$resource) {
				$status_message = "An internal database error occurred";
			} else if($resource->num_rows === 0) {
				$intermediateSalt = md5(uniqid(rand(), true));
    		$salt = substr($intermediateSalt, 0, 6);
    		$hash = hash("sha256", $password . $salt);

				$database->query("INSERT INTO users (username, password, salt, blid) VALUES ('" . $database->sanitize($safe_username) . "', '" . $database->sanitize($hash) . "', '" . $database->sanitize($salt) . "', '" . $database->sanitize($blid) . "')");
				$_SESSION['justregistered'] = 1;
				header("Location: /login.php");
				die();
			} else {
				$status_message = "That username is already taken";
			}
		}
	}
	$_PAGETITLE = "Glass | Register";
	include(realpath(dirname(__FILE__) . "/private/header.php"));
	include(realpath(dirname(__FILE__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<?php
	if(isset($status_message)) {
		echo("<p class=\"center\">An Error has occurred: " . $status_message . "</p>");
	}
	?>
	<form action="register.php" method="post">
		<table class="formtable">
			<tbody>
				<tr><td class="center" colspan="2"><h2>Register</h2></td></tr>
				<tr><td>Username:</td><td><input type="text" name="username" id="username"></td></tr>
				<tr><td>Password:</td><td><input type="password" name="password" id="password"></td></tr>
				<tr><td>Verify Password:</td><td><input type="password" name="verify" id="verify"></td></tr>
				<tr><td>BLID:</td><td><input type="text" name="blid" id="blid"></td></tr>
				<tr><td class="center" colspan="2"><input type="submit"></td></tr>
			</tbody>
		</table>
	</form>
</div>
<?php
	include(realpath(dirname(__FILE__) . "/private/footer.php"));
?>
