<?php
// Use in the "Post-Receive URLs" section of your GitHub repo.
if ( $_POST['payload'] ?? false ) {
  $res1 = shell_exec( $cmd1 = 'cd ' . realpath(dirname(__DIR__)) . ' && git reset --hard HEAD && git pull' );

  $devPath = dirname(__DIR__) . '/../glassDev/';
  if(is_dir($devPath)) {
    $res2 = shell_exec( $cmd2 = 'cd ' . realpath($devPath) . ' && git reset --hard HEAD && git checkout development && git pull' );
  }


  $payload = json_decode($_POST['payload']);

  if($payload === false) {
    error_log('GitHub webhook payload decoding failed!');
    return;
  }

  $str = $cmd1 . "\n" . ($cmd2 ?? "no dev path") . "\n\n" . $res1 . "\n" . ($res2 ?? "no dev res") . "\n\n" .json_encode($payload, JSON_PRETTY_PRINT);

  file_put_contents('./payload.info', $str);

  header('Content-Type: text/json');
  echo $str;
} else {
  echo "No payload!";
}
?>
