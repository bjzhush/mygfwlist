<?php
$content = file_get_contents('gfwlist.txt');
$content = base64_decode($content);
echo "<pre>";
var_dump($content);
exit;
