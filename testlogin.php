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
	$_PAGETITLE = "Log In";
	include(realpath(dirname(__FILE__) . "/private/header.php"));
	include(realpath(dirname(__FILE__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<?php
	if(isset($_POST['justregistered']) && $_POST['justregistered'] == 1) {
		echo("<p class=\"center\">Thank you for registering!  Please log in to continue.</p>");
	}

	if(isset($login_status)) {
		echo("<p class=\"center\">An Error has occurred: " . $login_status . "</p>");
	}
	?>
	<form action="testlogin.php" method="post" id="loginForm">
		<input type="text" name="username" id="username">
		<input type="password" name="password" id="password">
		<input type="submit">
		<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
		<?php
			if(isset($_POST['redirect'])) {
				echo("<input type=\"hidden\" name=\"redirect\" value=\"" . htmlspecialchars($_POST['redirect']) . "\">");
			}
		?>
	</form>
	<p class="center">Don't have an account? <a href="register.php">Register</a></p>
</div>
<script type="text/javascript">
$(document).ready(function () {
	$("#loginForm").submit(function () {
		var data = $(this).serialize();
		$.ajax({
			type: "POST",
			url: "/scripts/login.php",
			dataType: "json",
			contentType: "application/json",
			data: data,
			async: true,
			success: function (response) {
				console.log(response);
			}
		});
		return false;
	});
});
</script>
<?php
	include(realpath(dirname(__FILE__) . "/private/footer.php"));
?>
