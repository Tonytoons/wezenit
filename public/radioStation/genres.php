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

$host_url = (isset($_SERVER['HTTPS']) ? "https" : "https") . "://".$_SERVER['HTTP_HOST'];

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
/* 
echo "<pre>"; 
print_r($data_year);
echo "</pre>";   
exit; 
*/ 
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="" />
		<meta name="keywords" content="" /> 
		
		<link href="<?=$host_url?>/radioStation/css/bootstrap.min.css?v=20181004-1" rel="stylesheet">
	    <script src="//code.jquery.com/jquery-2.0.3.min.js" type="text/javascript"></script>
	    <script src="<?=$host_url?>/radioStation/js/bootstrap.min.js"></script>
	    <script src="<?=$host_url?>/radioStation/twbs-pagination/jquery.twbsPagination.js" type="text/javascript"></script>
	    
		<!--[if lte IE 8]><script src="css/ie/html5shiv.js"></script><![endif]-->  
		<script src="<?=$host_url?>/radioStation/js/jquery.dropotron.min.js"></script>
		<script src="<?=$host_url?>/radioStation/js/jquery.scrolly.min.js"></script>
		<script src="<?=$host_url?>/radioStation/js/jquery.onvisible.min.js"></script>
		<script src="<?=$host_url?>/radioStation/js/skel.min.js"></script>
		<script src="<?=$host_url?>/radioStation/js/skel-layers.min.js"></script>
		<script src="<?=$host_url?>/radioStation/js/init.js?v20181006-2"></script> 
		
		<link rel="stylesheet" href="<?=$host_url?>/radioStation/css/skel.css" /> 
		<link rel="stylesheet" href="<?=$host_url?>/radioStation/css/style.css?v20181009-1" />
	
		<!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->
		
		
		<style>
			
			
		.musicgenre label {
		    background-color: #c6c6c6;
		    padding-right: 20px;
		    padding-left: 20px;
		    border-radius: 100px;
		    height: 38px;
		}
		
		.img-song:hover {
		    opacity: 0.8;
		    
		}
		
		.row.youtubefix {
    margin-top: 10px;
}

.song-list:hover {
        background-color: white;
    /* border: solid 1px #d1d1d1; */
    padding: 8px;
    transition: 0.5s;
}
		</style>
		
	

	<?php
							if(!empty($name)){ 
							?>
							<title> Music <?=str_replace(' and ','&',$name);?> playlist  </title>

					
						
							<?php
							}else{ 
							?>
								<title>Radio Station</title>
							<?php
							}
							?>
	</head>
	
	<?php
						//print_r($data_station);
						if(!empty($data_station->total)){ 
							
							if(!empty($data_station->items)){
								
								$items = $data_station->items;
								
						    	foreach($items as $key => $value){
						    		$link = ''; 
									if(!empty($value->songs->lienYoutube)){
										
										$link = !empty($value->songs->lienYoutube)?$value->songs->lienYoutube:''; 
										
									}
								
									preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $link, $match);
									$youtube_id = @$match[1];
									// echo $youtube_id;
									$videoid[] = $youtube_id;
								}
							    // print_r($videoid);
							    $comma_separated = implode(",", $videoid);
							    // echo $comma_separated;
							    $youtubeurlplaylist = 'https://www.youtube.com/watch_videos?video_ids=';
							    $fullurl = $youtubeurlplaylist.$comma_separated;
							    	$playlisturl = "https://www.youtube.com/embed/VIDEO_ID?playlist=".$comma_separated;
							    
							}
						} 
						?>
	<body class="no-sidebar">
