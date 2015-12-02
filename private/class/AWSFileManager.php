<?php
require_once(realpath(dirname(__DIR__) . '/vendor/autoload.php'));
use Aws\S3\S3Client;

//maybe this should end up working like databasemanager
class AWSFileManager {
	public static function upload($target, $local) {
		$keyData = AWSFileManager::getCredentials();

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

	public static function uploadNewBuild($bid, $name, $buildFile) {
		$keyData = AWSFileManager::getCredentials();

		$client = S3Client::factory(array(
			"credentials" => array(
				"key" => $keyData->aws_access_key_id,
				"secret" => $keyData->aws_secret_access_key
			)
		));

		$result = $client->putObject(array(
			"Bucket" => $keyData->aws_bucket,
			"Key" => "builds/" . $bid . "/" . $name,
			"SourceFile" => $buildFile
		));
	}

	//to do: screenshots should now be bundled together with the builds/addons folders probably
	public static function uploadNewScreenshot($sid, $tempFile, $tempThumb) {
		$keyData = AWSFileManager::getCredentials();

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

	private static function getCredentials() {
		return json_decode(file_get_contents(dirname(__FILE__) . "/key.json"));
	}

	public static function getBucket() {
		$keyData = AWSFileManager::getCredentials();
		return $keyData->aws_bucket;
	}
}
?>
