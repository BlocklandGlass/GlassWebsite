<?php session_start(); ?>

<?php
	if(isset($_SESSION['loggedin']))
	{
		header("Location: /index.php");
		die();
	}

	function apply_custom_filter($input)
	{
		//the only characters allowed are a-z, A-Z, 0-9, '.', '/', '-', '_', ' '
		//there are more characters allowed in filepaths, but I will add those cases as they come up
		return preg_replace("/[^a-zA-Z0-9\.\-\/\_\ ]/", "", $input);
	}

	//check to see if log in was attempted/successfull

	if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['verify']))
	{
		$username = $_POST['username'];
		$password = $_POST['password'];
		$password_check = $_POST['verify'];
		$safe_username = apply_custom_filter($username);

		if($username !== $safe_username || strlen($safe_username) < 3 || strlen($safe_username) > 20)
		{
			$status_message = "Invalid username provided.  You must use 3-20 characters and may only use letters, numbers, spaces, periods, underscores, forward slashes, and dashes.";
		}
		else if($password !== $password_check)
		{
			$status_message = "Your passwords do not match.";
		}
		else if(strlen($password) < 3)
		{
			$status_message = "Your password must be at least 3 characters";
		}
		else
		{
			require_once(realpath(dirname(__FILE__) . "/private/class/DatabaseManager.php"));
			$database = new DatabaseManager();

			//if($database
			//	$status_message = "That username is already taken.  Please try another.";
			//can never be too safe
			$resource = $database->query("SELECT * FROM USERS WHERE username = '" . $database->sanitize($safe_username) . "'");

			if(!$resource)
			{
				$status_message = "An internal database error occurred";
			}
			else if($resource->num_rows === 0)
			{
				//success, we can create the account
				//to do: salt the password
				$hashed_password = password_hash($password, PASSWORD_DEFAULT);
				$database->query("INSERT INTO users (username, password) VALUES ('" . $database->sanitize($safe_username) . "', '" . $database->sanitize($hashed_password) . "')");
				$_SESSION['justregistered'] = 1;
				header("Location: /login.php");
				die();
			}
			else
			{
				$status_message = "That username is already taken";
			}
		}
	}
	$_PAGETITLE = "Register";
	include(realpath(dirname(__FILE__) . "/private/header.php"));
	include(realpath(dirname(__FILE__) . "/private/navigationbar.php"));

	if(isset($status_message))
	{
		echo("<p>An Error has occurred: " . $status_message . "</p>");
	}
?>
<h2>Register</h2>
<form action="register.php" method="post">
	<p>Username: <input type="text" name="username" id="username"></p>
	<p>Password: <input type="password" name="password" id="password"></p>
	<p>Verify Password: <input type="password" name="verify" id="verify"></p>
	<input type="submit">
</form>
<?php
	include(realpath(dirname(__FILE__) . "/private/footer.php"));
?>
