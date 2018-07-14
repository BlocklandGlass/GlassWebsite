<?php
	require_once dirname(__DIR__) . '/../private/autoload.php';
	session_start();

	if(!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
		header("Location: /login.php");
		die();
	}
	include(realpath(dirname(__DIR__) . "/../private/header.php"));
  use Glass\CookieManager;
  use Glass\UserManager;

	$userObject = UserManager::getCurrent();

	if($userObject === false) {
		header('Location: verifyAccount.php');
		die();
	}

  if($_REQUEST['revoke'] ?? false) {
    $revoke_ident = $_REQUEST['revoke'];
    if(CookieManager::ownsFamily($userObject->getBLID(), $revoke_ident)) {
      CookieManager::revokeFamily($revoke_ident);
    }
  }

  $active_chains = CookieManager::getActiveChains($userObject->getBLID(), ['created', 'used', 'ip', 'platform', 'browser'], 2);
  $usage_history = CookieManager::getUsageHistory($userObject->getBLID(), ['used', 'ip', 'platform', 'browser', 'predecessor']);

  function usage_icon($cookie) {
    $platform = $cookie['platform'];
    switch($platform) {
      case "Windows":
      case "Linux":
      case "Macintosh":
      case "Chrome OS":
        $icon = "desktop";
        break;

      case "Android":
      case "iPhone":
      case "iPad / iPod Touch":
      case "Windows Phone OS":
      case "Kindle":
      case "Kindle Fire":
      case "BlackBerry":
      case "Playbook":
      case "Tizen":
        $icon = "phone";
        break;

      case "Nintendo 3DS":
      case "New Nintendo 3DS":
      case "Nintendo Wii":
      case "Nintendo WiiU":
      case "PlayStation 3":
      case "PlayStation 4":
      case "PlayStation Vita":
      case "Xbox 360":
      case "Xbox One":
        $icon = "game_controller_round";
    }

    if($cookie['browser'] == "Wget" || $cookie['browser'] == "curl") {
      $icon = "code";
    }

    return $icon;
  }
?>
<style>
.flex-container {
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
}

.flex-container > div {
	background-color: #eee;
	padding: 10px;
	margin: 5px;
	flex-basis: calc(400px);
	flex-grow: 1;

	overflow: hidden;
}

.flex-container > div > h3 {
	margin: 0px 10px 10px 10px;
	border-bottom: 2px solid #ddd;
}

.list td {
  padding: 10px;
}

.list tr:nth-child(2n+1) td {
  background-color: #fafafa;
}

.list th {
  background-color: #2ecc71;
	padding: 5px 10px;
  color: #fff;
  font-weight: bold;
	font-size: inherit;

  text-align: left;
}

.list {
  margin: 0 auto;
	width: 100%;
	font-size: 0.8em
}

</style>

