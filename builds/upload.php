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
<div id="dropArea" class="maincontainer">
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
						<p>Choose a Name for your Build</p>
					</td>
					<td>
						<input type="text" name="buildname" id="buildname" style="margin: 0; float: none;">
					</td>
				</tr>
				<tr>
					<td>
						<p>Write a Description for the Build</p>
					</td>
					<td>
						<textarea name="description" id="description" form="uploadForm" rows="5" style="margin: 0; float: none;"></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<p>Select a Blockland Save File</p>
					</td>
					<td>
						<input type="file" name="uploadfile" id="uploadfile">
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
<form class="hidden" action="/builds/manage.php" method="post" id="redirectToManageForm">
	<input type="hidden" name="init" value="1">
	<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
</form>
<div class="hidden" id="preloadImage">
	<img src="/img/loading.gif" />
</div>
<script type="text/javascript">
//$(document).on('dragenter', function (e) { e.stopPropagation(); e.preventDefault(); });
//$(document).on('dragover', function (e) { e.stopPropagation(); e.preventDefault(); });
//$(document).on('drop', function (e) { e.stopPropagation(); e.preventDefault(); });
//
//var escapedCharacters = [
//	/\\n/g,
//	/\\t/g,
//	/\\\\/g,
//	/\\\"/g,
//	/\\\'/g
//];
//
//var unescapedCharacters = [
//	"\n",
//	"\t",
//	"\\",
//	"\"",
//	"\'"
//];
//
//$(document).ready(function () {
//	if($("#uploadStatus").children().html() === "Form incomplete.") {
//		$("#uploadStatus").hide();
//	}
//	$(document).on("drop", function(event) {
//		event.preventDefault();
//		var files = event.originalEvent.dataTransfer.files;
//		$("#uploadfile").prop("files", files);
//		console.log(files[0]);
//	});
//	$("#uploadfile").on("change", function (event) {
//		var file = event.target.files[0];
//
//		if($("#buildname").val() == "") {
//			$("#buildname").val(file.name.replace(/\.[^/.]+$/, ""));
//		}
//		if($("#description").val() == "") {
//			var r = new FileReader();
//			r.onload = function (e) {
//				var contents = e.target.result.split("\n");
//				var desclen = parseInt(contents[1]);
//				var desc = "";
//
//				for(var i=0; i<desclen; i++) {
//					desc += contents[i+2];
//				}
//
//				for(var i=0; i<escapedCharacters.length; i++) {
//					console.log(escapedCharacters[i]);
//					desc = desc.replace(escapedCharacters[i], unescapedCharacters[i]);
//				}
//				$("#description").val(desc.trim());
//			};
//			r.readAsText(file);
//		}
//	});
//	$("#uploadForm").submit(function (event) {
//		event.stopPropagation();
//		event.preventDefault();
//		$("#uploadStatus").html("<p><img src=\"/img/loading.gif\" /></p>");
//
//		if(!$("#uploadStatus").is(":visible")) {
//			$("#uploadStatus").slideDown();
//		}
//		//var data = $(this).serialize();
//		var data = new FormData(this);
//		console.log(data);
//		//$.post("/ajax/uploadBuild.php", data, function (response) {
//		$.ajax({
//			url: "/ajax/uploadBuild.php",
//			type: "POST",
//			data: data,
//			dataType: "text",
//			cache: false,
//			processData: false,
//			contentType: false,
//			success: function (response) {
//				console.log(response);
//				response = JSON.parse(response);
//				globalvar = response;
//
//				if(response.hasOwnProperty('redirect')) {
//					$("#redirectToManageForm").get(0).setAttribute('action', escapeHtml(response.redirect));
//					$("#redirectToManageForm").submit();
//				} else {
//					$("#uploadStatus").html("<p>" + escapeHtml(response.message) + "</p>");
//				}
//			},
//			error: function (idk, response) {
//				console.log("error!");
//				$("#uploadStatus").html("<p>Error: " + response + "</p>");
//			}
//		});
//	});
//});
</script>
<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
