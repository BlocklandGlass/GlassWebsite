<?php
require_once(realpath(dirname(__DIR__) . "/../private/class/UserManager.php"));
require_once(realpath(dirname(__DIR__) . "/../private/class/AddonFileHandler.php"));
$user = UserManager::getCurrent();

$_PAGETITLE = "Blockland Glass | Upload Success";

include(realpath(dirname(dirname(__DIR__)) . "/private/header.php"));
include(realpath(dirname(dirname(__DIR__)) . "/private/navigationbar.php"));
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
  <h3>Success!</h3>
  Your add-on uploaded successfully. It'll now be carefully reviewed by our moderators and reviewers and hopefully approved!
</div>

<?php include(realpath(dirname(dirname(__DIR__)) . "/private/footer.php")); ?>
