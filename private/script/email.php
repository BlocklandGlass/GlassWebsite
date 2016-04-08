<?php
require_once dirname(__DIR__) . '/class/UserManager.php';
$user = UserManager::getFromBLID(9789);
$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Blockland Glass Password Reset</title>
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1" />
    <style type="text/css">
    h1 {
      margin-top: 0;
    }

    tr {
      width: 600px;
      padding: 20px;
      font-family: Verdana;
      border-radius: 15px;
      border: 1px solid #aaa;
      background-color: #ccc;
    }

    table {
      width: 600px;
      margin: 15px auto;
    }
    </style>
  </head>
  <body>
    <table class="content">
      <tr>
        <td>
          <h1>Blockland Glass</h1>
          You forgot your password! Please click <a href="http://blocklandglass.com/user/resetPassword.php?id=9789">here to reset your password</a>
        </td>
      </tr>
      <tr>
        <td style="font-size:0.6em; text-align:center;">Email sent ' . date('H:i:s M-d-y') . '</td>
      </tr>
    </table>
  </body>
</html>
';
echo $body;
UserManager::email($user, "Password Reset", $body);
?>
