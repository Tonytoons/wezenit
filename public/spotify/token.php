<?php
session_start();

echo "<pre>"; 
print_r($_SESSION['access_token']);
echo "</pre>";
echo "<pre>";  
print_r($_SESSION['refresh_token']);
echo "</pre>";


?>