<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
	use Glass\BoardManager;
	use Glass\UploadManager;
  use Glass\AddonManager;
	use Glass\ScreenshotManager;

	$_PAGETITLE = "Blockland Glass | Screenshots";
	include(__DIR__ . "/../../../private/header.php");

  $id = $_REQUEST['id'] ?? 0;
  $addon = AddonManager::getFromId($id);

  if($_POST['delete'] ?? false) {
    ScreenshotManager::deleteScreenshot($_POST['sid']);
  }
?>
<style>
	#drop-box-box {
	  display: inline-block;

	  padding: 7px;
	  background-color:#cecece;

	  border-radius: 5px;
	}

	#drop-box {
	  display: inline-block;

	  padding: 15px;
	  background-color:#cecece;
	  border: 2px dashed #55acee;
	  border-radius: 5px;

	  text-align: center;
	  cursor: pointer;

	  width: 200px;
	}

	#drop-box:hover {
	  background-color: rgba(255, 255, 255, 0.5);
	}

	.image-preview {
	  display: inline-block;
	  text-align: center;

		-webkit-transition: all 0.5s ease;
	  -moz-transition: all 0.5s ease;
	  -o-transition: all 0.5s ease;
	  transition: all 0.5s ease;

		cursor: pointer;
	}

	.image-preview:hover {
	  background-color: #ffffff;
	}

	.image-preview img {
		border-radius: 2px;

		-webkit-transition: all 0.5s ease;
	  -moz-transition: all 0.5s ease;
	  -o-transition: all 0.5s ease;
	  transition: all 0.5s ease;
	}

	.image-preview:hover img {
		opacity: 0.5;
	}

	#previews {
	  overflow-x: scroll;
		word-wrap: none;
	}

	#continue {
		-webkit-transition: all 0.5s ease;
	  -moz-transition: all 0.5s ease;
	  -o-transition: all 0.5s ease;
	  transition: all 0.5s ease;
	}

	.btn {
		font-size: 0.9em;
	}

	.btn:hover {
		text-decoration: none !important;
	}

	#image-viewer {
		-webkit-transition: all 0.5s ease;
	  -moz-transition: all 0.5s ease;
	  -o-transition: all 0.5s ease;
	  transition: all 0.5s ease;

		display: none;

		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-color: rgba(0,0,0,0.7);
		z-index: 9999;
	}

	#image-view {
		position: absolute;
	  left: 50%;
	  top: 50%;

	  transform: translate(-50%, -50%);

		max-width: 90%;
		max-height: 90%;
	}

	#image-bar {
		position: absolute;
		bottom: 0;
	  left: 50%;
	  transform: translate(-50%, 0);

		background-color: #eee;

		border-radius: 10px 10px 0 0;
		min-width: 300px;
		text-align: center;

		padding: 5px 5px 10px 5px;
	}

	#image-bar a {
		margin: 0;
		padding: 5px 20px 5px 20px;
	}
</style>
<script>
  var addonId = "<?php echo $_REQUEST['id'] ?? 0; ?>";
</script>
<script src="/js/screenshotUpload.js"></script>
<div id="image-viewer">
	<img id="image-view" class="center" />
	<div id="image-bar">

		<form target="" method="post">
	    <input type="hidden" id="image-view-ssid" name="sid" value="" />
	    <input type="submit" class="btn red" name="delete" value="Delete" />
    </form>

	</div>
</div>
<div class="maincontainer" style="text-align:center">
  <?php
    include(__DIR__ . "/../../../private/navigationbar.php");
  ?>
  <div class="tile" style="text-align: left">
    <h3>Screenshots</h3>
    <p>
      Now that you've uploaded <b><?php echo htmlspecialchars($addon->getName()); ?></b>, why don't you show us what its all about and upload some screenshots?
    </p>
    <div style="text-align: center">
      <form>
        <div id="drop-box-box">
          <div id="drop-box">
            <p>Upload Screenshots</p>
          </div>
          <div>
            <input type="file" name="screenshot" id="screenshot" style="display:none"/>
          </div>
        </div>
      </form>
    </div>
		<div style="text-align: center">
			<a href="success.php?id=<?php echo $addon->getId(); ?>" type="submit" id="continue" class="btn red">Not Right Now</a>
		</div>
  </div>

  <div id="previews">
    <?php
      $screenshots = $addon->getScreenshots();
      foreach($screenshots as $sid) {
        ?>
        <div class="tile image-preview" ssid="<?php echo $sid; ?>">
          <img src="http://cdn.blocklandglass.com/screenshots/thumb/<?php echo $sid ?>"/>
        </div>
        <?php
      }
    ?>
  </div>
</div>
<?php include(__DIR__ . "/../../../private/footer.php"); ?>
