<?php
	require_once dirname(__DIR__) . '/private/autoload.php';
	session_start();
	$loginStatus = include(realpath(dirname(__DIR__) . "/private/json/login.php"));

	if(isset($loginStatus['redirect'])) {
		//I tried to add $_SERVER['SERVER_NAME'] but that doesn't work with localhost
		header("Location: " . $loginStatus['redirect']);
		die();
	}

  $redirect = $_REQUEST['redirect'] ?? ($_REQUEST['rd'] ?? false);

	$_PAGETITLE = "Log In | Blockland Glass";
	include(realpath(dirname(__DIR__) . "/private/header.php"));
?>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
  ?>
	<div class="tile" style="max-width: 600px; margin: 5px auto;">
		<div class="center" id="loginStatus">
			<?php echo("<p>" . utf8_encode($loginStatus['message']) . "</p>"); ?>
		</div>
		<form action="login.php" method="post" id="mainLoginForm">
			<table class="formtable">
				<tbody>
					<tr><td class="center" colspan="2"><h2>Log In</h2></td></tr>
					<tr><td>E-mail or BL_ID:</td><td><input style="max-width: 300px" type="text" name="username" id="username" required autofocus></td></tr>
					<tr><td>Password:</td><td><input style="max-width: 300px" type="password" name="password" id="password" required></td></tr>
					<tr><td class="center" colspan="2"><input type="submit"></td></tr>
				</tbody>
			</table>
			<div style="text-align: center; font-size:0.8em">
				<a href="/user/forgotPassword.php">Forgotten your password?</a>
			</div>
			<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
			<?php
				if($redirect != false) {
					echo("<input type=\"hidden\" name=\"redirect\" value=\"" . htmlspecialchars($redirect) . "\">");
				}
			?>
		</form>
		<?php
			if($redirect != false) {
				echo("<p class=\"center\">Don't have an account? <a href=\"register.php\" onclick=\"document.getElementById('redirectToRegisterForm').submit(); return false;\">Register</a></p>");
				echo("<form class=\"hidden\" action=\"/register.php\" method=\"post\" id=\"redirectToRegisterForm\">");
				echo("<input type=\"hidden\" name=\"redirect\" value=\"" . htmlspecialchars($redirect) . "\">");
				echo("</form>");
			} else {
				echo("<p class=\"center\">Don't have an account? <a href=\"register.php\">Register</a></p>");
			}
		?>
	</div>
</div>
<div class="hidden" id="preloadImage">
	<img src="/img/loading.gif" />
</div>
<script type="text/javascript">
$(document).ready(function () {
	if($("#loginStatus").children().html() === "Form incomplete.") {
		$("#loginStatus").hide();
	}
	$("#mainLoginForm").submit(function () {
		$("#loginStatus").html("<p><img src=\"/img/loading.gif\" /></p>");

		if(!$("#loginStatus").is(":visible")) {
			$("#loginStatus").slideDown();
		}
		var data = $(this).serialize();
		$.post("/ajax/login.php", data, function (response) {
			console.log(response);
			globalvar = response;

			if(response.hasOwnProperty('redirect')) {
				//using location.replace() will make it so hitting back will skip over /login.php
				window.location.replace(response.redirect);
			} else {
				$("#loginStatus").html("<p>" + escapeHtml(response.message) + "</p>");
			}
		}, "json");
		return false;
	});
});
</script>
<?php
	include(realpath(dirname(__DIR__) . "/private/footer.php"));
?>
