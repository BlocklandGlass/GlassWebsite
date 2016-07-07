<?php
require_once(realpath(dirname(__DIR__) . "/../private/class/UserManager.php"));
require_once(realpath(dirname(__DIR__) . "/../private/class/AddonFileHandler.php"));
$user = UserManager::getCurrent();

if(!$user) {
	header("Location: " . "/index.php");
	die();
}

$_PAGETITLE = "Glass | Upload";

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
  <h3>Select an Add-on type</h3>
  There's a few types of content that we accept here.
  <ul>
    <li><b>Add-Ons</b> are anything that starts with a server.cs or client.cs file.</li>
    <li><b>Clients</b> are typically server-specific and are mainly delivered in-game. These add-ons add GUIs or specific client effects that correspond to a modded server</li>
    <li><b>Other</b> contains colorsets, sounds, prints, or anything that isn't a normal add-on</li>
  </ul>
  <div style="text-align:center">
    <a href="upload.php?t=addon" class="typebox">Add-On<br /><br /><img src="/img/icons32/folder_vertical_zipper.png" /></a>
    <a href="upload.php?t=client" class="typebox">Client<br /><br /><img src="/img/icons32/new_window.png" /></a>
    <a href="upload.php?t=other" class="typebox">Other<br /><br /><img src="/img/icons32/billboard_picture.png" /></a>
  </div>
</div>

<?php include(realpath(dirname(dirname(__DIR__)) . "/private/footer.php")); ?>
