<?php
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

$host_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];

$station_id = !empty($_GET['id'])?$_GET['id']:7;
$page = !empty($_GET['page'])?$_GET['page']:1;
$limit = !empty($_GET['limit'])?$_GET['limit']:20;
$sortby = !empty($_GET['sortby'])?$_GET['sortby']:1;
$genre = !empty($_GET['genre'])?$_GET['genre']:'all';
$year = !empty($_GET['year'])?$_GET['year']:'all';
$aid = !empty($_GET['aid'])?$_GET['aid']:'all'; 
$name = !empty($_GET['name'])?$_GET['name']:'';
  

if($limit>100) $limit=100;
if($sortby>2) $sortby=2; 
$url_api = $host_url.'/api/fr/radio/'.$station_id.'/?username=RockStar&password=Um9ja1N0YXI=&act=songs&page='.$page.'&limit='.$limit.'&sortby='.$sortby.'&genre='.$genre.'&year='.$year.'&aid='.$aid;   
//echo $url_api; exit;   
    
$data_station = get_web_page($url_api);  


//echo $data_station; 
$data_station = json_decode($data_station['content']);  
$total_page = ceil($data_station->total / $limit);   
$html = '';

if(!empty($data_station->total)){ 
	
	$items = $data_station->items;
	?> 

		
     <?php
     //print_r(array_sort($items, 'start', SORT_ASC));
	foreach($items as $key => $value){
		// echo '<pre>';
		// print_r($value);
		// 	echo '<pre>';
		$img = 'images/pic07.jpg';
		if(!empty($value->songs->visual)){
			$img = $value->songs->visual; 	
		}
		$ext = strtolower(pathinfo($img, PATHINFO_EXTENSION)); 
		if($ext!='jpg' && $ext!='png' && $ext!='svg'){
			$img = 'images/pic07.jpg'; 
		}
		$link = '#'; 
		$target = '';
		if(!empty($value->songs->lienYoutube)){
			$link = $value->songs->lienYoutube; 
			$target = 'target="_blank"';
		}else if(!empty($value->songs->path)){ 
			$link = 'https://www.fip.fr/'.$value->songs->path;
			$target = 'target="_blank"'; 
		} 
		 
	$html .= '<div class="col-xs-6 col-sm-6 col-md-3 song-list">
				<div class="img-song">
				<a href="'.$link.'" '.$target.' class="image featured">
					<div class="embedType">'.$value->songs->authors.'</div>  
					<img src="'.$img.'" alt="'.$value->songs->title.'" class="img-responsive" />
				</a></div> 
				<div class="song-title block-ellipsis text-center">'.$value->songs->title.'</div>
				<div class="musicgenre"> '; 
				
				if(0){ 
				 if(!empty($value->genres)){
				 	foreach($value->genres as $key2 => $value2){    
				 ?>  
				 <!--<label> <a href="index.php?genre=<?=$value2->gid;?>&name=<?=str_replace('&',' and ',$value2->genre);?>"> <?=$value2->genre;?></a></label>-->
				 
				
				 <?php
				 
				 	}   
				 }  
				 ?> 
				  
				 <!--<a href="index.php?aid=<?=$value->author_id;?>&name=<?=str_replace('&',' and ',$value->songs->authors);?>"> Genre from spotify</a>-->
				 <?php
				 if(!empty($value->songs->anneeEditionMusique)){ 
				 ?>  
				 <!--<label> <?=!empty($value->songs->anneeEditionMusique)?$value->songs->anneeEditionMusique:date("Y");?></label>-->
				 <?php
				 }
				}
		$html .= '</div>
			</div> ';
	}
}
$status = 404;
if($html){
	$status = 200;
}
print_r(json_encode(['status'=>$status, 'songlist'=>$html,'totalpage'=>$total_page])); 
exit;
?>