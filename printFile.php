<?php
$path = $_GET["path"];
$group_id = $_GET["group_id"];

$handle = fopen($group_id.".txt", "w");

//connect the group with a collection on iRods server
fwrite($handle, "{\"".$path."\" : \"".$group_id."\"}");

fclose($handle);
?>