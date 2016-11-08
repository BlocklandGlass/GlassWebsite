<?php
require_once(realpath(dirname(__DIR__) . "/private/class/InstallationManager.php"));
?>
<!doctype html>
<html>
  <head>
  </head>
  <body>
    <h2>PHP Modules</h2>
    <p>
      Checking to see if all required PHP modules are installed...
    </p>
    <table>
      <?php
      $ct = 0;
      foreach(InstallationManager::getModuleStatus() as $mod=>$installed) {
        if(!$installed) {
          $ct++;
          echo '<tr><td>' . $mod . '</td><td>' . ($installed ? "Installed" : '<span style="color:red;">Missing!</span>') . '</td></tr>';
        }
      }

      if($ct == 0) {
        echo '<tr><td colspan="2">All required modules installed!</td></tr>';
      }
      ?>
    </table>
    <br />
    <?php if($ct == 0) { ?>
      <form method="post" action="/install/configCheck.php">
        <input type="submit" value="Continue"/>
      </form>
    <?php } ?>
  </body>
</html>
