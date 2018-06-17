<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';
	session_start();

	if(!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
		header("Location: /login.php");
		die();
	}
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
	use Glass\UserManager;
	use Glass\AddonManager;
	use Glass\BuildManager;
	use Glass\BuildObject;
	use Glass\BoardObject;
	use Glass\NotificationManager;
	use Glass\NotificationObject;
	$userObject = UserManager::getCurrent();

	if($userObject === false) {
		header('Location: verifyAccount.php');
		die();
	}

  //I dont have time to jump through hoops, I just want to finish this

  if(isset($_POST['sub'])) {
    if(isset($_POST['email']) && isset($_POST['conf'])) {
      if($_POST['email'] == $_POST['conf']) {
        if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
          $userObject->updateEmail($_POST['email']);
          header('Location: /user/index.php');
        } else {
          $error = "Invalid e-mail address";
        }
      } else {
        $error = "E-mail addresses do not match!";
      }
    } else {
      $error = "Missing Field";
      $_POST['email'] = "";
      $_POST['conf'] = "";
    }
  } else {
    $_POST['email'] = "";
    $_POST['conf'] = "";
  }

?>
<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php")); #636
  ?>
	<span style="font-size: 1.5em;">Hey there, <b><?php echo $_SESSION['username']; ?></b></span>
	<p>Welcome to the new Blockland Glass 2 website! We've rebuilt everything from the ground up, and to get started we're going to need some more information.</p>
  <form method="post" action="migrate.php">
    <table class="formtable">
      <tbody>
        <?php if(isset($error)) {
          echo "<tr><td colspan=\"2\"><b style=\"color:red;\">" . $error . "</b></td></tr>";
        }
        ?>
        <tr>
          <td><b>E-Mail Address</b></td>
          <td><input type="email" name="email" value="<?php echo $_POST['email']; ?>"></td>
        </tr>
        <tr>
          <td><b>Confirm</b></td>
          <td><input type="email" name="conf" value="<?php echo $_POST['conf']; ?>"></td>
        </tr>
        <tr>
          <td colspan="2">
            <input type="submit" name="sub" value="Update">
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
