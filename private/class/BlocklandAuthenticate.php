<?php
namespace Glass;

class BlocklandAuthenticate {
	public static function BlocklandAuthenticate($username, $ip = false) {
		return false;

		if($ip === false) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$username = mb_convert_encoding(urldecode($username), "ISO-8859-1");
		$username = str_replace("%", "%25", $username);
		$encodeChars = array(" ", "@", "$", "&", "?", "=", "+", ":", ",", "/");
		$encodeValues = array("%20", "%40", "%24", "%26", "%3F", "%3D", "%2B", "%3A","%2C", "%2F");
		$username = str_replace($encodeChars, $encodeValues, $username);

		$postData = "NAME=${username}&IP=${ip}";

		$opts = array('http' => array('method' => 'POST', 'header' => "Connection: keep-alive\r\nUser-Agent: Blockland-r1986\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: ". strlen($postData) . "\r\n", 'content' => $postData));

		$context  = stream_context_create($opts);
		//$result = file_get_contents('http://auth.blockland.us/authQuery.php', false, $context);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://auth.blockland.us/authQuery.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);

    curl_close ($ch);

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

	public static function BlocklandAuthenticateToken($blid, $joinToken) {

		$postData = "joinToken=${joinToken}&blid=${blid}";

		$opts = array('http' => array('method' => 'POST', 'header' => "Connection: keep-alive\r\nUser-Agent: Blockland-r1986\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: ". strlen($postData) . "\r\n", 'content' => $postData));

		$context  = stream_context_create($opts);
		//$result = file_get_contents('http://auth.blockland.us/authQuery.php', false, $context);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://master3.blockland.us/authQuery.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);

    curl_close ($ch);

		$lines = preg_split('/\r\n|\r|\n/', trim($result));

		if (sizeof($lines) < 2)
			return false;

		if (trim($lines[1]) != "SUCCESS")
			return false;

		$parsedResult = explode(' ', trim($lines[0]));

		if($parsedResult[0] != "NAME")
			return false;

		return substr($lines[0], 5);
	}
}

?>
