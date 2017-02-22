<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';

  use Glass\UserManager;

  $blid = $_POST['blid'] ?? false;
  $message = "Please insert your Blockland ID and we'll send you an email to reset your account.";
  $form = true;

  if($blid) {
    $user = UserManager::getFromBLID($blid);
    if($user) {
      $message = "You've been sent an email with instructions on how to reset your password.";
      $form = false;
      UserManager::sendPasswordResetEmail($user);
    } else {
      $message = "There is no account with that BL_ID!";
    }
  }
?>

<?php
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
?>


<div class="maincontainer">
  <div class="tile" style="width:50%; margin: 0 auto;">
    <h2>Forgot Password</h2>
    <p>
      <?php
        if($message) {
          echo $message;
        }

        if($form) {
      ?>
    </p>
    <form method="post" target="forgotPassword.php">
      <table class="formtable">
        <tr>
          <td>BL_ID</td>
          <td><input type="text" name="blid" /></td>
        </tr>
        <tr>
          <td colspan="2"><input type="submit" value="Reset" /></td>
        </tr>
      </table>
    </form>
    <?php
      }
    ?>
  </div>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
