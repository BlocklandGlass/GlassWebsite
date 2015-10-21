<?php
class BLSParser
{
	private $name;
	private $valid;
	private $error_id;
	private $error_message;

	/**
	 *  Takes in the $target parameter
	 *  This should be the form variable or whatever it's called that sent the file
	 */
	function __construct($target)
	{
		$this->name = "";
		$this->valid = false;
		$this->error_id = 1;
		$this->error_message = "";

		if(!isset($_FILES[$target]["name"]) || !$_FILES[$target]["size"])
		{
			$this->error_id = 1;
			$this->error_message = "No file provided.";
			return $this->error_id;
		}

		if($_FILES[$target]["size"] > 10000000)
		{
			$this->error_id = 2;
			$this->error_message = "The Maximum upload file size is 10 MB";
			return $this->error_id;
		}

		//a filter for file names
		//allows letters, numbers, '.', '-', '_', '\'', and ' '
		$this->name = basename($_FILES[$target]["name"]);

		if(preg_replace("/[^a-zA-Z0-9\.\-\_\ \']/", "", $this->name) !== $this->name)
		{
			$this->error_id = 2;
			$this->error_message = "Invalid characters in file name";
			return $this->error_id;
		}
		$fileExt = pathinfo($this->name, PATHINFO_EXTENSION);
		$targetPath = realPath(dirname(__FILE__) . "uploads/" . $this->name);

		if($fileExt != "bls")
		{
			$this->error_id = 2;
			$this->error_message = "Only .bls files are allowed";
			return $this->error_id;
		}

		if(file_exists($targetPath))
		{
			$this->error_id = 2;
			$this->error_message = "A file with that name already exists.";
			return $this->error_id;
		}

		//basic parse of .bls file
		//$contents = explode("\n", file_get_contents($_FILES["uploadfile"]["tmp_name"]));
		$contents = file($_FILES[$target]["tmp_name"]);
		return $this->validateFileContents($contents);
	}

	/**
	 *  Takes in an array of strings representing the contents of the file.
	 *  Returns 0 on success, and an integer greater than zero otherwise, representing the error id.
	 */
	public function validateFileContents($contents)
	{
		//check header
		if(	!preg_match("/^This is a Blockland save file\.  You probably shouldn't modify it cause you'll screw it up\.(\r)?$/", $contents[0]) ||
			!preg_match("/^([0]|[1-9][0-9]*)\r?$/", $contents[1]))
		{
			$this->error_id = 2;
			$this->error_message = "This save file appears to be corrupted: bad header.";
			return $this->error_id;
		}
		$desclen = $contents[1];

		//verify color table
		for($i=0; $i<64; $i++)
		{
			if(!preg_match("/^(((1(\.0+)?)|(0(\.[0-9]+)?))[ ]){3}((1(\.0+)?)|(0(\.[0-9]+)?))\r?$/", $contents[2 + $desclen + $i]))
			{
				$this->error_message = "Color parsing error - " . $i;
				$this->error_id = 2;
				return $this->error_id;
			}
		}

		if(!preg_match("/^Linecount (0|([1-9][0-9]*))\r?$/", $contents[66 + $desclen]))
		{
			$this->error_id = 2;
			$this->error_message = "This save file appears to be corrupted: bad linecount.";
		}
		$currentLine = 67 + $desclen;
		$count = count($contents);

		//verify actual brick data
		for($currline = 67 + $desclen; $currline < $count; $currline++)
		{
			if(isset($emptyline))
			{
				//we saw an empty line that was not the last one
				$this->error_id = 2;
				$this->error_message = "This save file appears to be corrupted: text after empty line.";
				return $this->error_id;
			}

			if($contents[$currline] === "")
			{
				$emptyline = true;
				continue;
			}

			if(!preg_match("/^[^\"]+[\"]( -?([0-9]+(\.[0-9]+)?)){3} [0-3] [0-1] [0-9]+ .* [0-9]+ [0-9]+ [0-1] [0-1] [0-1]\r?$/", $contents[$currline]))
			{
				$this->error_id = 2;
				$this->error_message = "This save file appears to be corrupted: Bad brick definition - " . $currline;
				return $this->error_id;
			}

			//check for +- properties
			for($i=$currline+1; $i < $count; $i++)
			{
				if(!preg_match("/^\+-/", $contents[$i]))
				{
					$currline = $i - 1;
					break;
				}
			}
		}
		//made it
		$this->error_id = 0;
		$this->error_message = "File OK.";
		$this->valid = true;
		return $this->error_id;
	}

	public function getErrorMessage()
	{
		return $this->error_message;
	}

	public function getErrorId()
	{
		return $this->error_id;
	}

	public function getName()
	{
		return $this->name;
	}
}
?>
