<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';

  $_PAGETITLE = "Account Verification | Blockland Glass";

	require_once(realpath(dirname(__DIR__) . "/../private/header.php"));
	use Glass\AddonManager;
	?>
	<div class="maincontainer">
    <?php
      require_once(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
    ?>
		<div class="tile">
			<p>
				<h1>Account Verification</h1>
				To use the Glass website, you first need to verify your account. It's fairly simple: <a href="/dl.php">Install the add-on</a> and start Blockland!<br>A prompt should automatically appear to verify your account.
			</p>
		</div>
	</div>

<?php require_once(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
