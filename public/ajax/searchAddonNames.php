<?php
  require_once dirname(__DIR__) . '/../private/autoload.php';
  use Glass\DatabaseManager;

  if(isset($_GET['q'])) {
    $_REQUEST['query'] = $_GET['q'];
  }

  if(!isset($_REQUEST['query'])) {
    $query = "";
  } else {
    $query = $_REQUEST['query'];
  }

  if($query == "") {
    die("[]");
  }


  $db = new DatabaseManager();

  $sql = "";
  if(isset($_REQUEST['owner'])) {
    $sql = " AND `blid`='" . $db->sanitize($_REQUEST['owner']) .  "' ";
  }

  $res = $db->query("SELECT `id`,`name` FROM `addon_addons` WHERE `name` LIKE '" . $db->sanitize($query) . "%' AND `approved`=1 AND `deleted`=0 $sql");

  $ret = array();
  while($obj = $res->fetch_object()) {
    $ret[] = $obj;
  }

  echo json_encode($ret, JSON_PRETTY_PRINT);
?>
