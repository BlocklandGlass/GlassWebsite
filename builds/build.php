<?php
	$data = require(__DIR__ . "/../private/json/getBuildPage.php");

	if(isset($status['redirect'])) {
		header("Location: " . $status['redirect']);
		die();
	}
	$_PAGETITLE = "Glass | " . htmlspecialchars($data['build']->name);
	include(__DIR__ . "/../private/header.php");
	include(__DIR__ . "/../private/navigationbar.php");
?>
<div class="maincontainer">
	<p><?php echo(htmlspecialchars($data['build']->name)); ?></p>
	<p><?php echo(htmlspecialchars($data['build']->description)); ?></p>
	<a href="<?php echo($data['build']->url); ?>">Download</a>
	<p>Downloads: <?php echo($data['downloads']); ?></p>
</div>
<?php include(__DIR__ . "/../private/footer.php"); ?>
