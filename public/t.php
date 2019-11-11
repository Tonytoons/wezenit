<?php 
// echo $_SERVER['DOCUMENT_ROOT'];
// phpinfo(); 
function  create_image(){
        $im = @imagecreate(200, 200) or die("Cannot Initialize new GD image stream");
        // $background_color = imagecolorallocate($im, 0, 0, 255);  // yellow
//   $blue = imagecolorallocate($im, 0, 0, 255);                 // blue
// $red = imagecolorallocate($im, 255, 0, 0);                  // red

// imagefilledrectangle ($im,   80,  5, 195, 60, $blue);
// imagestring($im, 5, 80, 5, "Modified!", $red);

// imagepng($im,"modified_image.png");
// imagedestroy($im);
}

create_image();
print "<img src=image.png?".date("U").">";



// header("Content-type: image/png");
// // $string = $_GET['text'];
// $im     = imagecreatefrompng("https://mediatemple.net/_img/homepage/home_move_709x399.jpg");
// $orange = imagecolorallocate($im, 220, 210, 60);
// $px     = (imagesx($im) - 7.5 * strlen($string)) / 2;
// imagestring($im, 3, $px, 9, $string, $orange);
// imagepng($im);
// imagedestroy($im);
?>