<div class="col-md-8">
		<!-- Header -->
			<div id="header">

				<!-- Inner -->
					<div class="inner">
						<header>
							<?php
							if(!empty($name)){ 
							?>
							<h1>Music <?=str_replace(' and ','&',$name);?> playlist</h1> 
							<h5> The best Music <?=str_replace(' and ','&',$name);?> playlist </h5>
					
						
							<?php
							}else{ 
							?>
							<h1>Curated playlist</h1>
							<?php
							}
							?>
						</header>
					</div>
				
				<!-- Nav -->
					<nav id="nav">
						<ul>
							<li><a href="index.php">Home</a></li>
							<li>
								<a href="">Genres</a>
								<ul>
																		<li><a href="#">All genres</a></li>

									<li><a href="#">Electronic</a></li>
									<li><a href="#">Reggae</a></li>
									<li><a href="#">Rock</a></li>
									
								</ul>
							</li>
					
						</ul>
					</nav>

			</div>
			
		<!-- Main -->
			<div class="wrapper style1">

				<div class="container-fluid">
				<div class="col-md-12" style="background-color: #f8f8f8e3; margin-left: -15px;">
						<div class="row" style="    margin-top: 10px; margin-bottom: 20px">
						
							
							<div class="col-xs-12 col-sm-12 col-md-12"> 
								<form class="form-inline" action="">
								  
								  <div class="form-group col-md-6 text-left">  
								    <!--<label  for="year" style="display: block;"> Filter by year </label>-->
								    <select class="form-control" name="year" id="year"  onchange="filter();">
								    	<option value="all">Music year release</option>
								    	<?php
								    	if(1){ 
									    	foreach($data_year->items as $key=>$value){ 
									    		if($value->year>=2016){
									    ?>
									    			<option value="<?=$value->year;?>" <?=($year==$value->year)?'selected':'';?>><?=$value->year;?></option>
									    <?php
									    		}
									    	} 
								    	} 
								    	?> 
								    	<option value="2000-<?=date("Y");?>" <?=($year=='2000-'.date("Y"))?'selected':'';?>>2000 to (<?=date("Y");?>)</option> 
								    	<option value="1990-2000" <?=($year=='1990-2000')?'selected':'';?>>1990 to 2000</option> 
								    	<option value="1990" <?=($year=='1990')?'selected':'';?>><1990</option>  
								    </select> 
								    <select class="form-control" name="genres" id="genres"  onchange="filter();">
								    	<option value="all">Music genre</option>
								    	<?php
								    	if(1){ 
									    	foreach($data_genres->items as $key=>$value){ 
									    ?>
									    	<option value="<?=$value->id;?>" <?=($genre==$value->id)?'selected':'';?>><?=$value->genre;?></option>
									    <?php 
									    	}  
								    	}   
								    	?> 
								    </select> 
								  </div> 
								
								  
								  <div class="form-group col-md-6 text-right">
								    <!--<label for="sortby"  style="display: block;">Sort by :</label>-->
								    <select class="form-control" name="sortby" id="sortby"  onchange="filter();">
								    	<option value="1" <?=($sortby==1)?'selected':'';?>>Sort by Date</option>
								    	<option value="2" <?=($sortby==2)?'selected':'';?>>Sort by Name</option>
								    </select>
								     <select class="form-control" name="limit" id="limit" onchange="filter();">
								    	<option value="20" <?=($limit==20)?'selected':'';?>>20 songs</option>
								    	<option value="40" <?=($limit==40)?'selected':'';?>>40 songs</option>
								    	<option value="60" <?=($limit==60)?'selected':'';?>>60 songs</option>
								    	<option value="80" <?=($limit==80)?'selected':'';?>>80 songs</option>
								    	<option value="100" <?=($limit==100)?'selected':'';?>>100 songs</option>
								    </select>
								  </div>
								 
								</form>
							</div>
							
						</div>
					
						<div class="">
							<?php
					
							//print_r($data_station);
							 
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
									 
								?>
								<div class="col-xs-6 col-sm-6 col-md-3 song-list">
									<div class="img-song">
									<a href="<?=$link;?>" <?=$target;?> class="image featured">
										<div class="embedType"><?=$value->songs->authors;?></div>  
										<img src="<?=$img;?>" alt="<?=$value->songs->title;?>" class="img-responsive" />
									</a></div> 
									<div class="song-title block-ellipsis text-center"><?=$value->songs->title;?></div>
									<div class="musicgenre">
										 <?php
										 if(!empty($value->genres)){
										 	foreach($value->genres as $key2 => $value2){    
										 ?>  
										 <label> <a href="index.php?genre=<?=$value2->gid;?>&name=<?=str_replace('&',' and ',$value2->genre);?>"> <?=$value2->genre;?></a></label>
										 
										 <?php
										 if(0){ 
										 ?> 
										   (<?=number_format($value2->total);?> <?=($value2->total>1)?'songs':'song';?> )
										  <?php
										 }
										  ?>
										  
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
										 ?>
									</div>
								</div> 
								<?php
								}
							}
							?>
						</div>
						 
						<div class="row">  
							<div class="col-md-12 text-center"> 
								<ul id="pagination" class="pagination-sm"> 
									<ul class="pagination"> </ul> 
								</ul>
							</div>
						</div>
					     
					</div>
					
