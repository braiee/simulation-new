<?php
session_start();

// Generate a random code
$randomCode = substr(md5(uniqid(mt_rand(), true)), 0, 6);

// Store the code in the session
$_SESSION['captcha_code'] = $randomCode;

// Create an image with the code
$image = imagecreatetruecolor(100, 30);

// Set the background color
$backgroundColor = imagecolorallocate($image, 255, 255, 255);
imagefilledrectangle($image, 0, 0, 100, 30, $backgroundColor);

// Set the text color
$textColor = imagecolorallocate($image, 0, 0, 0);

// Add the code to the image
imagestring($image, 5, 20, 8, $randomCode, $textColor);

// Set the content type header
header('Content-type: image/png');

// Output the image
imagepng($image);

// Clean up
imagedestroy($image);
?>