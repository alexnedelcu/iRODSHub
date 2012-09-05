<?php
$path = $_GET["path"];
$group_id = $_GET["group_id"];

$handle = fopen($group_id.".txt", "w");

$server = $_GET["host"];
$port = $_GET["port"];
$acc = $_GET["user"];
$pwd = $_GET["password"];
$zone= $_GET["zone"];

//connect the group with a collection on iRods server
fwrite($handle, "{\"path\" : \"".$path."\",");
fwrite($handle, "\n\"host\" : "."\"".$server."\",");
fwrite($handle, "\n\"port\" : "."\"".$port."\",");
fwrite($handle, "\n\"user\" : "."\"".$acc."\",");
fwrite($handle, "\n\"password\" : "."\"".$pwd."\",");
fwrite($handle, "\n\"zone\" : "."\"".$zone."\"}");

fclose($handle);
?>