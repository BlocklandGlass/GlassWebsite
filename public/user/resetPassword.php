<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';
	session_start();

	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	use Glass\UserManager;
	use Glass\NotificationManager;
	use Glass\NotificationObject;

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

      $userObj = UserManager::getFromBLID($blid);
      if($userObj->getResetKey() !== $token) {
				UserManager::invalidateResetKey($userObj->getBLID());
        $response = [
          "message" => "<h3>Invalid Reset Token</h3><p>Your password reset has been cancelled. If you restart password recovery, be sure to only use the latest recovery email!</p>" .
											 "<p style=\"font-size: 0.8em; text-align:center;\"><a href=\"/user/forgotPassword.php\">Password Recovery</a></p>",
          "form" => false
        ];
      } else if((time()-$userObj->getResetTime()) > 1800) {
        $response = [
          "message" => "<h3>Reset Expired</h3><p>You only have half an hour to reset your password after receiving a recovery email! You'll need to restart the password recovery process.</p>" .
											 "<p style=\"font-size: 0.8em; text-align:center;\"><a href=\"/user/forgotPassword.php\">Password Recovery</a></p>",
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
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
	<div class="tile" style="margin: 0 auto; max-width: 500px">
	  <?php if($response["message"] !== null) {
	    echo $response["message"];
	  }

	  if($response["form"]) {
	  ?>
		<h2>Password Recovery</h2>
		<p>
			Please enter a your new password for <strong><?php echo $userObj->getUsername() ?></strong> below.
		</p>
	  <form method="post" action="resetPassword.php?token=<?php echo urlencode($_REQUEST['token']); ?>&id=<?php echo ($_REQUEST['id']+0) ?>">
	    <table class="formtable" style="width: 100%;">
	      <tr>
	        <td>Password</td>
	        <td><input type="password" name="password" /></td>
	      </tr>
	      <tr>
	        <td>Confirm Password</td>
	        <td><input type="password" name="confirm" /></td>
	      </tr>
	      <tr>
	        <td colspan="2"><input type="submit" value="Update Password" /></td>
	      </tr>
	    </table>
	  </form>
	  <?php } ?>
	</div>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
