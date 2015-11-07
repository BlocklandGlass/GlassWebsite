<?php
	session_start();
	$registerStatus = include(realpath(dirname(__FILE__) . "/private/json/register.php"));

	if(isset($registerStatus['redirect'])) {
		header("Location: " . $registerStatus['redirect']);
		die();
	}
//session_start();
//include(realpath(dirname(__FILE__) . "/private/class/AccountManager.php"));
//	if(isset($_SESSION['loggedin'])) {
//		header("Location: /index.php");
//		die();
//	}

	//if(!isset($_SESSION['csrftoken'])) {
	//	$_SESSION['csrftoken'] = rand();
	//}

	//function apply_custom_filter($input) {
	//	//the only characters allowed are a-z, A-Z, 0-9, '.', '/', '-', '_', ' '
	//	//there are more characters allowed in filepaths, but I will add those cases as they come up
	//	return preg_replace("/[^a-zA-Z0-9\.\-\/\_\ ]/", "", $input);
	//}

	//check to see if log in was attempted/successfull

	//if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['verify']) && isset($_POST['blid'])) {
	//	$email = $_POST['email'];
	//	$password = $_POST['password'];
	//	$password_check = $_POST['verify'];
	//	$blid = trim($_POST['blid']);
	//	//I don't think it is actually necessary to check csrf token for registration
    //
	//	if(isset($_POST['redirect'])) {
	//		$redirect = $_POST['redirect'];
	//		$status_message = AccountManager::register($email, $password, $password_check, $blid, $redirect);
	//	} else {
	//		$status_message = AccountManager::register($email, $password, $password_check, $blid);
	//	}
	//}
	$_PAGETITLE = "Glass | Register";
	include(realpath(dirname(__FILE__) . "/private/header.php"));
	include(realpath(dirname(__FILE__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<div class="center" id="registerStatus" style="display: none;">
		<?php echo("<p>" . htmlspecialchars($registerStatus['message']) . "</p>"); ?>
	</div>
	<form action="register.php" method="post" id="mainRegisterForm">
		<table class="formtable">
			<tbody>
				<tr><td class="center" colspan="2"><h2>Register</h2></td></tr>
				<tr><td>E-Mail Address:</td><td><input type="text" name="email" id="email"></td></tr>
				<tr><td>BLID:</td><td><input type="text" name="blid" id="blid"></td></tr>
				<tr><td>Password:</td><td><input type="password" name="password" id="password"></td></tr>
				<tr><td>Verify Password:</td><td><input type="password" name="verify" id="verify"></td></tr>
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
<script type="text/javascript">
//http://jsperf.com/escape-html-special-chars/11
function escapeHtml(text) {
	var map = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#039;'
	};
	return text.replace(/[&<>"']/g, function(m) {
		return map[m];
	});
}
$(document).ready(function () {
	$("#registerStatus").hide();
	$("#mainRegisterForm").submit(function () {
		$("#registerStatus").html("<p><img src=\"/img/loading.gif\" /></p>");

		if(!$("#registerStatus").is(":visible")) {
			$("#registerStatus").slideDown();
		}
		var data = $(this).serialize();
		$.post("/ajax/register.php", data, function (response) {
			console.log(response);
			globalvar = response;

			if(response.hasOwnProperty('redirect')) {
				window.location.replace(response.redirect);
			} else {
				$("#registerStatus").html("<p>" + escapeHtml(response.message) + "</p>");
			}
		}, "json");
		return false;
	});
});
</script>
<?php
	include(realpath(dirname(__FILE__) . "/private/footer.php"));
?>
