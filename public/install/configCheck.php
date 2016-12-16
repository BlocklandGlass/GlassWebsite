<?php
	require dirname(__DIR__) . '/../private/autoload.php';
  use Glass\InstallationManager;
  if(!isset($_SESSION)) {
    session_start();
  }

  $root = $_SESSION['root'] ?? false;
  if(!$root) {
    header('Location: /install');
    die();
  }

  if($_POST['sub'] ?? false) {
    $obj = new stdClass();
    $obj->database = $_POST['mysql_name'] ?? "";
    $obj->host = $_POST['mysql_host'] ?? "";
    $obj->username = $_POST['mysql_username'] ?? "";
    $obj->password = $_POST['mysql_pass'] ?? "";

    $obj->aws_access_key_id = $_POST['aws_key'] ?? "";
    $obj->aws_secret_access_key = $_POST['aws_secret'] ?? "";
    $obj->aws_bucket = $_POST['aws_bucket'] ?? "";
    file_put_contents(dirname(__DIR__) . '/private/config.json', json_encode($obj));
  }

  function createDefaultConfig() {
    $obj = new stdClass();
    $obj->database = "blocklandGlass";
    $obj->host = "localhost";
    $obj->username = "";
    $obj->password = "";

    $obj->aws_access_key_id = "";
    $obj->aws_secret_access_key = "";
    $obj->aws_bucket = "cdn.blocklandglass.com";
    return $obj;
  }

  $fileExists = is_file( dirname(__DIR__) . '/private/config.json' );

  if($fileExists) {
    $config = json_decode(file_get_contents( dirname(__DIR__) . '/private/config.json' ));
    if($config == false || $config == null) {
      $config = createDefaultConfig();
    }
  } else {
    $config = createDefaultConfig();
  }
?>
<!doctype html>
<html>
  <head>
    <style>
    td[colspan="2"] {
      font-weight: bold;
      text-align: center;
    }
    td {
      padding: 5px;
    }
    </style>
  </head>
  <body>
    <h2>Config</h2>
    <p>
      <?php if($fileExists) { echo 'Please fill out the config below...'; } else { echo 'You have no config! Please fill out the file below'; } ?>
    </p>
    <form method="post" action="configCheck.php">
      <table>
        <tr><td colspan="2">MySQL</td></tr>
        <tr>
          <td>Database Name</td>
          <td><input type="text" name="mysql_name" value="<?php echo $config->database ?? ""; ?>" /></td>
        </tr>
        <tr>
          <td>Host</td>
          <td><input type="text" name="mysql_host" value="<?php echo $config->host ?? ""; ?>" /></td>
        </tr>
        <tr>
          <td>Username</td>
          <td><input type="text" name="mysql_username" value="<?php echo $config->username ?? ""; ?>" /></td>
        </tr>
        <tr>
          <td>Password</td>
          <td><input type="password" name="mysql_pass" value="<?php echo $config->password ?? ""; ?>" /></td>
        </tr>
        <tr><td colspan="2">AWS</td></tr>
        <tr>
          <td>Access Key</td>
          <td><input type="text" name="aws_key" value="<?php echo $config->aws_access_key_id ?? ""; ?>" /></td>
        </tr>
        <tr>
          <td>Secret Access Key</td>
          <td><input type="text" name="aws_secret" value="<?php echo $config->aws_secret_access_key ?? ""; ?>" /></td>
        </tr>
        <tr>
          <td>Bucket</td>
          <td><input type="text" name="aws_bucket" value="<?php echo $config->aws_bucket ?? ""; ?>" /></td>
        </tr>
        <tr>
          <td colspan="2">
            <input type="hidden" name="sub" value="true" />
            <input type="submit" value="Update Config" />
          </td>
        </tr>
      </table>
    </form>
    <?php if($fileExists) { ?>
    <form action="validateConfig.php" method="post">
      <input type="submit" value="Validate Config" />
    </form>
    <?php } ?>
  </body>
</html>
