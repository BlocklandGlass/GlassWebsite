<?php
header('Content-Type: text/plain');

define('LOGFILE', '/tmp/github-webhook.log');

$secret = file_get_contents(dirname(__FILE__) . '/../../private/github_secret.txt');

$post_data = file_get_contents('php://input');
$signature = hash_hmac('sha1', $post_data, $secret);

// required data in headers - probably doesn't need changing
$required_headers = array(
	'REQUEST_METHOD' => 'POST',
	'HTTP_X_GITHUB_EVENT' => 'push',
	'HTTP_USER_AGENT' => 'GitHub-Hookshot/*',
	'HTTP_X_HUB_SIGNATURE' => 'sha1=' . $signature,
);

error_reporting(0);
function log_msg($msg) {
	if(LOGFILE != '') {
		file_put_contents(LOGFILE, $msg . "\n", FILE_APPEND);
	}
}

function array_matches($have, $should, $name = 'array') {
	$ret = true;
	if(is_array($have)) {
		foreach($should as $key => $value) {
			if(!array_key_exists($key, $have)) {
				log_msg("Missing: $key");
				$ret = false;
			}
			else if(is_array($value) && is_array($have[$key])) {
				$ret &= array_matches($have[$key], $value);
			}
			else if(is_array($value) || is_array($have[$key])) {
				log_msg("Type mismatch: $key");
				$ret = false;
			}
			else if(!fnmatch($value, $have[$key])) {
				log_msg("Failed comparison: $key={$have[$key]} (expected $value)");
				$ret = false;
			}
		}
	}
	else {
		log_msg("Not an array: $name");
		$ret = false;
	}
	return $ret;
}

$headers_ok = array_matches($_SERVER, $required_headers, '$_SERVER');

// Use in the "Post-Receive URLs" section of your GitHub repo.
if ( $headers_ok ) {
  $res1 = shell_exec( $cmd1 = 'cd ' . realpath(dirname(__DIR__)) . ' && git reset --hard HEAD && git pull' );

  $devPath = dirname(__DIR__) . '/../glassDev/';
  if(is_dir($devPath)) {
    $res2 = shell_exec( $cmd2 = 'cd ' . realpath($devPath) . ' && git reset --hard HEAD && git checkout development && git pull' );
  }

  $payload = json_decode($post_data);

  if($payload === false) {
    log_msg('GitHub webhook payload decoding failed!');
    return;
  }

  $str = $cmd1 . "\n" . ($cmd2 ?? "no dev path") . "\n\n" . $res1 . "\n" . ($res2 ?? "no dev res") . "\n\n";
  file_put_contents('./payload.info', $str);

  shell_exec('cd /var/www/glass/ && composer update --no-ansi > /tmp/github-webhook-composer.log');

  echo $str;
} else {
  http_response_code(403);
	die("Forbidden\n");
}
?>
