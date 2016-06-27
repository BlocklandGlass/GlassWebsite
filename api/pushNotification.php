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

$data = new stdClass();
$data->type = "notification";

$data->target = $blid;
$data->title = $header;
$data->text = $body;
$data->image = $image;
$data->callback = $action;
$data->duration = ($sticky ? 5000 : 0);

socket_write($socket, json_encode($data));
socket_close($socket);
?>
