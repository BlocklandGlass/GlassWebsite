<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
	use Glass\BoardManager;
	use Glass\UploadManager;

	$_PAGETITLE = "Blockland Glass | Screenshots";
	include(__DIR__ . "/../../../private/header.php");
	include(__DIR__ . "/../../../private/navigationbar.php");

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
  text-align: center;

  width: 250px;
}

#preview {
  overflow-x: scroll;
}
</style>
<script>
  var addonId = "<?php echo $_REQUEST['id'] ?? 0; ?>";
</script>
<script src="/js/screenshotUpload.js"></script>
<div class="maincontainer" style="text-align:center">
  <div class="tile" style="text-align: left">
    <h3>Screenshots</h3>
    <p>
      Now that you've uploaded <b>-addon name-</b>, why don't you show us what its all about?
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
  </div>

  <div id="previews">
    <div class="tile image-preview">
      <img src="/img/loading.gif"/>
    </div>
  </div>
</div>
<?php include(__DIR__ . "/../../../private/footer.php"); ?>
