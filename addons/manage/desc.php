<?php
$dat = require dirname(__DIR__) . "/../private/json/manageAddon.php";
?>
<form method="post" action="">
<table>
  <tbody>
    <tr>
      <td><b>Title</b></td>
      <td><input type="text" name="addonname" value="<?php echo $dat['addon']->name; ?>"/></td>
    </tr>
    <tr>
      <td><b>Description</b></td>
      <td><textarea name="description" style="font-size: 0.9em; width: 300px; height: 300px"><?php echo $dat['addon']->description; ?></textarea></td>
    </tr>
    <tr>
      <td colspan="2" style="text-align:center"><input type="submit" name="submit"/></td>
    </tr>
  </tbody>
</table>
<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
</form>
