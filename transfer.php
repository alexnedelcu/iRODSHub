<?php session_start(); ?>
<?
	require_once("src/Prods.inc.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# irodshub: http://ogp.me/ns/fb/irodshub#">
  <meta property="fb:app_id" content="323823657701745" /> 
  <meta property="og:type"   content="irodshub:file" /> 
  <meta property="og:url"    content="Put your own URL to the object here" /> 
  <meta property="og:title"  content="a new file" /> 
  <meta property="og:image"  content="http://irodshub.appoverdrive.com/img/irods_small.png" /> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
<script type="text/javascript">
function postUpload()
{
  FB.api(
	'/me/irodshub:upload',
	'post',
	{ file: 'https://apps.facebook.com/irodshub' }
	);
}
function postDownload()
{
  FB.api(
	'/me/irodshub:download',
	'post',
	{ file: 'https://apps.facebook.com/irodshub' }
	);
}
</script>
<title>Transfer files</title>
</head>

<body>
<div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '323823657701745', // App ID
      channelUrl : 'http://irodshub.appoverdrive.com/channel.html', // Channel File
      status     : true, // check login status
      cookie     : true, // enable cookies to allow the server to access the session
      xfbml      : true  // parse XFBML
    });

    // Additional initialization code here
  };

  // Load the SDK Asynchronously
  (function(d){
     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement('script'); js.id = id; js.async = true;
     js.src = "//connect.facebook.net/en_US/all.js";
     ref.parentNode.insertBefore(js, ref);
   }(document));
</script>
<?
if (!isset($_SESSION['fileName']))
	$_SESSION['fileName'] = $_GET['fileName'];

if (isset($_SESSION['fileName']) and $_GET['fileName'] != "") 
	$_SESSION['fileName'] = $_GET['fileName'];

if (isset($_FILES["file"])){
		
	if (!empty($_FILES["file"]["name"])) {
	
	/**CREDIT
	Author: Alexandru Nedelcu
	Major changes: Vu Bui
	Date: August 25th, 2012
	*/				
	function uploadFileOnIrods() {
		
		$data = file_get_contents($_SESSION['fileName']);
		
		//unset($_SESSION['fileName']);
		
		$cInfo = json_decode($data, true);
	
		$server = $cInfo["host"];
		$port   = $cInfo["port"];
		$acc    = $cInfo["user"];
		$pwd    = $cInfo["password"];
		$zone   = $cInfo["zone"];
		
		$IrodsAccount = new RODSAccount($server, $port, $acc, $pwd, $zone);	
		
		// this block creates a directory and then it delete it.
		// it seems to be useless, but for some weird reason, the rest of the program works only if I do this first
		$dir=new ProdsDir($IrodsAccount,"/engineering/home/");
		$dirname = time();
		$mydir=$dir->mkdir($dirname); // creates dir
		$mydir->rmdir(); // deletes dir	
	
		$src_path = $_FILES["file"]["tmp_name"];
		$dest_path = $cInfo["path"]."/".$_FILES["file"]["name"];
		
		$file_to_be_uploaded = file_get_contents($src_path); // gets the content of the file and stores it in memory
		//echo $file_to_be_uploaded;exit;
		$myfile=new ProdsFile($IrodsAccount,$dest_path); // creates irods file object
		
		//read and print out the file
		$myfile->open("w+", "demoResc"); // creates an empty file on irods server
				
		$myfile->write($file_to_be_uploaded); // fills the empty file with the content stored in memory on irods server
	
		//close the file pointer
		$myfile->close();
	
		$myfile_check=new ProdsFile($IrodsAccount,$dest_path); // creates irods file object to check if it was successfully written
		$fileInfo = $myfile_check -> getReplInfo();
	
		if ($fileInfo[0]['size'] == filesize($src_path)) return true; // in case the file was completely uploaded
	
		return false;
	}
	
	if (uploadFileOnIrods()) {
		echo "<script>
			alert(\"".$_FILES["file"]["name"]." was uploaded!\");
			</script>";
	} else {
		echo "Upload failed!";
	}	
			
	} else { 
		echo "<script>alert('Warning: Please choose a file!');</script>";
	}
}
?>
<table width="100%" border="0">
  <tr>
    <td width="8%" height="71"><img src="img/upload_symbol-icon.gif" width="47" height="47" /></td>
    <td width="92%">
  		<form action="transfer.php" method="post" enctype="multipart/form-data">
        	<input type="file" name="file" onchange="postUpload();"/><br />
  			<input type="submit" value="Upload"/>
    	</form>
    </td>
  </tr>
</table><br />
<table width="100%" border="0">
    <div class="list-files" id="list-files">
		<?
			$data = file_get_contents($_SESSION['fileName']);
			$cInfo = json_decode($data, true);
		
			$server = $cInfo["host"];
			$port   = $cInfo["port"];
			$acc    = $cInfo["user"];
			$pwd    = $cInfo["password"];
			$zone   = $cInfo["zone"];
			
			$IrodsAccount = new RODSAccount($server, $port, $acc, $pwd, $zone);	
			
			echo "<b>PATH: </b>".$cInfo['path']."<br /><br />";
			
			echo "<tr><td></td><td><b>FILENAMES</b></td></tr>";
			
			$home=new ProdsDir($IrodsAccount, $cInfo['path']);

			//list home directory
  			$children=$home->getChildFiles();

  			//print each child's name
  			foreach($children as $child)
			{
				echo "<tr><td width='30px'><img src='img/download_symbol-icon.gif' /></td>";
    			echo "<td><a href=download.php?childName=".urlencode($child->getName())."&fileName=".$_SESSION['fileName']." onclick='postDownload();'>".$child->getName()."</a></td></tr>";
			}
    	?>
    </div>    
</table>
<script>
var xmlhttp;
if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
	xmlhttp=new XMLHttpRequest();
	}
else
	{// code for IE6, IE5
	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}

function download(childName, fileName) {
	
	xmlhttp.open("GET", "download.php?fileName="+fileName+"&childName="+childName, false);
	xmlhttp.send();
}
</script>
</body>
</html>