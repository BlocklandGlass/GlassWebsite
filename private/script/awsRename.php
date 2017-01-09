<?php
header('Content-Type: text');
require dirname(__DIR__) . '/autoload.php';

use Glass\AWSFileManager;
use Glass\AddonManager;
use Aws\S3\S3Client;

$addons = AddonManager::getAll();

$s3 = AWSFileManager::getClient();
$bucket = AWSFileManager::getBucket();

foreach($addons as $addon) {

  $sourceKeyname = 'addons/' . $addon->getId() . '_1';
  $targetKeyname = 'addons/' . $addon->getId();

  // Instantiate the client.


  // Copy an object.
  try {
    $response = $s3->doesObjectExist($bucket, $sourceKeyname);
    if($response !== true) {
      echo $sourceKeyname . ' doesn\'t exist!';
      echo "\n";
      continue;
    }
    $s3->copyObject(array(
        'Bucket'     => $bucket,
        'Key'        => $targetKeyname,
        'CopySource' => "{$bucket}/{$sourceKeyname}",
    ));

    $result = $s3->deleteObject(array(
        'Bucket' => $bucket,
        'Key' => "{$sourceKeyname}"
    ));
    echo 'Moved ' . $sourceKeyname . ' to ' . $targetKeyname . "\n";
  } catch(Exception $e) {
    echo 'Got Exception: ' . $e->getMessage() . "\n";
  }
}
