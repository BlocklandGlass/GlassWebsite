<?php session_start(); ?>

<?php
	if(isset($_SESSION['loggedin']))
	{
		//technically this should be an absolute url but this seems to work
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

	if(isset($_POST['username']) && isset($_POST['password']))
	{
		$username = $_POST['username'];
		$password = $_POST['password'];
		$safe_username = apply_custom_filter($username);

		if($username !== $safe_username)
		{
			$login_status = "Invalid username/password provided.  You may only use letters, numbers, spaces, periods, underscores, forward slashes, and dashes.";
		}
		else
		{
			require_once(realpath(dirname(__FILE__) . "/private/class/DatabaseManager.php"));
			$database = new DatabaseManager();

			$resource = $database->query("SELECT * FROM users WHERE username = '" . $database->sanitize($safe_username) . "'");

			if(!$resource)
			{
				$login_status = "An internal database error occurred";
			}
			else if($resource->num_rows === 0)
			{
				$login_status = "Incorrect username and/or password";
			}
			else
			{
				while($row = $resource->fetch_assoc())
				{
					$hash = $row['password'];

					if(password_verify($password, $hash))
					{
						$_SESSION['loggedin'] = 1;
						$_SESSION['userid'] = $row['id'];
						$_SESSION['username'] = $row['username'];
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

	if(isset($_SESSION['justregistered']) && $_SESSION['justregistered'] == 1)
	{
		echo("<p>Thank you for registering!  Please log in to continue.</p>");
	}

	if(isset($login_status))
	{
		echo("<p>An Error has occurred: " . $login_status . "</p>");
	}
?>
<h2>Log In</h2>
<form action="login.php" method="post">
	<p>Username: <input type="text" name="username" id="username"></p>
	<p>Password: <input type="password" name="password" id="password"></p>
	<input type="submit">
</form>
<p>Don't have an account? <a href="register.php">Register</a></p>
<?php
	include(realpath(dirname(__FILE__) . "/private/footer.php"));
?>
