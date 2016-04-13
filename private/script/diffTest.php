<style>
.diff {
  width: 100%;
}
.diff td {
  width: 50%;
  vertical-align : top;
  white-space    : pre;
  white-space    : pre-wrap;
  font-family    : monospace;
}

.diffDeleted {
  border: 1px solid rgb(255,192,192);
  background: rgb(255,224,224);
}

.diffInserted {
  border: 1px solid rgb(192,255,192);
  background: rgb(224,255,224);
}
</style>
<?php
require_once dirname(__DIR__) . '/class/AddonManager.php';
require_once dirname(__DIR__) . '/class/AddonUpdateObject.php';

$up = AddonManager::getUpdates(AddonManager::getFromId(4));
$up = $up[0];

$diff = $up->getDiff();

foreach($diff['changes'] as $file=>$table) {
  echo $file . "<br />" . $table . "<hr />";
}
?>