<!--					<div class="col-md-4">-->
<!--						<div class="row youtubefix">-->
							
<!--							     	<iframe width="500" height="300" src="<?php echo $playlisturl;?>" allowfullscreen > </iframe>-->
<!--<a target="_blank" href="<?php  echo $fullurl; ?>"> Listen to songs on this page on Youtube </a>-->
<!--						</div>-->
<!--					</div>-->
				</div>
				<!--<iframe width="200" height="200" src="https://www.youtube.com/embed/<?php echo $videoid['0'];?>?playlist=<?php echo $comma_separated ?>" frameborder="0" allowfullscreen>-->
			</div> 

		<!-- Footer -->
			<div id="footer">
				<div class="container">
					
					<div class="row">
						<div class="12u">
							
							<!-- Contact -->
								<section class="contact">
									<ul class="icons">
										<li><a href="#" class="icon fa-twitter"><span class="label">Twitter</span></a></li>
										<li><a href="#" class="icon fa-facebook"><span class="label">Facebook</span></a></li>
										<li><a href="#" class="icon fa-instagram"><span class="label">Instagram</span></a></li>
										<li><a href="#" class="icon fa-pinterest"><span class="label">Pinterest</span></a></li>
										<li><a href="#" class="icon fa-dribbble"><span class="label">Dribbble</span></a></li>
										<li><a href="#" class="icon fa-linkedin"><span class="label">Linkedin</span></a></li>
									</ul>
								</section>
							
							<!-- Copyright -->
								<div class="copyright">
									<ul class="menu">
										<li>&copy; Raido Station. All rights reserved.</li><li>Design: <a href="#">HTML5 UP</a></li>
									</ul>
								</div>
							
						</div>
					
					</div>
				</div>
			</div>
</div>
<div class="col-md-4">
	<div class="player" style="background-color: black; height: 100vh; position: fixed; top: 0; margin-left: -30px; padding: 10px; text-align: center;">
			<div class="row youtubefix">
							
							     	<iframe width="500" height="300" src="<?php echo $playlisturl;?>" allowfullscreen > </iframe>
<a target="_blank" href="<?php  echo $fullurl; ?>"> Listen to songs on this page on Youtube </a>
						</div>
	</div>
</div>
	</body>
</html>
<script type="text/javascript"> 
var hostUrl = '<?=$host_url;?>';
var limit = '<?=$limit;?>';
var sortby = '<?=$sortby;?>';
var genre = '<?=$genre;?>';
var year = '<?=$year;?>'; 
var aid = '<?=$aid;?>';
var name = '<?=$name;?>'; 
$(function () {
    window.pagObj = $('#pagination').twbsPagination({
    	startPage: <?=$page;?>,
        totalPages: <?=$total_page;?>,
        visiblePages: 5,
        hideOnlyOnePage: true,
		href: false,
        onPageClick: function (event, page) {
            //console.info(page + ' (from options)');
            //
        }   
    }).on('page', function (event, page) {        
    	//window.location = '/radioStation/?page='+page+'&limit='+limit+'&sortby='+sortby;
    	window.location = '/radioStation/?page='+page+'&limit='+limit+'&sortby='+sortby+'&genre='+genre+'&year='+year+'&aid='+aid+'&name='+name; 
        //console.info(page + ' (from event listening)'); 
    });  
    
    
});
 
function filter(){ 
	
	var limit = $('#limit').val(); 
	var sortby = $('#sortby').val();
	var genre = $('#genres').val();
	var year =  $('#year').val();    
	var genre_name = $('#genres ').children(':selected').text(); 
	
	window.location = '/radioStation/?page=1&limit='+limit+'&sortby='+sortby+'&genre='+genre+'&year='+year+'&aid='+aid+'&name='+genre_name;  
	
}    

</script>