<?php
require_once(realpath(dirname(__DIR__) . "/../private/class/DatabaseManager.php"));
require_once(realpath(dirname(__DIR__) . "/../private/class/TagManager.php"));

if(isset($_POST['name']) && isset($_POST['icon']) && isset($_POST['color'])) {
  $db = new DatabaseManager();
  $db->query("INSERT INTO `addon_tags` (`id`, `name`, `base_color`, `icon`) VALUES (NULL, '" . $db->sanitize($_POST['name']) . "', '" . $db->sanitize($_POST['color']) . "', '" . $db->sanitize($_POST['icon']) . "');");
}

?>
<table>
  <tbody>
    <tr>
      <th style="width: 200px">Tag</th>
      <th style="width: 100px">Options</th>
    </tr>
    <?php
    $tags = TagManager::getAllTags();
    foreach($tags as $tag) {
      echo "<tr>";
      echo "<td style=\"padding: 10px\">" . $tag->getHTML() . "</td>";
      echo "<td>...</td>";
      echo "</tr>";
    }
    ?>
  </tbody>
</table>
<hr />
<form action="?tab=tag" method="post">
<table class="formtable">
  <tbody>
    <tr><td class="center" colspan="2"><h3>Create Tag</h3></td></tr>
    <tr><td>Tag Name:</td><td><input type="text" name="name" id="name"></td></tr>
    <tr>
      <td>Icon:</td>
      <td>
        <select name="icon">
          <?php
          $files = scandir(dirname(__DIR__) . '/../img/icons16/');
          foreach($files as $file) {
            if(pathinfo($file)['extension'] == "png") {
              $file = substr($file, 0, strlen($file)-4);
              echo '<option value="' . $file . '">' . $file . '</option>';
            }
          }
          ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Color:</td>
      <td>
        <select name="color">
          <option value="ffcece">Red</option>
          <option value="ceffce">Green</option>
          <option value="ceceff">Blue</option>
        </select>
      </td>
    </tr>
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
