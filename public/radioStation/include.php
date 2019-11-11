<?php
session_start();
error_reporting(E_ALL|E_STRICT); 
ini_set('display_errors', '1');
//date_default_timezone_set('America/New_York');
mb_internal_encoding("UTF-8");
function get_web_page( $url )
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}

$host_url = (isset($_SERVER['HTTPS']) ? "https" : "https") . "://".$_SERVER['HTTP_HOST'];
$basePath = (isset($_SERVER['HTTPS']) ? "https" : "https") . "://".$_SERVER['HTTP_HOST'].'/radioStation/';

$station_id = !empty($_GET['id'])?$_GET['id']:7;
$page = !empty($_GET['page'])?$_GET['page']:1;
$limit = !empty($_GET['limit'])?$_GET['limit']:20;
$sortby = !empty($_GET['sortby'])?$_GET['sortby']:1;
$genre = !empty($_GET['genre'])?$_GET['genre']:'all';
$year = !empty($_GET['year'])?$_GET['year']:'all';
$aid = !empty($_GET['aid'])?$_GET['aid']:'all'; 
$name = !empty($_GET['name'])?$_GET['name']:'';
  
$genre = explode('-',$genre);
//print_r($genre); exit;
$genre = $genre[0];

if($limit>100) $limit=100;
if($sortby>2) $sortby=2; 
$url_api = $host_url.'/api/fr/radio/'.$station_id.'/?username=RockStar&password=Um9ja1N0YXI=&act=songs&page='.$page.'&limit='.$limit.'&sortby='.$sortby.'&genre='.$genre.'&year='.$year.'&aid='.$aid;   
//echo $url_api; exit;   
    
$data_station = get_web_page($url_api);  
//echo $data_station; 
$data_station = json_decode($data_station['content']);  
$total_page = ceil($data_station->total / $limit);   
/*
echo $total_page;
exit;  
 */ 
$url_api2 = $host_url.'/api/fr/radio/'.$station_id.'/?username=RockStar&password=Um9ja1N0YXI=&act=genreList';
//echo $url_api2; 
//exit; 
$data_genres = get_web_page($url_api2);  
$data_genres = json_decode($data_genres['content']); 

$url_api3 = $host_url.'/api/fr/radio/'.$station_id.'/?username=RockStar&password=Um9ja1N0YXI=&act=yearList';
$data_year = get_web_page($url_api3);  
$data_year = json_decode($data_year['content']);

function slugify($str) {
  // Convert to lowercase and remove whitespace
  $str = strtolower(trim($str));  

  // Replace high ascii characters
  $chars = array("ä", "ö", "ü", "ß");
  $replacements = array("ae", "oe", "ue", "ss");
  $str = str_replace($chars, $replacements, $str);
  $pattern = array("/(é|è|ë|ê)/", "/(ó|ò|ö|ô)/", "/(ú|ù|ü|û)/");
  $replacements = array("e", "o", "u");
  $str = preg_replace($pattern, $replacements, $str);

  // Remove puncuation
  $pattern = array(":", "!", "?", ".", "/", "'");
  $str = str_replace($pattern, "", $str);

  // Hyphenate any non alphanumeric characters
  $pattern = array("/[^a-z0-9-]/", "/-+/");
  $str = preg_replace($pattern, "-", $str);

  return $str;
} 
?>