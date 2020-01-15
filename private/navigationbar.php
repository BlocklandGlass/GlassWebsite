<?php
require dirname(__FILE__) . '/autoload.php';
use Glass\UserManager;
?>
<div class="navcontainer green">
	<div class="navcontent">
		<a class="homebtn" href="/"><img style="height: 38px" src="/img/logoWhite.png" /></a>
		<ul>
			<li><a href="/addons" class="navbtn">Add-Ons</a></li>
			<li><a href="/stats" class="navbtn">Statistics</a></li>
      <!-- <li><a href="#" class="navbtn">User Lookup</a></li> -->
			<?php
				if($user = UserManager::getCurrent()) {
					if($user->inGroup("Administrator")) {
						echo '<li><a href="/admin" class="navbtn">Admin</a></li>';
					}
				}
				if(!isset($_SESSION['csrftoken'])) {
					$_SESSION['csrftoken'] = mt_rand();
				}

				if(isset($_SESSION['loggedin'])) {
					//really the login/logout buttons should be submit buttons for a form
					//unfortunately there is too much css styling on submit buttons that I can't figure out how to remove
					?>
					<li><a href="/user" class="navbtn"><?php echo(htmlspecialchars($_SESSION['username'])) ?></a></li>
					<li><a href="/logout.php" class="navbtn" onclick="document.getElementById('logoutForm').submit(); return false;">Log Out</a></li>
					<form class="hidden" id="logoutForm" action="/logout.php" method="post">
						<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
						<input type="hidden" name="redirect" value="<?php echo(htmlspecialchars($_SERVER['REQUEST_URI'])); ?>">
					</form><?php
				} else {
					?>
					<li><a href="/login.php" class="navbtn" onclick="document.getElementById('loginForm').submit(); return false;">Login</a></li>
					<form class="hidden" id="loginForm" action="/login.php" method="post">
						<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
						<input type="hidden" name="redirect" value="<?php echo(htmlspecialchars($_SERVER['REQUEST_URI'])); ?>">
					</form><?php
				}
			?>
		</ul>
	</div>
</div>
