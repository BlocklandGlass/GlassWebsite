<?php
$ret = new stdClass();
$ret->status = "success";
$ret->rating = 0;

echo(json_encode($ret, JSON_PRETTY_PRINT));
?>