<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../private/navigationbar.php"));
  ?>
  <h2>Sessions and Usage - <?php echo $userObject->getUsername(); ?></h2>
  <div class="flex-container">
  	<div>
      <h3>Active Sessions</h3>
      <p style="font-size: 10pt; margin: 10px 10px;">
        Listed below are all the places your account is currented logged in. Accounts are automatically logged out after 30 days, or you can manually revoke access.
      </p>
      <table class="list" style="width: 100%">
        <thead>
          <tr>
            <th>
            </th>
            <th>
              Last Active
            </th>
            <th>
              Device Info
            </th>
            <th>

            </th>
          </tr>
        </thead>
        <tbody>
          <?php
            foreach($active_chains as $family=>$chain) {
              $is_current = ($chain[0]['id'] === ($cookie_info['id'] ?? false));
              $last_use = $chain[1] ?? false;

              $icon = "unknown";
              if($is_current) {
                $icon = "person";
              } else if($last_use) {
                $icon = usage_icon($last_use);
              }

              $icon_width = $icon == "phone" ? "18" : "28";

              ?>
              <tr>
                <td style="text-align: center">
                  <img src="/img/icon_minimal/<?php echo $icon; ?>.svg" style="width: <?php echo $icon_width; ?>px;" />
                </td>
              <?php

              if($is_current) {
                $ua_info = parse_user_agent();


                ?>
                <td>
                  Current Session
                  <br />
                  <span style="color: #999">
                    <?php echo $family ?>
                  </span>
                  <br />
                  <span style="color: #999">
                    <?php echo $_SERVER['REMOTE_ADDR']; ?>
                  </span>
                </td>
                <td>
                  <?php echo $ua_info['browser']; ?>
                  <br />
                  <span style="color: #999">
                    <?php echo $ua_info['platform']; ?>
                  </span>
                </td>
                <?php


              } else if($last_use) {


                ?>
                <td>
                  <?php echo date("F j, Y, g:i A T", strtotime($last_use['used'])); ?>
                  <br />
                  <span style="color: #999">
                    <?php echo $family ?>
                  </span>
                  <br />
                  <span style="color: #999">
                    <?php echo $last_use['ip']; ?>
                  </span>
                </td>
                <td>
                  <?php echo $last_use['browser']; ?>
                  <br />
                  <span style="color: #999">
                    <?php echo $last_use['platform']; ?>
                  </span>
                  <br />
                </td>
                <?php


              } else {


                ?>
                <td>
                  <?php echo $family ?>
                  <br />
                  <span style="color: #999">Unused</span>
                </td>
                <td>
                  <span style="color: #999">New Session</span>
                </td>
                <?php
              }
              ?>
                <td style="text-align: center">
                  <a href="?revoke=<?php echo htmlspecialchars($family); ?>" style="font-size: 1em; padding: 4px 10px; margin: 5px 0 10px 0;" class="btn red">Revoke</button>
                </td>
              </tr>
              <?php
            }

            if(sizeof($active_chains) == 0) {
              echo '<tr><td colspan="4" style="text-align: center">No Active Sessions!</td></tr>';
            }
          ?>
        </tbody>
      </table>
    </div>
    <div>
      <h3>Session History</h3>
      <p style="font-size: 10pt; margin: 10px 10px;">
        A history of each time your account has been accessed through a cookie. Sessions expire after an hour of inactivity, and are recreated if a valid cookie is given.
      </p>
      <table class="list" style="width: 100%">
        <thead>
          <tr>
            <th>
            </th>
            <th>
              Date
            </th>
            <th>
              Device Info
            </th>
            <th>
              IP Address
            </th>
            <th>
              Chain
            </th>
          </tr>
        </thead>
        <tbody>
          <?php
            foreach($usage_history as $usage) {
              $is_current = ($usage['id'] === ($cookie_info['id'] ?? false));

              $icon = "unknown";
              if($is_current) {
                $icon = "person";
              } else if($usage) {
                $icon = usage_icon($usage);
              }

              $icon_width = $icon == "phone" ? "18" : "28";
              ?>
              <tr>
                <td style="text-align: center">
                  <img src="/img/icon_minimal/<?php echo $icon; ?>.svg" style="width: <?php echo $icon_width; ?>px;" />
                </td>
                <td>
                  <?php echo $is_current ? "Current Session" : date("F j, Y, g:i A T", strtotime($usage['used'])); ?>
									<?php if(!$usage['predecessor']) { ?>
                  <br />
                  <span style="color: #999">
                    New Session
                  </span>
									<?php } ?>
                </td>
                <td>
                  <?php echo $usage['browser']; ?>
                  <br />
                  <span style="color: #999">
                    <?php echo $usage['platform']; ?>
                  </span>
                  <br />
                </td>
                <td>
                  <?php echo $usage['ip']; ?>
                </td>
                <td>
                  <span style="color: #999">
                    <?php echo $usage['family']; ?>
                  </span>
                </td>
              </tr>
              <?php
            }

            if(sizeof($usage_history) == 0) {
              echo '<tr><td colspan="4" style="text-align: center">No usage history.</td></tr>';
            }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include(realpath(dirname(__DIR__) . "/../private/footer.php")); ?>
