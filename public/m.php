<?php
    $link = $_GET['link'];
    if(empty($link)){
    $link = $_POST["link"];}
?>
<!DOCTYPE html> 
<html lang="en">
    <head>
        <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
        <meta http-equiv="Cache-control" content="public">
    	<META HTTP-EQUIV="EXPIRES" CONTENT="<?php echo gmdate("D, d M Y H:i:s", time() + (60*60)) . " GMT"; ?>">
        <meta http-equiv="cleartype" content="on">
        <meta name="MobileOptimized" content="768">
        <meta name="HandheldFriendly" content="True">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable = no, shrink-to-fit=no">
        <title>Schema</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link type="text/css" href="https://safe-tonytoons.c9users.io/argon.css" rel="stylesheet">
        <style>
            body{}
            h2 
            {
                font-size: 2rem;
                font-weight: 600;
            }
            
            .header 
            {
                border-bottom: 2px solid #525f7f;
                margin-bottom: 30px;
                padding-bottom: 13px;
            }
        </style>
    </head>
    <body class="py-5 bg-secondary">
        <div class="container">
            <div class="header">
                <h2 class="display-2 mb-0"> URL Data Extractor </h2>
            </div>
            <form action="" method="post">
                <div class="row">
                    <div class="col-sm-6 offset-md-2">
                        <input class="form-control form-control-lg" id="inlineFormInput" type="text" placeholder="Paste or type a link" name="link" width="280" value="<?php echo $link;?>" />
                    </div>
                    <div class="col-sm-2">
                        <input class="btn btn-1 btn-outline-primary form-control form-control-lg" id="inlineFormInput"  type="submit" value="Get data"/>
                    </div>
                </div>
            </form>
            <div class="card">
                <div class="card-title">
                    <h5> Test URLs </h5>
                </div>
                <ul>
                    <li> https://www.tripadvisor.com/Restaurant_Review-g196627-d1764348-Reviews-Restaurant_Au_Jeu_De_Paume-Millau_Aveyron_Occitanie.html  </li>
                    <li>https://www.yelp.com/biz/rooftop-reds-brooklyn-2 </li>
                    <li>https://www.facebook.com/sparklabsforever/  </li>
                    <li> https://www.booking.com/hotel/fr/ha-tel-magenta.fr.html?aid=356982;label=gog235jc-hotel-XX-fr-haNtelNmagenta-unspec-fr-com-L%3Afr-O%3AwindowsS10-B%3Achrome-N%3AXX-S%3Abo-U%3AXX-H%3As;sid=634e53bb3add7b825259950224a9abd0;dist=0&sb_price_type=total&type=total&</li>
                    <li>https://www.agoda.com/d-xpress-apartment/hotel/pattaya-th.html?cid=-132</li>
                    <li> https://fr.hotels.com/ho636643/d-xpress-apartment-pattaya-thailande/</li>
                    <li> https://www.facebook.com/dxapartment/</li>
                </ul>
            </div>
<?php
    if($link)
    {
        $facebook = 0;
        if (strpos($link, 'facebook') !== false) $facebook = 1;
        if($facebook == 1)
        {
            $tags = get_meta_tags($link);
            
            //$page = file_get_contents( $link ); 
            //$title = preg_match( '/<title[^>]*>(.*?)<\/title>/ims', $page, $match ) ? $match[ 1 ] : null;  
            //echo $title;
            
            $ch = curl_init();
            $timeout = 50;
            curl_setopt($ch, CURLOPT_URL, $link);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $data = curl_exec($ch);
            curl_close($ch);
            $dom = new DomDocument();
            $dom->loadHtml($data);
            $xpath = new DOMXPath($dom);
            
            $title = preg_match( '/<title[^>]*>(.*?)<\/title>/ims', $data, $match ) ? $match[ 1 ] : null;
            if($title) $tags['title'] = $title;
            
            $metas = $dom->getElementsByTagName('meta'); //print_r($metas);
            for ($i = 0; $i < $metas->length; $i++)
            {
                $meta = $metas->item($i);
                if($meta->getAttribute('name') == 'description') $tags['description'] = $meta->getAttribute('content');
                if($meta->getAttribute('name') == 'keywords') $tags['keywords'] = $meta->getAttribute('content');
                if($meta->getAttribute('image') == 'image') $tags['image'] = $meta->getAttribute('content');
            }
            
            $json = $dom->getElementsByTagName( 'script' );
            $json = $xpath->query('//script[@type="application/ld+json"]');
            for ($x = 0; $x <= 100; $x++) 
            {
                $json1 = json_decode($json->item($x)->nodeValue, true);
                if($json1) array_push($tags, $json1);
            }
        }
        else
        {
            $tags = get_meta_tags($link);
            $site_html = file_get_contents($link);
            $matches = NULL;
            preg_match_all('~<\s*meta\s+property="(og:[^"]+)"\s+content="([^"]*)~i', $site_html, $matches);
            $ogtags = [];
            for($i=0; $i < count($matches[1]); $i++)
            {
                if(@$matches[2][$i])
                {
                    $ogtags[$matches[1][$i]] = $matches[2][$i];
                    array_push($tags, $ogtags);
                }
            }
            
            $dom = new DOMDocument();
            libxml_use_internal_errors( 1 );
            $dom->loadHTML( $site_html );
            $xpath = new DOMXpath( $dom );
            $script = $dom->getElementsByTagName( 'script' );
            $script = $xpath->query( '//script[@type="application/ld+json"]' );
            for ($x = 0; $x <= 100; $x++)
            {
                $json = json_decode($script->item($x)->nodeValue, true);
                if($json) array_push($tags, $json);
            }
        }
        echo '<div class="py-3"><pre>';
        echo json_encode ($tags, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        echo '</pre></div>';
    }
?>
        </div>
    </body>
</html>