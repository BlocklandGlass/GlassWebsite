<?php
	session_start();
	$status = include(realpath(dirname(__DIR__) . "/private/json/manageBuild.php"));

	if(isset($status['redirect'])) {
		header("Location: " . $status['redirect']);
		die();
	}
	$_PAGETITLE = "Manage Build";
	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<div id="dropArea" class="maincontainer">
	<form action="upload.php" method="post" id="uploadForm" enctype="multipart/form-data">
		<table class="formtable">
			<tbody>
				<tr>
					<td class="center" colspan="2" id="statusMessage">
						<?php echo("<p>" . htmlspecialchars($status['message']) . "</p>"); ?>
					</td>
				</tr>
				<tr>
					<td>
						<p>Add Screenshots</p>
					</td>
					<td>
						<input type="file" name="screenshots" id="screenshots" multiple>
					</td>
				</tr>
				<tr>
					<td>
						<p>Add Tags</p>
					</td>
					<td>
						<input type="file" name="screenshots" id="screenshots" multiple>
					</td>
				</tr>
				<tr>
					<td>
						<p>Add Dependencies</p>
					</td>
					<td>
						<input type="file" name="screenshots" id="screenshots" multiple>
					</td>
				</tr>
				<tr>
					<td>
						<p>Add Co-Authors</p>
					</td>
					<td>
						<input type="file" name="screenshots" id="screenshots" multiple>
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
//to do
</script>
<?php
	include(realpath(dirname(__DIR__) . "/private/footer.php"));
?>
