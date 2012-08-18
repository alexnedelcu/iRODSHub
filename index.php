<?
  require_once('php-sdk/facebook.php');

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

if (! isset($data[oauth_token]) && isset($data[user_id]))
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

<h3>Test print out user's profile</h3>

 <?
    if($user_id) {

      // We have a user ID, so probably a logged in user.
      // If not, we'll get an exception, which we handle below.
      try {

        $user_profile = $facebook->api('/me','GET');
        echo "Name: " . $user_profile['name'];

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

</body>
</html>