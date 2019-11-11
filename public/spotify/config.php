<?php
// app boytest 
$CLIENT_ID = 'bfe888749f09419ca007965cb7e75e87';  
$CLIENT_SECRET = 'ebc64020e8094dcbb8d187c90b9d9f5b'; 
//$REDIRECT_URI = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].'/spotify/index.php';//(isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].'/spotify/callback/'; 
$REDIRECT_URI = 'https://safe-tonytoons.c9users.io/spotify/index.php';
if($_SERVER['HTTP_HOST'] == 'dev.wezenit.com' || $_SERVER['HTTP_HOST'] == 'dev.wezenit.com:80')
{  
    $REDIRECT_URI = 'http://dev.wezenit.com/spotify/index.php'; 
}
else if($_SERVER['HTTP_HOST'] == 'wezenit.com' || $_SERVER['HTTP_HOST'] == 'wezenit.com:80')
{  
    $REDIRECT_URI = 'http://wezenit.com/spotify/index.php'; 
} 
/* 
session_start();

echo "<pre>"; 
print_r($_SESSION['access_token']);
echo "</pre>";
echo "<pre>";  
print_r($_SESSION['refresh_token']);
echo "</pre>";
*/
?>