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
?>
<!doctype html>
<html>
  <head>
  </head>
  <body>
    <h2>PHP Modules</h2>
    <?php if(InstallationManager::isWindows()) echo '<h3 style="color: red; font-weight: bold;">Attention: Windows operating system detected, do not use this installation for production use. Some features will not work correctly.</h3>'; ?>
    <p>
      Checking to see if all required PHP modules are installed...
    </p>
    <table>
      <?php
      $ct = 0;
      foreach(InstallationManager::getModuleStatus() as $mod=>$installed) {
        if(!$installed) {
          $ct++;
        }

        echo '<tr><td>' . $mod . '</td><td>' . ($installed ? '<span style="color: green;">Installed</span>' : '<span style="color: red;">Not Found</span>') . '</td></tr>';
      }
      ?>
    </table>
    <p>
      <?php
        if($ct == 0) {
          echo '<span style="font-weight: bold;">All required modules installed!</span>';
        } else {
          echo '<span style="font-weight: bold;">Required modules have not been found, please install and/or enable the modules marked "Not Found".</span>';
        }
      ?>
    </p>
    <br />
    <?php if($ct == 0) { ?>
      <form method="post" action="/install/configCheck.php">
        <input type="submit" value="Continue"/>
      </form>
    <?php } ?>
  </body>
</html>
