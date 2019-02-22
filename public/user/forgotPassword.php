<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';

  use Glass\UserManager;

  $blid = $_POST['blid'] ?? false;
  $message = "Please insert your BL_ID and we'll send you an e-mail to reset your account password.";
  $form = true;

  if($blid) {
    $user = UserManager::getFromBLID($blid);
    if($user) {
      try {
        UserManager::sendPasswordResetEmail($user);
        $message = "If the BL_ID you entered was valid, you will have just been sent an e-mail with instructions on how to reset your password.<br /><br />Please allow up to 15 minutes for the e-mail to arrive.<br /><br />If you have triggered multiple reset attempts, only the latest one will work.";
        $form = false;
      } catch(Exception $e) {
        $message = "There appears to be no e-mail address associated with your account.<br /><br />Contact a Glass team member on the Blockland Forums for help!";
        $form = false;
      }

    } else {
      $message = "If the BL_ID you entered was valid, you will have just been sent an e-mail with instructions on how to reset your password.<br /><br />Please allow up to 15 minutes for the e-mail to arrive.<br /><br />If you have triggered multiple reset attempts, only the latest one will work.";
      $form = false;
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
  <div class="tile" style="width: 50%; margin: 5px auto;">
    <h2>Forgotten Password</h2>
    <div style="background-color: #f5f5f5; color: #333; border-radius: 5px; padding: 1px 10px; margin-bottom: 15px">
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
          <td>BL_ID:</td>
          <td><input type="text" name="blid" /></td>
        </tr>
        <tr>
          <td colspan="2"><input type="submit" value="Send Recovery Email" /></td>
        </tr>
      </table>
    </form>
    <?php
      }
    ?>
  </div>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
