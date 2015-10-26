<?php
// TODO
// THIS IS STRICTLY A TEST
// ALL OF THIS SHOULD BE REWRITTEN

require_once(realpath(dirname(__FILE__) . "/analyze.php"));

$_PAGETITLE = "Glass | Code Analysis";

require_once(realpath(dirname(__DIR__) . "/private/header.php"));
require_once(realpath(dirname(__DIR__) . "/private/navigationbar.php"));


?>
<div class="maincontainer">
<table style="font-family: Courier;">
  <tbody>
<?php

$file = realpath(dirname(__FILE__) . '/files/test.cs');
$fileContent = file_get_contents($file);
$parseDat = parseTS($file);

$lineHighlight = array();

//type, func length
foreach($parseDat->functions as $func) {
  $lineHighlight[$func->pos->line][$func->pos->column] = array(1, strlen($func->value));
}

$lines = explode("\n", $fileContent);

foreach($lines as $ct=>$line) {
  echo "<tr><td>" . ($ct+1) . "</td>";
  echo "<td><pre style=\"margin:0;font-family: Courier;font-size:0.8em\">";
  $cct = 0;
  $hlLen = -1;
  foreach(str_split($line) as $char) {
    $cct++;
    if(isset($lineHighlight[$ct+1][$cct])) {
      $dat = $lineHighlight[$ct+1][$cct];
      $hlLen = $dat[1]+10;
      echo "<span style=\"color:red\">";
    }

    $hlLen--;

    if($hlLen == 0) {
      echo "</span>";
    }

    echo $char;
  }
  echo "</pre></td></tr>";
}
?>
  </tbody>
</table>
</div>

<?php require_once(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
