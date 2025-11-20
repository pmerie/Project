<?php
// session_start();

// // Generate a random 5-character string
// $captcha_text = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
// $_SESSION['captcha_text'] = $captcha_text;

// //Create image
// $width = 120;
// $height = 40;
// $image = imagecreatetruecolor($width, $height);

// //Colors
// $bg_color = imagecolorallocate($image, 230, 230, 230);
// $text_color = imagecolorallocate($image, 50, 50, 50);
// $line_color = imagecolorallocate($image, 100, 100, 100);

// //Fill background
// imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// //Draw random lines for noise
// for ($i = 0; $i < 5; $i++) {
//     imageline($image, rand(0,$width), rand(0,$height), rand(0,$width), rand(0,$height), $line_color);
// }

// //Add text
// $font = __DIR__ . '/arial.ttf';
// imagettftext($image, 20, rand(-10, 10), 10, 30, $text_color, $font, $captcha_text);

// //Output image
// header('Content-Type: image/png');
// imagepng($image);
// imagedestroy($image);
?> 