<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';

  use Glass\UserManager;

  $blid = $_POST['blid'] ?? false;
  $message = "Please insert your Blockland ID and we'll send you an e-mail to reset your account.";
  $form = true;

  if($blid) {
    $user = UserManager::getFromBLID($blid);
    if($user) {
      try {
        UserManager::sendPasswordResetEmail($user);
        $message = "You've been sent an e-mail with instructions on how to reset your password.";
        $form = false;
      } catch(Exception $e) {
        $message = "There appears to be no e-mail address associated with your account! Message a Glass team member on the Blockland Forums for help!";
        $form = false;
      }

    } else {
      $message = "There is no account with that BL_ID!";
    }
  }
?>

<?php
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
?>

<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
  <div class="tile" style="width:50%; margin: 0 auto;">
    <h2>Forgotten Password</h2>
    <div style="background-color: #fafafa; color: #666; border-radius: 5px; padding: 1px; margin-bottom: 15px">
      <p style="text-align: center">
        <?php
          if($message) {
            echo $message;
          }
        ?>
      </p>
    </div>
    <?php
      if($form) {
    ?>

    <form method="post" action="forgotPassword.php">
      <table class="formtable">
        <tr>
          <td>Blockland ID:</td>
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
