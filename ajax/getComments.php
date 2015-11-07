<?php session_start(); ?>
<table class="commenttable">
<tbody>
<?php
	//header("Content-Type: application/json");
	//echo(json_encode(include(realpath(dirname(__DIR__) . "/private/json/getComments.php"))));

	$comments = include(realpath(dirname(__DIR__) . "/private/json/getComments.php"));

	foreach($comments as $comment) {
		
	}
?>
</tbody>
</table>
