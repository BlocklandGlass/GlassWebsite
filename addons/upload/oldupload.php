<?php
session_start();
require_once(realpath(dirname(__DIR__) . "/../private/class/UserManager.php"));
require_once(realpath(dirname(__DIR__) . "/../private/class/AddonFileHandler.php"));
$user = UserManager::getCurrent();

if(!$user) {
	header("Location: " . "/index.php");
	die();
}

$_PAGETITLE = "Blockland Glass | Upload";

include(realpath(dirname(dirname(__DIR__)) . "/private/header.php"));
include(realpath(dirname(dirname(__DIR__)) . "/private/navigationbar.php"));

if(isset($_FILES['uploadfile'])){
  $errors = array();
  $file_name = $_FILES['uploadfile']['name'];
  $file_size = $_FILES['uploadfile']['size'];
  $file_tmp = $_FILES['uploadfile']['tmp_name'];
  $file_type= $_FILES['uploadfile']['type'];
  $file_ext = strtolower(end(explode('.',$_FILES['uploadfile']['name'])));

  $ext= array("zip");

  if($file_ext == $ext){
    $errors[] = "extension not allowed, please choose a ZIP file.";
  }

  if(empty($errors)) {
    $filename = $user->getBlid() . "_" . $file_name;
    move_uploaded_file($file_tmp, dirname(__FILE__) . "/files/" . $user->getBlid() . "_" . $file_name);
    chmod(dirname(__FILE__) . "/files/" . $user->getBlid() . "_" . $file_name, 777);
  }
}
?>

<div class="maincontainer">
  <?php
    if(isset($_POST['type'])) {
      $type = $_POST['type'];
      if($type == 1) {
        $valid = AddonFileHandler::validateAddon($filename);
      } else if($type == 2) {
        $valid = AddonFileHandler::validatePrint($filename);
      } else if($type == 3) {
        $valid = AddonFileHandler::validateColorset($filename);
      }

      if(!$valid) {
        echo "Your add-on is missing required files!";
      }
    } else {
      header('Location: index.php');
    }
  ?>
  Tags<br />
  Screenshots<br />
  Authors<br />
  Version
</div>

<?php include(realpath(dirname(dirname(__DIR__)) . "/private/footer.php")); ?>
