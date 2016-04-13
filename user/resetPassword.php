<?php
	session_start();

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/BuildManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/BuildObject.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/BoardObject.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/NotificationManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/NotificationObject.php"));

  if(isset($_REQUEST['token']) && isset($_REQUEST['id'])) {
    if(isset($_REQUEST['password']) && isset($_REQUEST['confirm'])) {
      if($_REQUEST['password'] == $_REQUEST['confirm']) {
        UserManager::updatePassword($_REQUEST['id'], $_REQUEST['password']);
        header('Location: /login.php');
        return;
      } else {
        $response = [
          "message" => "Passwords dont match!",
          "form" => true
        ];
      }
    } else {
      $blid = $_REQUEST['id'];
      $token = $_REQUEST['token'];
      apc_delete('userObject_' . $_REQUEST['id']);
      $userObj = UserManager::getFromBLID($blid);
      if($userObj->getResetKey() !== $token) {
        $response = [
          "message" => "<b>Invalid reset token.</b> Did you request a password reset twice on accident?",
          "form" => false
        ];
      } else if((time()-$userObj->getResetTime()) > 1800) {
        $response = [
          "message" => "<b>Your password reset has expired!</b> Try resending the email. " . (time()-$userObj->getResetTime()),
          "form" => false
        ];
      } else {
        $response = [
          "message" => null,
          "form" => true
        ];
      }
    }
  } else {
    $response = [
      "message" => "Reset token/id missing",
      "form" => false
    ];
  }
?>
<div class="maincontainer">
  <?php if($response["message"] !== null) {
    echo $response["message"];
  }

  if($response["form"]) {
  ?>
  <form method="post" target="resetPassword.php?token=<?php echo urlencode($_REQUEST['token']); ?>&id=<?php echo ($_REQUEST['id']+0) ?>">
    <table class="formtable">
      <tr>
        <td>Password</td>
        <td><input type="password" name="password" /></td>
      </tr>
      <tr>
        <td>Confirm</td>
        <td><input type="password" name="confirm" /></td>
      </tr>
      <tr>
        <td colspan="2"><input type="submit" value="Reset" /></td>
      </tr>
    </table>
  </form>
  <?php } ?>
</div>

<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
