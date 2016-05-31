<?php
$blid = $_GET['blid'];
$header = $_GET['head'];
$body = $_GET['body'];
$image = "star";
$action = "";
$sticky = "1";


ob_implicit_flush();

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_connect($socket, gethostbyname('localhost'), 27001);
socket_strerror(socket_last_error($socket));
socket_write($socket, "notification\t" . $blid . "\t" . $header . "\t" . $body . "\t" . $image . "\t" . $action . "\t" . $sticky);
socket_close($socket);
?>
