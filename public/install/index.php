<?php
	require dirname(__DIR__) . '/../private/autoload.php';
  if($_POST['token'] ?? false) {
    if($_POST['token'] == file_get_contents('token.txt')) {
      session_start();
      $_SESSION['root'] = true;
      header('Location: /install/moduleCheck.php');
    } else {
      $message = "Incorrect token! A new token has been generated.";
    }
  }

  file_put_contents(dirname(__FILE__) . '/token.txt', uniqid("install_"));
?>

<!doctype html>
<html>
  <head>
    <title>Installation | Blockland Glass</title>
  </head>
  <body>
    <h2>Site Setup</h2>
    <h3>Authentication</h3>
    <?php if(isset($message)) { echo '<i>' . $message . '</i>'; } ?>
    <p>
      Hey there! We see that you've installed the Glass website, but it isn't configured yet! First, we're going to need to prove that you're a system adminstrator. We've generated a file (/install/token.txt) that isn't web accessible. Copy the contents in to the form below to get started:
    </p>
    <form method="post" action="/install/">
      <input type="text" name="token" /><br />
      <input type="submit"/>
    </form>
  </body>
</html>
