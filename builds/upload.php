<?php
	session_start();
	$status = include(realpath(dirname(__DIR__) . "/private/json/uploadBuild.php"));

	if(isset($status['redirect'])) {
		header("Location: " . $status['redirect']);
		die();
	}
	$_PAGETITLE = "Build Upload";
	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
	<form action="upload.php" method="post" id="uploadForm" enctype="multipart/form-data">
		<table class="formtable">
			<tbody>
				<tr>
					<td class="center" colspan="2" id="uploadStatus">
						<?php echo("<p>" . htmlspecialchars($status['message']) . "</p>"); ?>
					</td>
				</tr>
				<tr>
					<td>
						<p>Select a Blockland Save File to upload</p>
					</td>
					<td>
						<input type="file" name="uploadfile" id="uploadfile">
					</td>
				</tr>
				<tr>
					<td>
						<p>Choose a Name for your Build</p>
					</td>
					<td>
						<input type="text" name="fileName" id="buildname" style="margin: 0; float: none;">
					</td>
				</tr>
				<tr>
					<td class="center" colspan="2">
						<input type="submit" value="Upload File" name="submit">
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
	</form>
</div>
<div class="hidden" id="preloadImage">
	<img src="/img/loading.gif" />
</div>
<script type="text/javascript">
$(document).ready(function () {
	if($("#uploadStatus").children().html() === "Form incomplete.") {
		$("#uploadStatus").hide();
	}
	$("#uploadForm").submit(function () {
		$("#uploadStatus").html("<p><img src=\"/img/loading.gif\" /></p>");

		if(!$("#uploadStatus").is(":visible")) {
			$("#uploadStatus").slideDown();
		}
		var data = $(this).serialize();
		console.log(data);
		$.post("/ajax/uploadBuild.php", data, function (response) {
			console.log(response);
			globalvar = response;

			if(response.hasOwnProperty('redirect')) {
				//using location.replace() will make it so hitting back will skip over /login.php
				window.location.replace(response.redirect);
			} else {
				$("#uploadStatus").html("<p>" + escapeHtml(response.message) + "</p>");
			}
		}, "json");
		return false;
	});
});
</script>
<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
