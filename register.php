<?php
	session_start();
	$registerStatus = include(realpath(dirname(__FILE__) . "/private/json/register.php"));

	if(isset($registerStatus['redirect'])) {
		header("Location: " . $registerStatus['redirect']);
		die();
	}
	$_PAGETITLE = "Glass | Register";
	include(realpath(dirname(__FILE__) . "/private/header.php"));
	include(realpath(dirname(__FILE__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<div class="center" id="registerStatus" style="display: none;">
		<?php echo("<p>" . htmlspecialchars($registerStatus['message']) . "</p>"); ?>
	</div>
	<form action="/register.php" method="post" id="mainRegisterForm">
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
<form class="hidden" action="/login.php" method="post" id="redirectToLoginForm">
<?php
	if(isset($_POST['redirect'])) {
		echo("<input type=\"hidden\" name=\"redirect\" value=\"" . htmlspecialchars($_POST['redirect']) . "\">");
	}
?>
	<input type="hidden" name="justregistered" value="1">
</form>
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
				$("#redirectToLoginForm").get(0).setAttribute('action', escapeHtml(response.redirect));
				$("#redirectToLoginForm").submit();
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
