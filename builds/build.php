<?php
	$data = require(__DIR__ . "/../private/json/getBuildPage.php");

	if(isset($data['redirect'])) {
		header("Location: " . $data['redirect']);
		die();
	}
	$_PAGETITLE = "Glass | " . htmlspecialchars($data['build']->name);
	include(__DIR__ . "/../private/header.php");
	include(__DIR__ . "/../private/navigationbar.php");

	//print_r($data);
?>
<div class="maincontainer">
	<?php
		$primary = $data['screenshots']['primaryid'];
		if($primary !== false) {
			echo("<img src=\"" . $data['screenshots']['data'][$primary]->url . "\">");
		}
	?>
	<p><?php echo(htmlspecialchars($data['build']->name)); ?></p>
	<p><?php echo(htmlspecialchars($data['build']->description)); ?></p>
	<a href="<?php echo($data['build']->url); ?>">Download</a>
	<p>Downloads: <?php echo($data['downloads']); ?></p>

	<?php
		foreach($data['screenshots']['data'] as $screenshot) {
			echo("<img src=\"" . $screenshot->thumburl . "\">");
		}
	?>
</div>
<?php include(__DIR__ . "/../private/footer.php"); ?>
