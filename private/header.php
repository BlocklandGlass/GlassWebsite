<?php
	use Glass\CookieManager;
	use Glass\UserManager;

	if(!isset($_SESSION)) {
		session_start();
	}

	if(($_REQUEST['killsession'] ?? false) == 1) {
		session_destroy();
	}

	$current_user = UserManager::getCurrent();
	$cookie = CookieManager::getCurrentCookie();

	if(!$current_user) {
		// we're not logged in. we need to check for a cookie to revive the session
		if($cookie) {
			//echo "was cookie! ";
			list($ident, $key) = explode(':', $cookie);

			$cookie_info = CookieManager::isValid($ident, $key);
			if($cookie_info) {
				$last_cookie = $cookie_info['id'];
				UserManager::setSessionLoggedInBlid($cookie_info['blid']);
				//echo "Logged in via cookie! ";

				// update
				$current_user = UserManager::getCurrent();

				// issue new cookie as this has expired
				$success = CookieManager::giveCookie($current_user->getBLID(), $last_cookie ?? NULL);

				if($success) {
					//echo "gave cookie. predecessor $last_cookie. ";
				}
			} else {
				//echo "Cookie was not valid! ";
				CookieManager::clearCookie();
			}
		}
	} else {
		if($cookie) {
			//echo "is logged with cookie, no cookie business. ";
		} else {
			$success = CookieManager::giveCookie($current_user->getBLID(), $last_cookie ?? NULL);
			//echo "is logged but had no cookie, gave cookie. ";
		}
	}

	if(($_REQUEST['killsession'] ?? false) == 2) {
		session_destroy();
	}
?>
<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="og:site_name" content="Blockland Glass" />
    <meta name="og:type" content="website" />
    <meta name="og:image" content="/img/logo2.png" />
		<?php
			if(isset($_PAGETITLE)) {
				echo '<title>' . $_PAGETITLE . '</title>';
			} else {
				echo '<title>Blockland Glass</title>';
			}

      if(isset($_PAGETITLE)) {
        echo '<meta name="og:title" content="' . str_replace("Blockland Glass | ", "", $_PAGETITLE) . '" />';
      }

      if(isset($_PAGEDESCRIPTION)) {
        echo '<meta name="og:description" content="' . mb_strimwidth($_PAGEDESCRIPTION, 0, 140, "...") . '" />';
      } else {
        echo '<meta name="og:description" content="Social and content platform for Blockland." />';
      }
		?>
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<!-- Temporary -->
		<meta http-equiv="cache-control" content="max-age=0" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="expires" content="0" />
		<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
		<meta http-equiv="pragma" content="no-cache" />

		<link rel="apple-touch-icon" href="apple-touch-icon.png">
		<!-- Place favicon.ico in the root directory -->

		<link rel="stylesheet" href="/css/normalize.css">
		<link rel="stylesheet" href="/css/main.css">

		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Quicksand" />

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script type="text/javascript">window.jQuery || document.write('<script src="/js/vendor/jquery-1.11.3.min.js"><\/script>')</script>
		<script src="/js/plugins.js"></script>
		<script src="/js/main.js"></script>
		<script src="/js/Chart.min.js"></script>

	</head>
	<body>
		<!--[if lt IE 8]>
			<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
		<![endif]-->
