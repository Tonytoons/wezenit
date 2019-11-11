<?php
try
{    
    set_time_limit(7200);    
    $contentHeader = ob_get_contents();
    header("Content-Type: text/html; charset=utf-8");
    header("cache-control: must-revalidate");
    $offset = 60 * 60;
    $expire = "expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
    header($expire);
    header('Content-Length: ' . strlen($contentHeader));
    header('Vary: Accept-Encoding');
    header("Access-Control-Allow-Origin: *");
    //echo $_SERVER['HTTP_HOST']; exit;  
    /* 
    if($_SERVER['HTTP_HOST'] == 'wezenit.com' || $_SERVER['HTTP_HOST'] == 'wezenit.com:80') 
	{
	    $redirect = 'https://www.wezenit.com'.$_SERVER[REQUEST_URI];
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit();
	}
	else if($_SERVER['HTTP_HOST'] == 'dev.wezenit.com' || $_SERVER['HTTP_HOST'] == 'dev.wezenit.com:80')
	{
	    $redirect = 'https://dev.wezenit.com'.$_SERVER[REQUEST_URI];
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit();
	}
	else if($_SERVER['HTTP_HOST'] == 'safe-tonytoons.c9users.io' || $_SERVER['HTTP_HOST'] == 'safe-tonytoons.c9users.io:80')
	{
	    $redirect = 'https://safe-tonytoons.c9users.io'.$_SERVER[REQUEST_URI];
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit();
	} */ 
	
	/*
	if($_SERVER['HTTPS'] != 'https') 
	{ 
	    $redirect = 'https://dev.wezenit.com';
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit(); 
	} 
	*/  
	
	/* 
	$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	if( ($actual_link == 'https://www.wezenit.com/admin') || ($actual_link == 'http://www.wezenit.com/admin') ) 
	{ 
	    $redirect = 'https://www.wezenit.com/admin/';
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit();
	}
	else if( ($actual_link == 'https://safe-tonytoons.c9users.io/public/') || ($actual_link == 'http://safe-tonytoons.c9users.io/public/') )
	{
		$slang = 'fr';
	    if($_COOKIE['ck_lang']) $slang = $_COOKIE['ck_lang'];
	    $redirect = 'https://safe-tonytoons.c9users.io/public/'.$slang.'/';
		//header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit;
	}
	else if( ($actual_link == 'https://www.zenovly.com/') || ($actual_link == 'http://www.zenovly.com/') )
	{
	    $slang = 'fr';
	    if($_COOKIE['ck_lang']) $slang = $_COOKIE['ck_lang'];
	    $redirect = 'https://www.zenovly.com/'.$slang.'/';
		//header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit();
	}
	else if( ($actual_link == 'https://www.zenovly.com') || ($actual_link == 'http://www.zenovly.com') )
	{
	    $slang = 'fr';
	    if($_COOKIE['ck_lang']) $slang = $_COOKIE['ck_lang'];
	    $redirect = 'https://www.zenovly.com/'.$slang.'/';
		//header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit();
	}
*/
	if($_SERVER['HTTP_HOST'] == 'wezenit.com' || $_SERVER['HTTP_HOST'] == 'wezenit.com:80') 
	{
		error_reporting(0);
	 	ini_set('display_errors', 0); 
	}else{
		error_reporting(E_ALL|E_STRICT); 
	 	ini_set('display_errors', '1'); 
	}  
	//Europe/Paris
	//date_default_timezone_set('Asia/Bangkok');
	date_default_timezone_set('Europe/Paris'); 
 	mb_internal_encoding("UTF-8"); 
 	  
    /**
     * This makes our life easier when dealing with paths. Everything is relative
     * to the application root now.
     */
    chdir(dirname(__DIR__));
    
    // Decline static file requests back to the PHP built-in webserver
    if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
        return false;
    }
    
    // Setup autoloading
    require 'init_autoloader.php';
    
    // Run the application!
    Zend\Mvc\Application::init(require 'config/application.config.php')->run();
}
catch (\Exception $e)
{
    header( "location: error404.html" );
    exit(0); 
}
