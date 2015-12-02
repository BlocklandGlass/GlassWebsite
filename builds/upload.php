<?php
	session_start();
	$status = include(realpath(dirname(__DIR__) . "/private/json/uploadBuild.php"));

	if(isset($status['redirect'])) {
		//echo("REDIRECT: " . $status['redirect']);
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
						<?php echo("<h2>" . htmlspecialchars($status['message']) . "</h2>"); ?>
					</td>
				</tr>
				<tr>
					<td>
						<p>Choose a <b>Title</b> for your Build Page</p>
						<!--<span style="font-size: 0.7em;">What do you want your build to be called?</span>-->
						<p class="description">What do you want your build to be called?</p>
					</td>
					<td>
						<input type="text" name="buildname" id="buildname" style="margin: 0; float: none; width: 80%;">
					</td>
				</tr>
				<tr>
					<td>
						<p>Choose a <b>File Name</b> for your Build</p>
						<p class="description">This must be a unique and valid file name</p>
					</td>
					<td>
						<input type="text" name="filename" id="filename" style="margin: 0; float: none; width: 80%;">
					</td>
				</tr>
				<tr>
					<td>
						<p>Write a <b>Description</b> for the Build</p>
						<!--<span style="font-size: 0.7em;">How would you describe your masterpiece?</span>-->
						<p class="description">How would you describe your masterpiece?<br />Text formatting with <a href="https://daringfireball.net/projects/markdown/basics" target="_blank">Markdown</a> is supported</p>
					</td>
					<td>
						<textarea name="description" id="description" form="uploadForm" rows="5" style="margin: 0; float: none; width: 80%;font-size: 0.8em"></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<p><b>Save File</b></p>
						<!--<span style="font-size: 0.7em;">You can find your saves in your Blockland folder!</span>-->
						<!--<p class="description">You can find your saves in your Blockland folder!</p>-->
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
$(document).on('dragenter', function (e) { e.stopPropagation(); e.preventDefault(); });
$(document).on('dragover', function (e) { e.stopPropagation(); e.preventDefault(); });
$(document).on('drop', function (e) { e.stopPropagation(); e.preventDefault(); });

var escapedCharacters = [
	/\\n/g,
	/\\t/g,
	/\\\\/g,
	/\\\"/g,
	/\\\'/g
];

var unescapedCharacters = [
	"\n",
	"\t",
	"\\",
	"\"",
	"\'"
];

$(document).ready(function () {
	$(document).on("drop", function(event) {
		event.preventDefault();
		var files = event.originalEvent.dataTransfer.files;
		$("#uploadfile").prop("files", files);
		console.log(files[0]);
	});
	$("#filename").focusout(function () {
		if($(this).val() !== "" && !$(this).val().endsWith(".bls")) {
			$(this).val($(this).val() + ".bls");
		}
	});
	$("#uploadfile").on("change", function (event) {
		var file = event.target.files[0];

		if($("#buildname").val() == "") {
			$("#buildname").val(file.name.replace(/\.[^/.]+$/, ""));
		}

		if($("#filename").val() == "") {
			$("#filename").val(file.name);
		}

		if($("#description").val() == "") {
			var r = new FileReader();
			r.onload = function (e) {
				var contents = e.target.result.split("\n");
				var desclen = parseInt(contents[1]);
				var desc = "";

				for(var i=0; i<desclen; i++) {
					desc += contents[i+2];
				}

				for(var i=0; i<escapedCharacters.length; i++) {
					//console.log(escapedCharacters[i]);
					desc = desc.replace(escapedCharacters[i], unescapedCharacters[i]);
				}
				$("#description").val(desc.trim());
			};
			r.readAsText(file);
		}
	});
	$("#uploadForm").submit(function (event) {
		event.stopPropagation();
		event.preventDefault();
		$("#uploadStatus").html("<p><img src=\"/img/loading.gif\" /></p>");

		if(!$("#uploadStatus").is(":visible")) {
			$("#uploadStatus").slideDown();
		}
		//var data = $(this).serialize();
		var data = new FormData(this);
		console.log(data);
		//$.post("/ajax/uploadBuild.php", data, function (response) {
		$.ajax({
			url: "/ajax/uploadBuild.php",
			type: "POST",
			data: data,
			dataType: "text",
			cache: false,
			processData: false,
			contentType: false,
			success: function (response) {
				console.log(response);
				response = JSON.parse(response);
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
<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
