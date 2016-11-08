<?php
if(!is_file(realpath(dirname(__DIR__) . '/vendor/autoload.php'))) {
	header('Location: /install/composer.php');
	die();
}
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
			"ACL" => 'public-read',
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
			"Key" => "builds/" . $bid,
			"SourceFile" => $buildFile,
			"ACL" => 'public-read',
			"ContentDisposition" => "attachment; filename=\"" . $name . "\""
		));
	}

	public static function uploadNewAddon($aid, $branchid, $name, $tempFile) {
		$keyData = AWSFileManager::getCredentials();

		$client = S3Client::factory(array(
			"credentials" => array(
				"key" => $keyData->aws_access_key_id,
				"secret" => $keyData->aws_secret_access_key
			)
		));

		$result = $client->putObject(array(
			"Bucket" => $keyData->aws_bucket,
			"Key" => "addons/" . $aid . "_" . $branchid,
			"SourceFile" => $tempFile,
			"ACL" => 'public-read',
			"ContentDisposition" => "attachment; filename=\"" . urlencode($name) . "\""
		));
	}

	//to do: screenshots should now be bundled together with the builds/addons folders probably
	public static function uploadNewScreenshot($sid, $name, $tempFile, $tempThumb) {
		$keyData = AWSFileManager::getCredentials();

		$client = S3Client::factory(array(
			"credentials" => array(
				"key" => $keyData->aws_access_key_id,
				"secret" => $keyData->aws_secret_access_key
			)
		));

		$result = $client->putObject(array(
			"Bucket" => $keyData->aws_bucket,
			"Key" => "screenshots/" . $sid,
			"SourceFile" => $tempFile,
			"ACL" => 'public-read',
			"ContentDisposition" => "attachment; filename=\"" . $name . "\""
		));

		$result = $client->putObject(array(
			"Bucket" => $keyData->aws_bucket,
			"Key" => "screenshots/thumb/" . $sid,
			"SourceFile" => $tempThumb,
			"ACL" => 'public-read',
			"ContentDisposition" => "attachment; filename=\"" . $name . "\""
		));
	}

	private static function getCredentials() {
		$key = json_decode(file_get_contents(dirname(__DIR__) . "/config.json"));
		return $key;
	}

	public static function getBucket() {
		$keyData = AWSFileManager::getCredentials();
		return $keyData->aws_bucket;
	}
}
?>
