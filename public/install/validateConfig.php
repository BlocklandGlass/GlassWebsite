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

  $config = json_decode(file_get_contents( dirname(__DIR__) . '/private/config.json' ));

  $success = true;
  $message = [];

  function testMysql() {
    global $config, $message, $success;
    try {
      $mysqli = @new mysqli($config->host, $config->username, $config->password);
      if($mysqli->connect_error) {
        $success = false;
        $message['mysql'] = 'Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error;
        return;
      } else {
        $message['mysql'] = "Success: " . $mysqli->host_info;
      }
    } catch(Exception $e) {
      $success = false;
      $message['mysql'] = $e;
      return;
    }

    try {
      if($mysqli->select_db($config->database)) {
        $message['mysql_db'] = "Success";
      } else {
        if($mysqli->query('CREATE DATABASE IF NOT EXISTS `' . $config->database . '`')) {
          $message['mysql_db'] = "Success: database created";
        } else {
          $message['mysql_db'] = "Failed to create database";
        }
      }

    } catch(Exception $e) {
      $success = false;
      $message['mysql_db'] = $e;
    }
  }

  testMysql();

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
    <h2>Config Validation</h2>
    <form method="post" action="configCheck.php">
      <table>
        <tr><td colspan="2">MySQL</td></tr>
        <tr>
          <td>Host</td>
          <td><?php echo $message['mysql']; ?></td>
        </tr>
        <tr>
          <td>Database Name</td>
          <td><?php echo $message['mysql_db'] ?? ""; ?></td>
        </tr>
        <tr><td colspan="2">AWS</td></tr>
        <tr>
          <td>Access Key</td>
          <td>to-do</td>
        </tr>
        <?php if($success) { ?>
        <tr>
          <td colspan="2">
            <input type="hidden" name="sub" value="true" />
            <input type="submit" value="Update Config" />
          </td>
        </tr>
        <?php } ?>
      </table>
    </form>
  </body>
</html>
