<?php
	require dirname(__DIR__) . '/../../private/autoload.php';
  use Glass\UserManager;
  use Glass\AddonFileHandler;
  $user = UserManager::getCurrent();

  $_PAGETITLE = "Upload Success";

  include(realpath(dirname(dirname(__DIR__)) . "/../private/header.php"));
?>
<style>
  .typebox {
    width: 150px;
    background-color:#ccc;
    padding: 40px 15px;
    border-radius:10px;
    text-align:center;
    display: inline-block;
    margin: auto 0;
    vertical-align: middle;
    margin: 30px;
    text-decoration: none;
  }

  .typebox:hover {
    background-color: #eee;
    color: #222;
    text-decoration: none !important;
  }
</style>
<div class="maincontainer">
  <?php
    include(realpath(dirname(dirname(__DIR__)) . "/../private/navigationbar.php"));
  ?>
  <div class="tile">
    <h2>Success!</h2>
    <p>
      Your add-on was uploaded successfully.<br>
      It will now be carefully inspected by our mod reviewers.
    </p>
    <p>
      <a href="/user/">View your content.</a>
    </p>
  </div>
</div>

<?php include(realpath(dirname(dirname(__DIR__)) . "/../private/footer.php")); ?>
