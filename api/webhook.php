<?php
// Use in the "Post-Receive URLs" section of your GitHub repo.
if ( $_POST['payload'] ) {
  shell_exec( $cmd = 'cd ' . realpath(dirname(__DIR__)) . ' && git reset --hard HEAD && git pull' );
  $str = $cmd . "\n" . $_POST['payload'];
  file_put_contents('./payload.info', $str);
}
?>
