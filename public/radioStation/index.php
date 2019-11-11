<?php
include("include.php");
?> 
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="" />
		<meta name="keywords" content="" /> 
		 
		<?php
		if(0){
		?>
		<link href="<?=$basePath;?>css/bootstrap.min.css?v=20181004-1" rel="stylesheet">
	    <script src="<?=$basePath;?>js/bootstrap.min.js"></script>
	    <?php
		} 
	    ?>  
	    
	    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/css/bootstrap-select.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/js/bootstrap-select.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
	    
	    <script src="<?=$basePath;?>twbs-pagination/jquery.twbsPagination.js" type="text/javascript"></script>
	    
	    <script type="text/javascript"> 
			var hostUrl = '<?=$host_url;?>';
			var basePath = '<?=$basePath;?>'; 
			var limit = '<?=$limit;?>';
			var sortby = '<?=$sortby;?>';
			var genre = '<?=$genre;?>';
			var year = '<?=$year;?>'; 
			var aid = '<?=$aid;?>';
			var name = '<?=$name;?>';
			var page = <?=$page;?>;
			var total_page = <?=$total_page;?>; 
			var id = <?=json_encode(explode(',', $genre));?>;   
		</script>   
		
		<!--[if lte IE 8]><script src="css/ie/html5shiv.js"></script><![endif]-->  
		<script src="<?=$basePath;?>js/jquery.dropotron.min.js"></script>
		<script src="<?=$basePath;?>js/jquery.scrolly.min.js"></script>
		<script src="<?=$basePath;?>js/jquery.onvisible.min.js"></script>
		
		
		<script src="<?=$basePath;?>js/skel.min.js"></script>
		<script src="<?=$basePath;?>js/skel-layers.min.js"></script>
	 
		 
		<script src="<?=$basePath;?>js/init.js?v20181006-2"></script> 
		
		<link rel="stylesheet" href="<?=$basePath;?>css/skel.css" /> 
		<link rel="stylesheet" href="<?=$basePath;?>css/style.css?v20181103-1" />
	
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
		<?php
		$register_display = 'block;';
		if(!empty($_SESSION['id'])){
			$register_display = 'none;'; 
		}
		?>
		<div class="register-block" style="display:<?=$register_display;?>">
			<div id="progress"></div>
			<div class="new-center">
			  <div id="register"> 
			    <i id="previousButton" class="ion-android-arrow-back"></i> 
			    <i id="forwardButton" class="ion-android-arrow-forward"></i>
			    
			    <div id="inputContainer">
			      <input id="inputField"  multiple />
			      <label id="inputLabel"></label>
			      <div id="inputProgress"></div>
			    </div>
			    
			  </div>
			</div>
		</div>
		
		<div class="col-md-8">
			<!-- Header -->
			<div id="header">

				<!-- Inner -->
				<div class="inner">
					<header>
						<?php
						if(!empty($name)){ 
							$name = str_replace(' and ','&',$name);
							$name = str_replace('-',' ',$name); 
							$name = str_replace('_',', ',$name);
							$name = str_replace('/','',$name); 
						?>
						<h1>Music <?=$name;?> playlist</h1> 
						<h5> The best Music <?=$name;?> playlist </h5>
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
						<li><a href="<?=$basePath;?>">Home</a></li>
						<li>
							<a href="">Genres</a>
							<ul>
								<li><a href="<?=$basePath;?>genre/all-All-genres/">All genres</a></li>
								<?php 
						    	if(0){ 
							    	foreach($data_genres->items as $key=>$value){ 
							    ?>
							    	<li><a href="<?=$basePath;?>genre/<?=$value->id;?>-<?=slugify(str_replace('&',' and ',$value->genre));?>/"><?=$value->genre;?></a></li>
							    <?php  
							    	}  
						    	}   
						    	?> 
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
								  
								  <div class="form-group col-md-2 text-left">  
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
								 </div>  
								 <div class="form-group col-md-4 text-left">
								    <select class="form-control" name="genres" id="genres"  multiple data-live-search="true" style="display:none;"> 
								   		<?php
								    	if(0){
								    	?>
								    	<option value="all">Music genre</option>
								    	<?php
								    	} 
								    	?>
								    	<?php
								    	if(1){ 
									    	foreach($data_genres->items as $key=>$value){ 
									    ?>
									    	<option value="<?=$value->id;?>" <?=($genre==$value->id)?'selected':'';?> data-slug="<?=slugify($value->genre);?>"><?=$value->genre;?></option>
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
					
						<div class="wrapper-list">
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
								<div class="song-list"> 
									<!-- <div class="col-xs-6 col-sm-6 col-md-3"> -->
									<div class="img-song"> 
										<a href="<?=$link;?>" <?=$target;?> class="image featured">
											<div class="embedType"><?=$value->songs->authors;?></div>  
											<img src="<?=$img;?>" alt="<?=$value->songs->title;?>" class="img-responsive" />
										</a>
									</div> 
									<div class="song-title block-ellipsis text-center"><?=$value->songs->title;?></div>
									<div class="musicgenre">
										 <?php
										 if(!empty($value->genres)){
										 	foreach($value->genres as $key2 => $value2){     
										 ?>  
										 <label> <a href="<?=$basePath;?>genre/<?=$value2->gid;?>-<?=slugify(str_replace('&',' and ',$value2->genre));?>/"> <?=$value2->genre;?></a></label>
										 
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
		
		<script src="<?=$basePath;?>js/js.js?v20181103-1"></script> 	
	</body>
</html> 
