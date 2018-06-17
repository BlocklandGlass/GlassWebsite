<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
	use Glass\BoardManager;
	use Glass\UploadManager;

	$status =	UploadManager::getStatus($_REQUEST, $_FILES['uploadfile'] ?? null);

	if(isset($status['redirect'])) {
		//echo("REDIRECT: " . $status['redirect']);
		header("Location: " . $status['redirect']);
		die();
	}
	$_PAGETITLE = "Blockland Glass | Add-On Upload";
	include(__DIR__ . "/../../../private/header.php");

	$message = "";

	if(isset($status['message'])) {
		$message .= "<h3>{$status['message']}</h3>";
	}

	if(isset($status['problems'])) {
		$message .= "<div style=\"text-align:left\"><ul>";
		foreach($status['problems'] as $problem) {
			$message .= "<li>$problem</li>";
		}
		$message .= "</ul></div>";
	}
?>
<div class="maincontainer" style="text-align:center">
	<?php
    include(__DIR__ . "/../../../private/navigationbar.php");
		//if(isset($status["message"])) {
		//	echo $status["message"];
		//}
	?>
	<form action="" method="post" enctype="multipart/form-data">
		<div class="tile" style="display: inline-block; margin: 10px 0; width: 590px; text-align:left">
			<h2>Add-On Upload</h2>
			<p>
				Blockland Glass provides easy add-on browsing for our users, ensuring safety and usability. We ask that you, the developer, only upload age-appropriate and safe content. All add-ons are reviewed before becoming publically available.
			</p>
			<p>
				<b>Do not upload content that you did not make. Period.</b>
			</p>
			<p>
				<a href="https://daringfireball.net/projects/markdown/syntax">Markdown</a> is supported for descriptions. Glass uses the <a href="http://semver.org">SemVer</a> versioning system
			</p>
		</div>
		<br />
		<div class="tile" style="display: inline-block; margin: auto 0; width: 590px; text-align:center">
			<table class="formtable">
				<tbody>
					<tr>
						<td colspan="2">
							<?php echo(utf8_encode($message)); ?>
						</td>
					</tr>
					<tr>
						<td><b>Name</b></td>
						<td><input type="text" name="addonname" id="addonname" style="width: 400px" placeholder="Give your add-on a title" value="<?php echo $_REQUEST['addonname'] ?? ""; ?>"/></td>
					</tr>
					<tr>
						<td><b>Summary</b></td>
						<td><input type="text" name="summary" id="summary" style="width: 400px" placeholder="A short one-liner description" value="<?php echo $_REQUEST['summary'] ?? ""; ?>"/></td>
					</tr>
					<tr>
						<td><b>Board</b></td>
						<td style="text-align:left">
							<select name="board"  value="<?php echo $_REQUEST['board'] ?? ""; ?>">
								<?php
									$boards = BoardManager::getAllBoards();
									foreach($boards as $board) {
										$boardName = htmlspecialchars($board->getName());
										$boardId = $board->getId();
										echo "<option value=\"$boardId\">$boardName</option>";
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td style="vertical-align:top"><b>Description</b></td>
						<td><textarea style="font-size:0.8em;width:400px;height:200px" name="description" placeholder="How does it work? Markdown is supported."/><?php echo $_REQUEST['description'] ?? ""; ?></textarea></td>
					</tr>
					<tr>
						<td style="vertical-align:top"><b>Filename</b></td>
						<td><input type="text" name="filename" style="width: 400px" placeholder="Client_MySuperCoolAddon"  value="<?php echo $_REQUEST['filename'] ?? ""; ?>"/></td>
					</tr>
					<tr>
						<td style="vertical-align:top"><b>Current Version</b></td>
						<td><input type="text" name="version" style="width: 400px" placeholder="Probably 1.0.0"  value="<?php echo $_REQUEST['version'] ?? "1.0.0"; ?>"/></td>
					</tr>
					<tr>
						<td>
							<p><b>File</b></p>
							<!--<span style="font-size: 0.7em;">You can find your saves in your Blockland folder!</span>
							<p class="description">You can find your saves in your Blockland folder!</p>-->
						</td>
						<td style="vertical-align: middle; text-align:left">
							<input type="file" name="uploadfile" id="uploadfile">
						</td>
					</tr>
					<tr>
				</tbody>
			</table>
		</div>
		<div style="text-align:center">
			<input type="submit" value="Upload File" name="submit">
		</div>
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

		if($("#filename").val() == "") {
			$("#filename").val(file.name);
		}

		//using a javascript .zip library to pull the description.txt contents might be overkill
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
		//console.log(data);
		//$.post("/ajax/uploadBuild.php", data, function (response) {
		$.ajax({
			url: "/ajax/uploadAddon.php",
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
<?php include(__DIR__ . "/../../../private/footer.php"); ?>
