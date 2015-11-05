<div class="navcontainer">
	<div class="navcontent">
		<!-- temporary nav -->
		<a class="homebtn" href="/">Blockland Glass</a>
		<ul>
			<li><a href="/addons" class="navbtn">Add-Ons</a></li>
			<li><a href="/builds" class="navbtn">Builds</a></li>
			<li><a href="/stat" class="navbtn">Statistics</a></li>
			<?php
				if(isset($_SESSION['loggedin']))
				{
					//for some reason these only work for me with .php at the end
					//it might have to do with my version of apache
					echo "<li><a href=\"/user.php\" class=\"navbtn\">" . htmlspecialchars($_SESSION['username']) . "</a></li>";
					echo "<li><a href=\"/logout.php\" class=\"navbtn\">Log Out</a></li>";
				}
				else
				{
					echo "<li><a href=\"/login.php\" class=\"navbtn\">Log In</a></li>";
				}
			?>
		</ul>
	</div>
</div>
