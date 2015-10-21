<?php
	//so it turns out require_once is really intended for class files and functions
	//include is better for templates
	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

	if(isset($_POST["submit"]))
	{
		require_once(realpath(dirname(__DIR__) . "/private/class/BLSParser.php"));
		$parser = new BLSParser("uploadfile");

		echo("<p>Name: " . $parser->getName() . "</p>");
		echo("<p>Status: " . $parser->getErrorId() . "</p>");
		echo("<p>Message: " . $parser->getErrorMessage() . "</p>");
	}
	else
	{
		echo("<p>Nothing submitted yet</p>");
	}
?>

<form action="upload.php" method="post" enctype="multipart/form-data">
	Select a Blockland Save file to upload
	<input type="file" name="uploadfile" id="uploadfile">
	<input type="submit" value="Upload File" name="submit">
</form>

<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
