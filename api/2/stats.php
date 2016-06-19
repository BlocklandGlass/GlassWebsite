<?php
require_once dirname(__FILE__) . "/private/ClientConnection.php";
require_once dirname(__FILE__) . "/private/BlocklandAuth.php";
require_once dirname(__DIR__) . "/../private/class/StatUsageManager.php";

//fields -
// ident    - unique numerical session identifier
// username - username
// blid     - blockland id
// version  - version of glass

header('Content-Type: text/json');
error_reporting(E_ALL);
if(isset($_REQUEST['ident']) && $_REQUEST['ident'] != "") {
	$con = ClientConnection::loadFromIdentifier($_REQUEST['ident']);
  $ret = new stdClass();
  $ret->ident = $con->getIdentifier();

  if($con->isAuthed()) {
		$ret->authed = true;
		StatUsageManager::checkExpired();
    $dat = explode("^", $_REQUEST['data']);
    foreach($dat as $ad) {
      $adat = explode(",", $ad);
      $aid = $adat[0];
      $branch = $adat[1];
      $version = $adat[2];
			StatUsageManager::addEntry($con->getBlid(), $aid, $_REQUEST['sha'], $version, ($branch == "beta"));
    }
  } else {
		$ret->authed = false;
	}
  echo json_encode($ret, JSON_PRETTY_PRINT);
} else {
	echo "bad ident (" . $_REQUEST['ident'] . ")";
}
?>
