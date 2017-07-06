<?php

if($_POST['password'] ?? false) {
  $pass = file_get_contents(dirname(__FILE__) . '/password.txt');
  if($_POST['password'] != trim($pass)) {
    die('bad password');
    return;
  }

  $target_file = dirname(__FILE__) . '/System_BlocklandGlass.zip';
  if(move_uploaded_file($_FILES["upload"]["tmp_name"], $target_file)) {
    echo "Success";
  } else {
    echo "Failed";
  }
  die();
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>Beta Upload</title>
    <style>
      input {
        margin: 10px;
      }
    </style>
  </head>
  <body>
    <form action="upload.php" method="post" enctype="multipart/form-data">
      <table>
        <tbody>
          <tr>
            <td>
              File
            </td>
            <td>
              <input type="file" name="upload" id="upload" />
            </td>
          </tr>
          <tr>
            <td>Password</td>
            <td>
              <input type="text" name="password" id="password" />
            </td>
          </tr>
          <tr>
            <td colspan="2" style="text-align: center">
              <input type="submit" value="Upload Update" name="submit" />
            </td>
          </tr>
        </tbody>
      </table>
    </form>
  </body>
</html>
