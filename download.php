<? 
ob_start();
	require_once("src/Prods.inc.php");
	
	$fileName  = urldecode($_GET['fileName']);
	
	$data = file_get_contents($fileName);
	$cInfo = json_decode($data, true);

	$server = $cInfo["host"];
	$port   = $cInfo["port"];
	$acc    = $cInfo["user"];
	$pwd    = $cInfo["password"];
	$zone   = $cInfo["zone"];
	
	$IrodsAccount = new RODSAccount($server, $port, $acc, $pwd, $zone);
	
	$childName = $_GET['childName'];
	
	$myfile=new ProdsFile($IrodsAccount, $cInfo['path']."/".$childName);
	
	$myfile->open("r", "demoResc"); // open in secure mode
	
	$myfile->rewind();  // move the cursor to the begining of the file
	while($str=$myfile->read(1048576)) 	// Variable is updated at every 1MB read from the server
		if(!isset($content)) $content = $str;
		else $content = $content . $str;
	
	//close the file pointer
	$myfile->close();
	
	// Gather information about the content
	$len = strlen($content); // length of the file, aka the size of the file
	
	//Begin writing headers
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	
	header("Content-Type: application/force-download");
	
	//Force the download
	$header="Content-Disposition: attachment; filename=".$childName.";";
	header($header);
	header("Content-Transfer-Encoding: binary");
	//header("Content-Length: ".$len);
	header('Last-Modified: '.date('r'));
	header( "Content-Type: application/octet-stream" );
	header( "Content-Type: application/download" );
	
	ob_clean();
	flush(); 
	
	echo $content;	
?>