<?php session_start(); ?>

<?php
	if(isset($_SESSION['loggedin'])) {
		header("Location: /index.php");
		die();
	}

	if(!isset($_SESSION['csrftoken'])) {
		$_SESSION['csrftoken'] = rand();
	}

	//function apply_custom_filter($input) {
	//	//the only characters allowed are a-z, A-Z, 0-9, '.', '/', '-', '_', ' '
	//	//there are more characters allowed in filepaths, but I will add those cases as they come up
	//	return preg_replace("/[^a-zA-Z0-9\.\-\/\_\ ]/", "", $input);
	//}

	//check to see if log in was attempted/successfull

	if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['verify']) && isset($_POST['blid'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
		$password_check = $_POST['verify'];
		$blid = trim($_POST['blid']);
		//I don't think it is actually necessary to check csrf token for registration

		if(isset($_POST['redirect'])) {
			$redirect = $_POST['redirect'];
			$status_message = AccountManager::register($username, $password, $password_check, $blid, $redirect);
		} else {
			$status_message = AccountManager::register($username, $password, $password_check, $blid, $redirect);
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
		<?php
			if(isset($_POST['redirect'])) {
				echo("<input type=\"hidden\" name=\"redirect\" value=\"" . htmlspecialchars($_POST['redirect']) . "\">");
			}
		?>
	</form>
</div>
<?php
	include(realpath(dirname(__FILE__) . "/private/footer.php"));
?>
