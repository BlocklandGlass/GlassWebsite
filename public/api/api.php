<?php
echo "update\n";
return;
require_once dirname(__DIR__);
$mysqli = OpenMysqliCon();
$ip = $_SERVER['REMOTE_ADDR'];
if($ip == '127.0.0.1') {
	//yey testing
	$ip = '50.89.187.38';
}

require_once 'verify.php';
$call = $_POST['call'];


if($_POST['sid']) {
	session_id($_POST['sid']);
	session_start();
	//check for spoofing
	if($_SESSION['ip'] != $ip) {
		echo "gen error\treauth\n";
		$_SESSION['authed'] = false;
		die();
	}
} else {
	session_start();
	$_SESSION['ip'] = $ip;
	$_SESSION['authed'] = false;
}


echo $ip . " - " . $call . "\n";
if($call == "init") {
	$name = $_POST['arg1'];
	$version = $_POST['arg2'];
	if(isset($_SESSION['username'])) {
		echo "howdy\t" . $_SESSION['username'] . "\n";
	} else {
		echo "howdy\n";
	}




	$ret = checkAuth($ip, $name);
	echo $ret . "\n";
	if($ret === false) {
		echo "auth\tfailed\t0\n";
		die();
	} else {
		//bl_id, name, ip confirmed
		// TODO checkAccountStatus($blid);
		echo "auth\tpassed\t" . session_id() . "\n";
		$_SESSION['authed'] = true;
		$_SESSION['username'] = $name;
		$_SESSION['blid'] = $ret;
		$_SESSION['version'] = $version;

		$web = checkWebAccount($ret);
		if($web[0]) {
			if(!$web[3]) {
				echo "verify\tpassword\t" . $web[1] . "\t" . $web[2] . "\n";
			}
		} else {
			echo "verify\tno_account\n";
		}
	}


	if($version > 0.1) {
		echo "notification\tDoing some shaky shit\tYou're a hipster, aren't you? BLG test version detected\n";
	} else if ($version < 0.1) {
		echo "update\n";
	}
} else if($_SESSION['authed']) {
	if($call == "verify") {
		if($_POST['arg1'] == "confirm") {
			$mysqli->query("UPDATE `users` SET `verified`=true WHERE `blid`=" . $_SESSION['blid']);
		} else {
			$mysqli->query("DELETE FROM `users` WHERE `blid`=" . $_SESSION['blid']);
			session_destroy();
			die();
		}
	}
} else {
	echo "gen error\treauth\n";
}
echo "\n";

function checkWebAccount($blid) {
	//ret [exists, username, blid, verified]
	global $mysqli;
	$result = $mysqli->query("SELECT * FROM `users` WHERE blid=" . $blid);
	if($result->num_rows == 0) {
		return array(false, "");
	} else {
		$obj = $result->fetch_object();
		return array(true, $obj->username, $obj->blid, $obj->verified);
	}
}
?>
