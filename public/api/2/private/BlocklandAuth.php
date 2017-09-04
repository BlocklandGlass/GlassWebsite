<?php
require dirname(__DIR__) . '/../../../private/autoload.php';

class BlocklandAuth {
  public static function checkAuth($username, $ip, $blid) {
    $res = BlocklandAuth::auth($username, $ip);
    if($res !== false) {
      return $res == $blid;
    } else {
      return false;
    }
  }

  public static function auth($name, $ip) {
    try {
  		$result = BlocklandAuthenticate($name);
  		return $result;
    } catch(Exception $e) {
      error_log("BlocklandAuth auth error: " . $e);
      return false;
    }
  }
}

function BlocklandAuthenticate($username) {
	$username = mb_convert_encoding(urldecode($username), "ISO-8859-1");
	$username = str_replace("%", "%25", $username);
	$encodeChars = array(" ", "@", "$", "&", "?", "=", "+", ":", ",", "/");
	$encodeValues = array("%20", "%40", "%24", "%26", "%3F", "%3D", "%2B", "%3A","%2C", "%2F");
	$username = str_replace($encodeChars, $encodeValues, $username);

	$postData = "NAME=${username}&IP=${_SERVER['REMOTE_ADDR']}";

	$opts = array('http' => array('method' => 'POST', 'header' => "Connection: keep-alive\r\nUser-Agent: Blockland-r1986\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: ". strlen($postData) . "\r\n", 'content' => $postData));

	$context  = stream_context_create($opts);
	$result = file_get_contents('http://auth.blockland.us/authQuery.php', false, $context);
	$parsedResult = explode(' ', trim($result));

	if($parsedResult[0] == "NO")
		return false;

	else if(!is_numeric($parsedResult[1]))
	{
		print($result);
		return false;
	}

	else
		return intval($parsedResult[1]);
}
?>
