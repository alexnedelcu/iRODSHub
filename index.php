<?
  require_once('php-sdk/src/facebook.php');

  $config = array(
    'appId' => '323823657701745',
    'secret' => 'c16e3be960ead3b04c01480d59b3072a',
  );

  $facebook = new Facebook($config);
  $user_id = $facebook->getUser();
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$("p#your_groups") .click(function(){
		$(".list_group").slideToggle("slow");
	});
	
	$("p#connections") .click(function(){
		$(".list_connection").slideToggle("slow");
	});
	
	$("p#login") .click(function(){
		$(".list_group").slideToggle("slow");
		$(".list_connection").slideToggle("slow");
	});
	
});
</script>
<style type="text/css">
body {
	padding: 10px;
}

div.filler {
	height:5px;
	width:inherit;	
}

button.button_delete {
	background-image: url(img/symbol-delete.png);
	background-color: transparent;
	background-repeat: no-repeat;
	border: none;
	cursor: pointer;        /* make the cursor like hovering over an <a> element */
	height: 20px;
	padding-left: 10px;     /* make text start to the right of the image */
	vertical-align: middle; /* align the text vertically centered */
}

p.title {
	margin: 0px;
	padding: 5px;
	text-align: left;
	background: #e5eecc;
	border: solid 1px #c3c3c3;
	width: 360px;
}
div.log-in-form {
	display: none;
}
</style>
<title>iBox</title>
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
function parse_signed_request($signed_request, $secret) {
  list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

  // decode the data
  $sig = base64_url_decode($encoded_sig);
  $data = json_decode(base64_url_decode($payload), true);

  if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
    error_log('Unknown algorithm. Expected HMAC-SHA256');
    return null;
  }

  // check sig
  $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
  if ($sig !== $expected_sig) {
    error_log('Bad Signed JSON signature!');
    return null;
  }

  return $data;
}

function base64_url_decode($input) {
  return base64_decode(strtr($input, '-_', '+/'));
}

$data = parse_signed_request($_REQUEST['signed_request'], c16e3be960ead3b04c01480d59b3072a);

if (! isset($data[oauth_token]) && ! isset($data[user_id]))
{
	echo "<script>
  			var oauth_url = 'https://www.facebook.com/dialog/oauth/';
  			oauth_url += '?client_id=323823657701745';
  			oauth_url += '&redirect_uri=' + encodeURIComponent('http://apps.facebook.com/irodshub/');
		  	oauth_url += '&scope=publish_stream,user_groups,friends_groups';

  			window.top.location = oauth_url;
		  </script>";
}
?>
<table>
  <tr>
    <td id="initial-view"><p class='title' id="your_groups">YOUR GROUPS</p>
      <div class="list_group">
        <?
    if($user_id) {

      // We have a user ID, so probably a logged in user.
      // If not, we'll get an exception, which we handle below.
      try {

        $groups = $facebook->api('/me/groups','GET');
        
		// Print out a list of the groups' names
		$datas = $groups['data'];
		$string = "https://www.facebook.com/groups/";
		
		foreach ($datas as $data)
		{
			echo "<a href='$string".$data['id']."' target='_blank'>". $data['name']. "</a><br />";
		}

      } catch(FacebookApiException $e) {
        // If the user is logged out, you can have a 
        // user ID even though the access token is invalid.
        // In this case, we'll get an exception, so we'll
        // just ask the user to login again here.
        $login_url = $facebook->getLoginUrl(); 
        echo 'Please <a href="' . $login_url . '">login.</a>';
        error_log($e->getType());
        error_log($e->getMessage());
      }   
    } else {

      // No user, print a link for the user to login
      $login_url = $facebook->getLoginUrl();
      echo 'Please <a href="' . $login_url . '">login.</a>';

    }

  	?>
      </div>
      <div class="filler"></div>
      <p class='title' id="connections">BROWSE FILES</p>
      <div class="list_connection">
        <?
	foreach ($datas as $data)
	{
		if (@fopen($data['id'].'.txt', 'r'))
		{
			$group = $facebook->api($data['id'],'GET');

			echo "<table>
					<tr>
    					<td width='30px'><img src='".$group['icon']."'/></td>".
			 		   "<td width='300px'>".$data['name']."</td>".
					   "<td ><button class='button_delete' onClick='deleteLink(".$data['id'].")'/></td>".
					"</tr>".
				 "</table>";
		}
	}
?>
      </div>
      <div class="filler"></div>
      <p class='title' id="login">LOG IN</p>
      
      <p>Click the log in bar to access your iRODS server. </p>
      
      <div class="log-in-form">
        <form>
          <input type="text" placeholder="Instant Search" id="path"/>
          <br />
          <select id="group_id">
            <?
 if($user_id) {
 	foreach ($datas as $data)
	{
		if (!@fopen($data['id'].'.txt', 'r'))
		{
			echo "<option value='".$data['id']."'>".$data['name']."</option><br />";
		}
	}
 }
 ?>
          </select>
          <input type="submit" value="Link" onclick="linkGroupWCollection()" />
        </form>
      </div></td>
    <td></td>
  </tr>
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

function deleteLink(id) {
	
	var fileName = id + ".txt";
	
	xmlhttp.open("GET", "unlink.php?fileName="+fileName, true);
	xmlhttp.send();
	
	/*
	xmlhttp.onreadystatechange = function()
  	{
	  	if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			alert("Deteled!");
		}
	}
	*/
}

function linkGroupWCollection() {
	
	var path = document.getElementById("path").value;
	var id = document.getElementById("group_id").value;
	
	if (path == "")
	{
		alert("Error: Invalid Path!");
		return;
	}
		
	xmlhttp.open("GET", "printFile.php?path="+path+"&group_id="+id, false);
	xmlhttp.send();

	}
</script>
</body>
</html>