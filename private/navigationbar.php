<div class="navcontainer">
	<div class="navcontent">
		<!-- temporary nav -->
		<a class="homebtn" href="/">Blockland Glass</a>
		<ul>
			<li><a href="/addons" class="navbtn">Add-Ons</a></li>
			<li><a href="/builds" class="navbtn">Builds</a></li>
			<li><a href="/stat" class="navbtn">Statistics</a></li>
			<?php
				if(isset($_SESSION['loggedin'])) {
					$name = "BLID_" . htmlspecialchars($_SESSION['blid']);
					if(isset($_SESSION['username'])) {
						if($_SESSION['username'] != "") {
							$name = htmlspecialchars($_SESSION['username']);
						}
					}
					echo "<li><a href=\"/user\" class=\"navbtn\">" . $name . "</a></li>";
					echo "<li><a href=\"/logout.php\" class=\"navbtn\" onclick=\"document.getElementById('logoutForm').submit(); return false;\">Log Out</a></li>";
				} else {
					echo "<li><a href=\"/login.php\" class=\"navbtn\" onclick=\"document.getElementById('loginForm').submit(); return false;\">Log In</a></li>";
				}

				if(!isset($_SESSION['csrftoken'])) {
					$_SESSION['csrftoken'] = rand();
				}
				//these forms are a bit redundant but i'm not sure if login and logout will stay identical
			?>
			<form class="hidden" id="logoutForm" action="/logout.php" method="post">
				<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
				<input type="hidden" name="redirect" value="<?php echo(htmlspecialchars($_SERVER['REQUEST_URI'])); ?>">
			</form>
			<form class="hidden" id="loginForm" action="/login.php" method="post">
				<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
				<input type="hidden" name="redirect" value="<?php echo(htmlspecialchars($_SERVER['REQUEST_URI'])); ?>">
			</form>
		</ul>
	</div>
</div>
