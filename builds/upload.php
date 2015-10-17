<?php

function getUploadFileStatus()
{
	//$loadStatus = 0;

	if(isset($_POST["submit"]))
	{
		//$loadStatus = 1;

		//a filter for file names
		//allows letters, numbers, '.', '-', '_', and ' '
		$fileName = preg_replace("/[^a-zA-Z0-9\.\-\_\ ]/", "", basename($_FILES["uploadfile"]["name"]));
		$fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
		$targetPath = realPath(dirname(__FILE__) . "uploads/" . $fileName);

		if($fileExt != "bls")
		{
			$loadError = "Only .bls files are allowed";
			$loadStatus = 2;
		}

		if($_FILES["uploadfile"]["size"] > 10000000)
		{
			//$loadError = "The Maximum file size allowed is 10 MB.";
			//$loadStatus = 2;
			return array(2, "The Maximum file size allowed is 10 MB.");
		}

		if(file_exists($targetPath))
		{
			//$loadError = "A file with that name already exists";
			//$loadStatus = 2;
			return array(2, "A file with that name already exists");
		}

		//basic parse of .bls file
		$contents = explode("\n", file_get_contents($_FILES["uploadfile"]["tmp_name"]));

		if(	!preg_match("/^This is a Blockland save file\.  You probably shouldn't modify it cause you'll screw it up\.(\r)?$/", $contents[0]) ||
			!preg_match("/^([0]|[1-9][0-9]*)(\r?)$/", $contents[1]))
		{
			//$loadError = "This save file appears to be corrupted.";
			//$loadStatus = 2;
			return array(2, "This save file appears to be corrupted.");
		}
		$desclen = $contents[1];

		//if(preg_match("/^(((1(\.0+)?)|(0(\.[0-9]+)?))[ ]){3}((1(\.0+)?)|(0(\.[0-9]+)?))\r?$/", "1.00 0.5 0.98 1.000"))
		//{
		//	$loadError = "got it";
		//	$loadStatus = 2;
		//}

		//verify color table
		for($i=0; $i<64; $i++)
		{
			if(!preg_match("/^(((1(\.0+)?)|(0(\.[0-9]+)?))[ ]){3}((1(\.0+)?)|(0(\.[0-9]+)?))\r?$/", $contents[2 + $desclen + $i]))
			{
				//$loadError = "Color parsing error: " . $i;
				//$loadStatus = 2;
				return array(2, "Color parsing error: " . $i);
			}
		}

		if(!preg_match("/^Linecount ()\r?$/", contents[66 + $desclen]))
		{
			return array(2, "This save file appears to be corrupted.");
		}
	}
}



	//so it turns out require_once is really intended for class files and functions
	//include is better for templates
	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

	echo("<p>status: " . $loadStatus . "</p>");

	if($loadStatus == 2)
	{
		echo("<p>Error: " . $loadError . "</p>");
	}

	if(isset($contents))
	{
		echo("<pre>");

		foreach($contents as $line)
		{
			echo(filter_var($line, FILTER_SANITIZE_SPECIAL_CHARS) . "\n");
		}
		echo("</pre>");
	}
	else
	{
		echo("<p>no contents</p>");
	}
?>

<form action="upload.php" method="post" enctype="multipart/form-data">
	Select a Blockland Save file to upload
	<input type="file" name="uploadfile" id="uploadfile">
	<input type="submit" value="Upload File" name="submit">
</form>

<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
