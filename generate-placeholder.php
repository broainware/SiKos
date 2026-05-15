<?php
// generate-placeholder.php - run once to create placeholder images
// Access: http://localhost/sikos/generate-placeholder.php

// Create hero-bg placeholder
$heroW = 1280; $heroH = 500;
$img = imagecreatetruecolor($heroW, $heroH);
$bg = imagecolorallocate($img, 74, 124, 89);
imagefill($img, 0, 0, $bg);
$text = imagecolorallocate($img, 255, 255, 255);
imagestring($img, 5, 540, 240, 'SIKOS', $text);
imagejpeg($img, __DIR__ . '/public/images/hero-bg.jpg', 90);
imagedestroy($img);

echo "✅ Hero background created at public/images/hero-bg.jpg";
