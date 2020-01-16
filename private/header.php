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
			// echo "was cookie! ";
			list($cookie_blid, $cookie_key) = explode(':', $cookie);


			if(is_numeric($cookie_blid) &&
				 $cookie_info = CookieManager::isValid($cookie_blid, $cookie_key)) {

				CookieManager::useKey($cookie_info['id'],
															$_SERVER['REMOTE_ADDR']);

				$last_cookie = $cookie_info['id'];
				UserManager::setSessionLoggedInBlid($cookie_info['blid']);
				// echo "Logged in via cookie! ";

				// update
				$current_user = UserManager::getCurrent();

				// issue new cookie as this has expired
				$cookie_success = CookieManager::giveCookie($current_user->getBLID(), $last_cookie ?? NULL);

				if($cookie_success) {
					// echo "gave cookie. predecessor $last_cookie. ";
				}
			} else {
				// echo "Cookie was not valid! ";
				CookieManager::clearCookie();
			}
		}
	} else {
		$needs_cookie = false;

		if(!$cookie) {
			$needs_cookie = true; // they have no cookie
		} else {
			list($cookie_blid, $cookie_key) = explode(':', $cookie);

			if(!is_numeric($cookie_blid)) {
				$needs_cookie = true; // they have a cookie but its not formatted right
			} else {
		 		$cookie_info = CookieManager::getId($cookie_blid, $cookie_key);

				if(!$cookie_info || CookieManager::isExpired($cookie_info['id'])) {
					$needs_cookie = true; // they have a cookie but it's expired
				}

				if(CookieManager::isRevoked($cookie_info['id'])) {
					//kick user off, probably a better way to do this
					include(dirname(__DIR__) . '/public/logout.php');
					session_destroy();
					header("Location: /index.php");
					die();
				}
			}
		}

		if($needs_cookie) {
			// echo " giving signed in account a cookie, successor " . ($cookie_info['id'] ?? NULL);
			$cookie_success = CookieManager::giveCookie($current_user->getBLID(), $cookie_info['id'] ?? NULL);
		}
	}

	if(($_REQUEST['killsession'] ?? false) == 2) {
		session_destroy();
	}
	$current_user = UserManager::getCurrent();
?>
<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="og:site_name" content="Blockland Glass" />
    <meta name="og:type" content="website" />
    <meta name="theme-color" content="#15B358" />
		<?php
      if(isset($_PAGEIMAGE)) {
        echo '<meta name="og:image" content="' . $_PAGEIMAGE . '" />';
      } else {
        echo '<meta name="og:image" content="/img/logo2.png" />';
      }

			if(isset($_PAGETITLE)) {
				echo '<title>' . htmlspecialchars($_PAGETITLE) . '</title>';
			} else {
				echo '<title>Blockland Glass</title>';
			}

      if(isset($_PAGETITLE)) {
        echo '<meta name="og:title" content="' . htmlspecialchars(str_replace(" | Blockland Glass", "", $_PAGETITLE)) . '" />';
      }

      if(isset($_PAGEDESCRIPTION)) {
        echo '<meta name="og:description" content="' .  htmlspecialchars(mb_strimwidth($_PAGEDESCRIPTION, 0, 200, "...")) . '" />';
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
		<link rel="stylesheet" href="/css/main.css?rev=5">

		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Quicksand" />

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<script src="/js/plugins.js"></script>
		<script src="/js/js.cookie.js"></script>
		<script src="/js/main.js"></script>
		<script src="/js/Chart.min.js"></script>

	</head>
	<body>
		<!--[if lt IE 8]>
			<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
		<![endif]-->
