<?php
// TODO
// THIS IS STRICTLY A TEST
// ALL OF THIS SHOULD BE REWRITTEN

require_once(realpath(dirname(__FILE__) . "/analyze.php"));

$_PAGETITLE = "Glass | Code Analysis";

require_once(realpath(dirname(__DIR__) . "/private/header.php"));
require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));

$results = parseTS_dir(dirname(__FILE__) . '/files/');

?>
<div class="maincontainer">
<?php
foreach($results as $file => $data) {
  $relFile = substr($file, strlen(dirname(__FILE__) . '/files/'));
  echo "<h3>" . $relFile . "</h3>";
  echo "Functions:<ul>";
  foreach($data->functions as $func) {
    echo "<li>" . $func->value . " ({$func->pos->line}:{$func->pos->column})</li>";
  }
  echo "</ul><br />";
  echo "Packages:<ul>";
  foreach($data->packages as $pack) {
    echo "<li>" . $func->value . " ({$func->pos->line}:{$func->pos->column})</li>";
  }
  echo "</ul><br />";
}
?>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
