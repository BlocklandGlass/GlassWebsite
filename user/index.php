<?php
require_once(realpath(dirname(__DIR__) . "/private/header.php"));
require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
require_once(realpath(dirname(__DIR__) . "/private/class/UserHandler.php"));
?>
<div class="maincontainer">
	<span style="font-size: 1.5em;">Hey there, <b><?php echo $_SESSION['username']; ?></b></span>
  <table class="userhome">
    <tbody>
      <tr>
        <td style="width: 50%">
          <p>
            <h3>Recent Activity</h3>
            <div style="background-color: #eee; border-radius: 15px; padding: 5px; margin: 0;"><a href="#">Jincux</a> commented on <a href="#">Blockland Glass</a><br /><span style="font-size: 0.8em;">Yesterday, 4:20pm</span></div>
          </p>
        </td>
        <td>
          <p>
            <h3>My Add-Ons</h3>
            <div class="useraddon">
              <a href="#"><img style="width: 1.2em;" src="http://blocklandglass.com/icon/icons32/wrench.png" /> <span style="font-size: 1.2em; font-weight:bold;">Blockland Glass</span></a>
              <br />
              <span style="font-size: 0.8em;">
                <a href="#">Update</a> | <a href="#">Edit</a> | <a href="#">Repository</a> | <a href="#">Delete</a>
              </span>
            </div>
            <div class="useraddon">
              <a href="#"><img style="width: 1.2em;" src="http://blocklandglass.com/icon/icons32/gun.png" /> <span style="font-size: 1.2em; font-weight:bold;">Weapon Pack</span></a>
              <br />
              <span style="font-size: 0.8em;">
                <a href="#">Update</a> | <a href="#">Edit</a> | <a href="#">Repository</a> | <a href="#">Delete</a>
              </span>
            </div>
          </p>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
