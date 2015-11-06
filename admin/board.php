<?php
require_once(realpath(dirname(__DIR__) . "/private/header.php"));
require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
require_once(realpath(dirname(__DIR__) . "/private/class/DatabaseManager.php"));

if(isset($_POST['name']) && isset($_POST['icon'] && isset($_POST['cate'])) {
  $db = new DatabaseManager();
  $db->query("INSERT INTO `addon_boards` VALUES (`id`, `name`, `icon`, `subCategory`) VALUES (NULL, '" . $db->sanitize($_POST['name']) . "', '" . $db->sanitize($_POST['icon']) . "', '" . $db->sanitize($_POST['cate']) . "');")
}

?>

<div class="bigheadcontainer">
	<h1>Blockland Glass</h1>
	<h2>A service for the community, by the community</h2>
	<a href="dl.php" class="btn blue"><b>Download</b> v1.1.0-alpha.1</a><br />
	<a href="http://blocklandglass.com" class="btn green">Classic Site</a><br />
	<a href="addons" class="btn yellow">Add-Ons</a><br /><br />
</div>
<div class="maincontainer">
  <table class="formtable">
    <tbody>
      <tr><td class="center" colspan="2"><h2>Log In</h2></td></tr>
      <tr><td>Board Name:</td><td><input type="text" name="name" id="name"></td></tr>
      <tr><td>Icon:</td><td><input type="text" name="icon" id="icon"></td></tr>
      <tr><td>Sub Category:</td><td><input type="text" name="cate" id="cate"></td></tr>
      <tr><td class="center" colspan="2"><input type="submit"></td></tr>
    </tbody>
  </table>
  <input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
  <?php
    if(isset($_POST['redirect'])) {
      echo("<input type=\"hidden\" name=\"redirect\" value=\"" . htmlspecialchars($_POST['redirect']) . "\">");
    }
  ?>
</div>

<?php require_once(realpath(dirname(__FILE__) . "/private/footer.php")); ?>
