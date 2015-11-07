<?php
	if(!isset($_SESSION)) {
		session_start();
	}
?>

<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<?php if(isset($_PAGETITLE)) {
			echo '<title>' . $_PAGETITLE . '</title>';
		} else {
			echo '<title>Blockland Glass</title>';
		}
		?>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link rel="apple-touch-icon" href="apple-touch-icon.png">
		<!-- Place favicon.ico in the root directory -->

		<link rel="stylesheet" href="/css/normalize.css">
		<link rel="stylesheet" href="/css/main.css">

		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Quicksand" />

		<script src="/js/vendor/modernizr-2.8.3.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script type="text/javascript">window.jQuery || document.write('<script src="js/vendor/jquery-1.11.3.min.js"><\/script>')</script>
		<script src="/js/plugins.js"></script>
		<script src="/js/main.js"></script>

	</head>
	<body>
		<!--[if lt IE 8]>
			<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
		<![endif]-->
