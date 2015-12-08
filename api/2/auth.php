<?php
require_once dirname(__FILE__) . "/private/ClientConnection.php";
require_once dirname(__FILE__) . "/private/BlocklandAuth.php";

//fields -
// ident    - unique numerical session identifier
// username - username
// blid     - blockland id
// version  - version of glass

header('Content-Type: text/json');
if(isset($_REQUEST['ident']) && $_REQUEST['ident'] != "") {
  // glass checks in every 5 (?) minutes
  // on the old site, this was used to keep the "currently active" list
  // this will get added back later

	$con = ClientConnection::loadFromIdentifier($_REQUEST['ident']);
  $ret = new stdClass();
  $ret->ident = $con->getIdentifier();

  if($con->isAuthed()) {
    // action   - verify
    // email    - correct email
    if($_REQUEST['action'] == "verify") {
      if($con->hasGlassAccount()) {
        $ret->result = "error";
        $ret->error = "already verified";
      } else {
        $email = $_REQUEST['email'];

        $userArray = $con->getUnverifiedAccounts();

        foreach($userArray as $user) {
          if($user->getEmail() == $email) {
            $user->setVerified(true);
            $ret->result = "success";
            break;
          }
        }
      }
    } else if($_REQUEST['action'] == "checkin") {
      $ret->status = "success";
    }
  }
  echo json_encode($ret, JSON_PRETTY_PRINT);
} else {
  $ret = new stdClass();

  $username = $_REQUEST['username'];
  $blid = $_REQUEST['blid'];
  $ip = $_SERVER['REMOTE_ADDR'];

	$con = new ClientConnection(array($blid, $username, $ip));

  $blAuth = $con->attemptBlocklandAuth();
  if($blAuth === true) {
    $ret->status = "success";
    $ret->ident = $con->getIdentifier();

    $con->setAuthed(true);

    if($con->hasGlassAccount()) {
      $ret->debug = "glass account found";
    } else {
      $userArray = $con->getUnverifiedAccounts();
      if(sizeof($userArray) > 0) {
        $ret->action = "verify";
        $verifyData = array();
        foreach($userArray as $user) {
          $verifyData[] = $user->getEmail();
        }
        $ret->verify_data = $verifyData;
      }
    }
  } else {
    $ret->status = "failed";
    $ret->msg = $blAuth[0];
    $ret->ident = $con->getIdentifier();
  }

  echo json_encode($ret, JSON_PRETTY_PRINT);
}
?>
