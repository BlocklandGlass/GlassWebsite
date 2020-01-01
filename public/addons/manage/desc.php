<?php
$dat = require dirname(__DIR__) . "/../../private/json/manageAddon.php";
?>
<form method="post" action="">
<table class="formtable">
  <tbody>
    <tr>
      <td><strong>Title</strong></td>
      <td><input type="text" name="addonname" value="<?php echo $dat['addon']->name; ?>"/></td>
    </tr>
    <tr>
      <td><strong>Summary</strong></td>
      <td><input type="text" name="summary" maxlength="150" value="<?php echo $dat['addon']->summary; ?>"/></td>
    </tr>
    <tr>
      <td><strong>Description</strong></td>
      <td><textarea name="description" style="font-size: 0.9em; width: 800px; height: 300px"><?php echo $dat['addon']->description; ?></textarea></td>
    </tr>
    <tr>
      <td colspan="2" style="text-align:center"><input class="btn blue" type="submit" name="submit"/></td>
    </tr>
  </tbody>
</table>
<input type="hidden" name="csrftoken" value="<?php echo($_SESSION['csrftoken']); ?>">
</form>
