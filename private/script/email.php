<?php
require_once dirname(__DIR__) . '/class/UserManager.php';
$user = UserManager::getFromBLID(9789);
$body = '<!DOCTYPE html>
<html>
  <head>
    <style>
    h1 {
      margin-top: 0;
    }

    .content {
      color: #000;
      width: 600px;
      margin: 15px auto;
      padding: 20px;
      font-family: Verdana;
      border-radius: 15px;
      border: 1px solid #cecece;
      background-color: #ffffff;
    }

    body {
      background-color: #aaa;
    }
    </style>
  </head>
  <body>
    <div class="content">
      <h1>Blockland Glass</h1>
      You forgot your password! Please click <a href="http://blocklandglass.com/user/resetPassword.php?id=9789">here to reset your password</a>
    </div>
  </body>
</html>
';
UserManager::email($user, "Password Reset", $body);
?>
