<?php
require_once(realpath(dirname(__DIR__) . "/private/header.php"));
require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
require_once(realpath(dirname(__DIR__) . "/private/class/DatabaseManager.php"));

if(isset($_POST['name']) && isset($_POST['icon']) && isset($_POST['desc'])) {
  $db = new DatabaseManager();
  $db->query("INSERT INTO `addon_boards` (`id`, `name`, `video`, `description`) VALUES (NULL, '" . $db->sanitize($_POST['name']) . "', '" . $db->sanitize($_POST['icon']) . "', '" . $db->sanitize($_POST['desc']) . "');");
}

?>
<div class="maincontainer">
  <form action="board.php" method="post">
  <table class="formtable">
    <tbody>
      <tr><td class="center" colspan="2"><h2>Log In</h2></td></tr>
      <tr><td>Board Name:</td><td><input type="text" name="name" id="name"></td></tr>
      <tr><td>Icon:</td><td><input type="text" name="icon" id="icon"></td></tr>
      <tr><td>Description:</td><td><textarea name="desc" id="desc"></textarea></tr>
      <tr><td class="center" colspan="2"><input type="submit"></td></tr>
    </tbody>
  </table>
  <input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
  <?php
    if(isset($_POST['redirect'])) {
      echo("<input type=\"hidden\" name=\"redirect\" value=\"" . htmlspecialchars($_POST['redirect']) . "\">");
    }
  ?>
  </form>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
