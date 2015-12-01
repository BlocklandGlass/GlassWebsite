<?php
	session_start();

	//info is an array that either has the property "redirect" set, or has the following
	//	message - string
	//	addon - AddonObject
	//	user - UserObject
	$info = include(realpath(dirname(__DIR__) . "/private/json/manageAddon.php"));

	if(isset($info['redirect'])) {
		header("Location: " . $info['redirect']);
		die();
	}
	$_PAGETITLE = "Manage Addon";
	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div id="dropArea" class="maincontainer">
	<form action="manage.php?id=<?php echo($info['addon']->id) ?>" method="post" id="manageForm" enctype="multipart/form-data">
		<table class="formtable">
			<tbody>
				<tr>
					<td class="center" colspan="2" id="statusMessage">
						<?php echo("<p>" . htmlspecialchars($info['message']) . "</p>"); ?>
					</td>
				</tr>
				<tr>
					<td>
						<p>Title</p>
					</td>
					<td>
						<input type="text" name="addonname" id="addonname" value="<?php echo(htmlspecialchars($info['addon']->name)); ?>" style="margin: 0; float: none; width: 80%;">
					</td>
				</tr>
				<tr>
					<td>
						<p>File Name</p>
					</td>
					<td>
						<input type="text" name="filename" id="filename" value="<?php echo(htmlspecialchars($info['addon']->filename)); ?>" style="margin: 0; float: none; width: 80%;">
					</td>
				</tr>
				<tr>
					<td>
						<p>Description</p>
					</td>
					<td>
						<textarea name="description" id="description" form="manageForm" rows="5" style="margin: 0; float: none; width: 80%;"><?php
							echo(htmlspecialchars($info['addon']->description));
						?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<p>Add Screenshot</p>
					</td>
					<td>
						<input type="file" name="screenshots" id="screenshots">
					</td>
				</tr>
				<tr>
					<td>
						<p>Add Tags</p>
					</td>
					<td>
						<p>Coming Soon</p>
					</td>
				</tr>
				<tr>
					<td>
						<p>Add Dependencies</p>
					</td>
					<td>
						<p>Coming Sooner</p>
					</td>
				</tr>
				<tr>
					<td>
						<p>Add Co-Authors</p>
					</td>
					<td>
						<p>Coming Soonish</p>
					</td>
				</tr>
				<tr>
					<td>
						<p>Update Addon</p>
					</td>
					<td>
						<p>Coming Soonest</p>
					</td>
				</tr>
				<tr>
					<td class="center" colspan="2">
						<input type="submit" value="Save Changes" name="submit">
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="manage" value="1">
		<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
	</form>
</div>
<div class="hidden" id="preloadImage">
	<img src="/img/loading.gif" />
</div>
<script type="text/javascript">
//$(document).on('dragenter', function (e) { e.stopPropagation(); e.preventDefault(); });
//$(document).on('dragover', function (e) { e.stopPropagation(); e.preventDefault(); });
//$(document).on('drop', function (e) { e.stopPropagation(); e.preventDefault(); });
//
//$(document).ready(function () {
//	$(document).on("drop", function(event) {
//		event.preventDefault();
//		var files = event.originalEvent.dataTransfer.files;
//		$("#screenshots").prop("files", files);
//		console.log(files[0]);
//	});
//
//	$("#filename").focusout(function () {
//		if($(this).val() !== "" && !$(this).val().endsWith(".bls")) {
//			$(this).val($(this).val() + ".bls");
//		}
//	});
//	$("#uploadfile").on("change", function (event) {
//		var file = event.target.files[0];
//
//		if($("#buildname").val() == "") {
//			$("#buildname").val(file.name.replace(/\.[^/.]+$/, ""));
//		}
//
//		if($("#filename").val() == "") {
//			$("#filename").val(file.name);
//		}
//
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
//			dataType: "json",
//			cache: false,
//			processData: false,
//			contentType: false,
//			success: function (response) {
//				//console.log(response);
//				//response = JSON.parse(response);
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
<?php
	include(realpath(dirname(__DIR__) . "/private/footer.php"));
?>
