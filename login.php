<?php
	require_once("src/Prods.inc.php");
	require_once("src/RODSAccount.class.php");

	$server = $_POST["host"];
	$port = $_POST["port"];
	$acc = $_POST["user"];
	$pwd = $_POST["password"];
	$zone= $_POST["zone"];

	//make an iRODS account to check if the
	//user inputs the correct information
	try {
		$account = new RODSAccount($server, $port, $acc, $pwd, $zone);
		
		$info = $account->getUserInfo();
		echo "Login succeeded!";
		
	} catch (RODSException $e) { 
	
		echo "<p>Login failed! Error: <p/>";
    	echo $e;
	}
?>