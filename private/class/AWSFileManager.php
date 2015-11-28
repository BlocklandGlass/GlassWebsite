<?php
require_once(realpath(dirname(__DIR__) . '/vendor/autoload.php'));
use Aws\S3\S3Client;

class AWSFileManager {
	public static function upload($target, $local) {
		$keyData = json_decode(file_get_contents(dirname(__FILE__) . "/key.json"));
		$client = S3Client::factory(array(
			"credentials" => array(
				"key" => $keyData->aws_access_key_id,
				"secret" => $keyData->aws_secret_access_key
			)
		));

		$result = $client->putObject(array(
			"Bucket" => $keyData->aws_bucket,
			"Key" => $target,
			"SourceFile" => $local
		));
	}
	public static function uploadNewBuild($bid, $tempFile) {
		$keyData = json_decode(file_get_contents(dirname(__FILE__) . "/key.json"));
		$client = S3Client::factory(array(
			"credentials" => array(
				"key" => $keyData->aws_access_key_id,
				"secret" => $keyData->aws_secret_access_key
			)
		));

		$result = $client->putObject(array(
			"Bucket" => $keyData->aws_bucket,
			"Key" => "builds/" . $bid . ".bls",
			"SourceFile" => $tempFile
		));
	}

	public static function uploadNewScreenshot($sid, $tempFile, $tempThumb) {
		$keyData = json_decode(file_get_contents(dirname(__FILE__) . "/key.json"));
		$client = S3Client::factory(array(
			"credentials" => array(
				"key" => $keyData->aws_access_key_id,
				"secret" => $keyData->aws_secret_access_key
			)
		));

		$result = $client->putObject(array(
			"Bucket" => "blocklandglass-test-bucket",
			"Key" => "screenshots/" . $sid,
			"SourceFile" => $tempFile
		));

		$result = $client->putObject(array(
			"Bucket" => "blocklandglass-test-bucket",
			"Key" => "screenshots/thumb/" . $sid,
			"SourceFile" => $tempThumb
		));
	}
}
?>
