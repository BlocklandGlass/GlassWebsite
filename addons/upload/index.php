<?php
	session_start();
	$status = include(realpath(dirname(__DIR__) . "/../private/json/uploadAddon.php"));

	if(isset($status['redirect'])) {
		//echo("REDIRECT: " . $status['redirect']);
		header("Location: " . $status['redirect']);
		die();
	}
	$_PAGETITLE = "Addon Upload";
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
?>
<div class="maincontainer">
	<?php if(isset($status["message"])) {
		echo $status["message"];
	}?>
  <form action="index.php" method="post" enctype="multipart/form-data">
		<table class="formtable">
			<tbody>
				<tr>
					<td colspan="2"><h2>Upload (step 1 of 2)</h2></td>
				</tr>
				<tr>
					<td><b>Name</b></td>
					<td><input type="text" name="name" /></td>
				</tr>
				<tr>
					<td>
				    <b>Add-On Type</b><br />
					</td>
					<td>
						<select name="type">
				      <option value="1">Add-On</option>
				      <option value="2">Print</option>
				      <option value="3">Colorset</option>
				    </select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<span style="font-size: 0.8em">
							<b>Add-On</b> - Anything with a .cs file!<br />
							<b>Print</b> - Prints, faces, any of the likes<br />
							<b>Colorset</b> - Pretty rainbow!
						</span>
					</td>
				</tr>
				<tr>
					<td><b>Description</b></td>
					<td><textarea style="font-size:0.8em;" rows="5" name="description" /></textarea></td>
				</tr>
				<tr>
					<td><b>Filename</b></td>
					<td><input type="text" name="filename" /></td>
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
					<td colspan="2"><input type="submit" value="Upload File" name="submit"></td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
  </form>
</div>

<?php include(realpath(dirname(dirname(__DIR__)) . "/private/footer.php")); ?>
