<?php
	require dirname(__DIR__) . '/../../private/autoload.php';

  use Glass\AddonManager;
  use Glass\BugManager;
  use Glass\UserManager;

	require_once(realpath(dirname(__DIR__) . "/../../private/lib/Parsedown.php"));

	if(isset($_GET['id'])) {
		try {
			$addonObject = AddonManager::getFromId($_GET['id'] + 0);

      if($addonObject->getDeleted()) {
        include(__DIR__ . "/../deleted.php");
        die();
      } else if($addonObject->isRejected()) {
        include(__DIR__ . "/../rejected.php");
        die();
      }
		} catch(Exception $e) {
			//board doesn't exist
			header('Location: /addons');
			die("addon doesnt exist");
		}
	} else {
		header('Location: /addons');
		die();
	}

  $title = trim($_POST['title'] ?? "");
  $body  = trim($_POST['body']  ?? "");

  $invalid = false;
  $bodyMessage = "";
  $titleMessage = "";

  $user = UserManager::getCurrent();
  if(!$user) {
    header('Location: /login.php?rd=' . $_SERVER['REQUEST_URI']);
    return;
  }

  if($_POST ?? false) {
    if(strlen($body) < 5) {
      $bodyMessage = "Invalid body!<br />";
      $invalid = true;
    }

    if(strlen($title) < 5) {
      $titleMessage = "Invalid title!<br />";
      $invalid = true;
    }

    if(!$invalid) {
      $id = BugManager::newBug($addonObject->getId(), $user->getBLID(), $title, $body);
      header("Location: /addons/bugs/view.php?id=$id");
      return;
    }
  }


	$_PAGETITLE = "Bugs | Blockland Glass";

	include(realpath(dirname(__DIR__) . "/../../private/header.php"));
?>

<div class="maincontainer">
  <?php
    include(realpath(dirname(__DIR__) . "/../../private/navigationbar.php"));
  ?>
  <span style="font-size: 0.8em; padding-left: 10px">
    <a href="/addons/addon.php?id=<?php echo $addonObject->getId();?>"><?php echo $addonObject->getName(); ?></a> >> <a href="/addons/bugs?id=<?php echo $addonObject->getId();?>">Bugs</a> >> <strong>New</strong>
  </span>

  <div class="tile" style="width:50%; margin: 0 auto;">
		<style>
		  .invalid {
		    font-size: 0.8em;
		    color: red
		  }
		</style>

		<form method="post">
		  <table class="formtable" style="width: 100%">
				<tbody>
			    <tr>
			      <td style="width: 10%">
			        Title
			      </td>
			      <td>
			        <span class="invalid"><?php echo $titleMessage ?></span>
			        <input style="width: 100%" type="text" name="title" value="<?php echo $title; ?>" />
			      </td>
			    </tr>
			    <tr>
			      <td>
			        Body
			      </td>
			      <td>
			        <span class="invalid"><?php echo $bodyMessage ?></span>
			        <textarea style="width: 100%; height: 200px" name="body"><?php echo $body; ?></textarea>
			      </td>
			    </tr>
			    <tr>
			      <td colspan="2">
			        <input class="btn blue" type="submit" value="Submit" />
			      </td>
			    </tr>
				</tbody>
		  </table>
		</form>
	</div>
</div>
