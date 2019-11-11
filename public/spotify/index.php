<?php
session_start();
require 'vendor/autoload.php';
require 'config.php';

$session = new SpotifyWebAPI\Session($CLIENT_ID, $CLIENT_SECRET, $REDIRECT_URI);

$api = new SpotifyWebAPI\SpotifyWebAPI();


if (isset($_GET['code'])) {
    		
    $session->requestAccessToken($_GET['code']);
	
	$accessToken = $session->getAccessToken();
	$refreshToken = $session->getRefreshToken();
	
    $api->setAccessToken($accessToken);
	
	
	$_SESSION['code'] = $_GET['code']; 
	$_SESSION['access_token'] = $accessToken; 
	$_SESSION['refresh_token'] = $refreshToken;
	 
	echo "<pre>"; 
    print_r('accessToken : '.$accessToken); 
	echo "</pre>";
	echo "<pre>";  
    print_r('refreshToken : '.$refreshToken);
	echo "</pre>";
	echo "<pre>"; 
    print_r($api->me());
	echo "</pre>";
	//header('Location: search.php?q=DE PHAZZ');
    die();
} else {
    $options = [ 
        'scope' => [ 
            'user-read-email',
            'user-read-private'
        ],
    ];

    header('Location: ' . $session->getAuthorizeUrl($options));
    die();
} 
?>
