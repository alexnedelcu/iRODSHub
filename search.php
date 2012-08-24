<?php
	require_once("src/Prods.inc.php");
	
	$server = $_GET["host"];
	$port = $_GET["port"];
	$acc = $_GET["user"];
	$pwd = $_GET["password"];
	$zone= $_GET["zone"];

	//make an iRODS account object for connection
	$account = new RODSAccount($server, $port, $acc, $pwd, $zone);
	
	//create a dir object
	$home = new ProdsDir($account, "/engineering");
	
	//get the file name from URL
	$key = $_GET["q"];
	
	$Dirs = $home->findDirs(
		array(
		  $key,
		  'descendantOnly'=>true, //only search under this dir
		  'recursive'=>true       //search through all child dir as well
		), $count
	);
	
	//print the found files
	echo "<br />";
	if ($Dirs == NULL)
		echo "COLLECTION NOT FOUND.";		
	foreach($Dirs as $Dir)
	{		
		echo $Dir->getPath()."<br />";
	}
?>