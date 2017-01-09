<?php

function checkAuth($ip, $name) {
	$url = 'http://auth.blockland.us/authQuery.php';
	$data = array('NAME' => $name, 'IP' => $ip);
	$options = array(
	        'http' => array(
	        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
	        'method'  => 'POST',
	        'content' => http_build_query($data),
	    )
	);

	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	
	if(strpos($result, "NO") === 0) {
		return false;
	} else if(strpos($result, "YES") === 0) {
		$words = explode(" ", $result);
		return $words[1];
	} else if(strpos($result, "ERROR") === 0) {
		//well fuck
	} else {
		echo "shit done be breakin";
	}
}

//checkAuth('72.238.43.101', 'Jincux');
//var_dump($result);
?>