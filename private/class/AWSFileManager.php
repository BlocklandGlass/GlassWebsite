<?php
require_once(realpath(dirname(__DIR__) . '/vendor/autoload.php'));
use Aws\S3\S3Client;

class AWSFileManager {
	public static function uploadNewBuild($bid, $tempFile) {
		$keyData = json_decode(file_get_contents(dirname(__FILE__) . "/key.json"));
		$client = S3Client::factory(array(
			//"profile" => "default"
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
}
?>
