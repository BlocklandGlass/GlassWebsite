<?php session_start();
	header("Content-Type: application/json");
	echo(json_encode(include(realpath(dirname(__DIR__) . "/private/json/register.php"))));
?>
