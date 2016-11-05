<?php
	session_start();
	$status = include(__DIR__ . "/../private/json/updateAddon.php");

	if(isset($status['redirect'])) {
		//echo("REDIRECT: " . $status['redirect']);
		header("Location: " . $status['redirect']);
		die();
	}
	$_PAGETITLE = "Blockland Glass | Addon Update";
	include(__DIR__ . "/../private/header.php");
	include(__DIR__ . "/../private/navigationbar.php");
?>
<div class="maincontainer">
	<?php
		//if(isset($status["message"])) {
		//	echo $status["message"];
		//}
	?>
	<form action="update.php?id=<?php echo $_REQUEST['id'] ?>" method="post" enctype="multipart/form-data">
		<table class="formtable">
			<tbody>
<!--				<tr>
					<td colspan="2"><h2>Upload (step 1 of 2)</h2></td>
				</tr> -->
				<tr>
					<td class="center" colspan="2" id="uploadStatus">
						<?php echo(utf8_encode($status["message"])); ?>
					</td>
				</tr>
				<tr>
					<td>
            <b>Version</b><br />
            <span style="font-size: 0.7em;"><a href="http://semver.org" target="_blank">SemVer</a> naming convention</span>
          </td>
					<td><input type="text" name="addonversion" id="addonversion" value="<?php echo $status['version']; ?>"/></td>
				</tr>
				<tr>
					<td>
            <b>Change Log</b><br />
            <span style="font-size: 0.7em;"><a href="https://bitbucket.org/Greek2me/support_updater#rst-header-formatting" target="_blank">TML</a> mark-up</span>
          </td>
					<td><textarea style="font-size:0.8em;" rows="5" name="changelog" /></textarea></td>
				</tr>
				<tr>
					<td>
						<p><b>File</b></p>
						<!--<span style="font-size: 0.7em;">You can find your saves in your Blockland folder!</span>-->
						<!--<p class="description">You can find your saves in your Blockland folder!</p>-->
					</td>
					<td style="vertical-align: middle">
						<input type="file" name="uploadfile" id="uploadfile">
					</td>
				</tr>
				<tr>
					<td>
						<p><b>Restart Required</b></p>
						<!--<span style="font-size: 0.7em;">You can find your saves in your Blockland folder!</span>-->
						<!--<p class="description">You can find your saves in your Blockland folder!</p>-->
					</td>
					<td style="vertical-align: middle">
						<input type="checkbox" name="restart">
					</td>
				</tr>
				<tr>
					<td>
						<p><b>Beta</b></p>
						<!--<span style="font-size: 0.7em;">You can find your saves in your Blockland folder!</span>-->
						<!--<p class="description">You can find your saves in your Blockland folder!</p>-->
					</td>
					<td style="vertical-align: middle">
						<input type="checkbox" name="beta">
					</td>
				</tr>
				<tr>
					<td colspan="2"><input type="submit" value="Upload File" name="submit"></td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
  </form>
</div>
<form class="hidden" action="/addons/manage.php" method="post" id="redirectToManageForm">
	<input type="hidden" name="init" value="1">
	<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
</form>
<div class="hidden" id="preloadImage">
	<img src="/img/loading.gif" />
</div>
<script type="text/javascript">
$(document).on('dragenter', function (e) { e.stopPropagation(); e.preventDefault(); });
$(document).on('dragover', function (e) { e.stopPropagation(); e.preventDefault(); });
$(document).on('drop', function (e) { e.stopPropagation(); e.preventDefault(); });

$(document).ready(function () {
	$(document).on("drop", function(event) {
		event.preventDefault();
		var files = event.originalEvent.dataTransfer.files;
		$("#uploadfile").prop("files", files);
		console.log(files[0]);
	});
	$("#filename").focusout(function () {
		if($(this).val() !== "" && !$(this).val().endsWith(".zip")) {
			$(this).val($(this).val() + ".zip");
		}
	});
	$("#uploadfile").on("change", function (event) {
		var file = event.target.files[0];

		if($("#addonname").val() == "") {
			$("#addonname").val(file.name.replace(/\.[^/.]+$/, ""));
		}

		if($("#filename").val() == "") {
			$("#filename").val(file.name);
		}

		//using a javascript .zip library to pull the description.txt contents might be overkill
	});
	$("#uploadForm").submit(function (event) {
		console.log("upload form?");
		event.stopPropagation();
		event.preventDefault();
		$("#uploadStatus").html("<p><img src=\"/img/loading.gif\" /></p>");

		if(!$("#uploadStatus").is(":visible")) {
			$("#uploadStatus").slideDown();
		}
		//var data = $(this).serialize();
		var data = new FormData(this);
		//console.log(data);
		//$.post("/ajax/uploadBuild.php", data, function (response) {
		$.ajax({
			url: "/ajax/updateAddon.php",
			type: "POST",
			data: data,
			dataType: "json",
			cache: false,
			processData: false,
			contentType: false,
			success: function (response) {
				//console.log(response);
				//response = JSON.parse(response);
				globalvar = response;

				if(response.hasOwnProperty('redirect')) {
					$("#redirectToManageForm").get(0).setAttribute('action', escapeHtml(response.redirect));
					$("#redirectToManageForm").submit();
				} else {
					$("#uploadStatus").html("<h2>" + escapeHtml(response.message) + "</h2>");
				}
			},
			error: function (idk, response) {
				console.log("error!");
				$("#uploadStatus").html("<h2>Error: " + response + "</h2>");
			}
		});
	});
});
</script>
<?php include(__DIR__ . "/../private/footer.php"); ?>
