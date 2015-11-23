<?php
	session_start();
	require_once(realpath(dirname(__DIR__) . "/private/class/UserManager.php"));
	$user = UserManager::getCurrent();

	if(!$user) {
		header("Location: " . "/index.php");
		die();
	}

$_PAGETITLE = "Glass | Add-Ons";

include(realpath(dirname(dirname(__DIR__)) . "/private/header.php"));
include(realpath(dirname(dirname(__DIR__)) . "/private/navigationbar.php"));
?>
<div class="maincontainer">
  <form action="upload.php" method="post">
    Add-On Type: <select>
      <option>Add-On</option>
      <option>Print</option>
      <option>Brick</option>
    </select>
  </form>
  <p><b>Add-On</b><br />
  Any ordinary add-on, including either a client.cs or server.cs file.</p>
  <p><b>Add-On</b><br />
  Any ordinary add-on, including either a client.cs or server.cs file.</p>
  <p><b>Add-On</b><br />
  Any ordinary add-on, including either a client.cs or server.cs file.</p>
</div>

<?php include(realpath(dirname(dirname(__DIR__)) . "/private/footer.php")); ?>
