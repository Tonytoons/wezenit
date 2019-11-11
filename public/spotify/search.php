<?php
session_start();
require 'vendor/autoload.php';
require 'config.php';

$q = @$_GET['q'];
$token = @$_GET['token'];
$status = 400; 
$return = array('status'=>$status, 'items'=>"Sorry, we can't process this time please try again later!");
if(empty($refreshToken)){
	
}
$session = new SpotifyWebAPI\Session($CLIENT_ID, $CLIENT_SECRET, $REDIRECT_URI);

$api = new SpotifyWebAPI\SpotifyWebAPI(); 

$session->refreshAccessToken($token); 

$accessToken = $session->getAccessToken();


if(!empty($accessToken)){   
	if(!empty($q)){ 
		
		$api->setAccessToken($accessToken);	
		$refreshToken = $session->getRefreshToken();  
		
		$_SESSION['refresh_token'] = $refreshToken;
		//print_r($session);exit; 
		 
		$results = $api->search($q, 'artist');
		//echo "<pre>"; 
		if(!empty($results->artists->items))$status = 200; 
		$return = array('status'=>$status, 'items'=>$results->artists->items, 'accessToken'=>$accessToken, 'refreshToken'=>$refreshToken);
	}else{
		$return = array('status'=>$status, 'items'=>'Not Artist', 'accessToken'=>$accessToken, 'refreshToken'=>'');
	}    
}else{  
	$return = array('status'=>$status, 'items'=>'Invalid Access Token', 'accessToken'=>$accessToken, 'refreshToken'=>'');
}  
print_r(json_encode($return));  
exit;

//echo "</pre>"; 
/* 
foreach ($results->artists->items as $artist) {
    echo $artist->name, '<br>';
}
 */
?>