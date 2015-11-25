<?php
class BlocklandAuth {
  public static function checkAuth($username, $ip, $blid) {
    $authData = BlocklandAuth::auth($username, $ip);
    if($authData[0] == "success") {
      if($authData[1] == $blid) {
        return true;
      } else {
        return false;
      }
    } else {
      return $authData;
    }
  }

  public static function auth($name, $ip) {
    $url = 'http://auth.blockland.us/authQuery.php';
		$data = array('NAME' => $name, 'IP' => $ip);
		$options = array(
		        'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query($data),
		    )
		);

    try {
  		$context  = stream_context_create($options);
  		$result = @file_get_contents($url, false, $context);

      if($result === false) {
        //unable to open connection_status
        return array("connection_failed");
      }

  		if(strpos($result, "NO") === 0) {
  			return array("rejected");
  		} else if(strpos($result, "YES") === 0) {
  			$words = explode(" ", $result);
  			return array("success", $word[1]);
  		} else if(strpos($result, "ERROR") === 0) {
  			return array("blauth_error");
  		} else {
  			return array("unhandled_error");
  		}
    } catch(Exception $e) {
      return false;
    }
  }
}
?>
