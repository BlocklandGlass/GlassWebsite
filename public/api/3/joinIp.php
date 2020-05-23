<?php
$record = dns_get_record('api.blocklandglass.com', DNS_A);
echo ("{$record[0]['ip']}\n");
?>
