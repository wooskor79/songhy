<?php
// stream.php

$type = $_GET['type'] ?? 'gallery';
$file = basename($_GET['file']);
$full = isset($_GET['full']); 
$isThumb = isset($_GET['thumb']); 

// 1. 경로 설정 (volume1 명시)
if ($type === 'temp') {
    $sourcePath = "/volume1/etc/aim/photo/" . $file;
} elseif ($type === 'video') {
    $sourcePath = "/volume1/ShareFolder/aimyon/묭영상/" . $file;
} else {
    $sourcePath = "/volume1/ShareFolder/Song-hayoung/사진/" . $file;
}

if (!file_exists($sourcePath)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// 2. 원본 파일 스트리밍
if ($full || ($type === 'video' && !$isThumb)) {
    $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
    if (in_array($ext, ['mp4', 'webm', 'mov', 'm4v'])) {
        header("Content-Type: video/mp4");
    } else {
        $imgInfo = @getimagesize($sourcePath);
        header("Content-Type: " . ($imgInfo['mime'] ?? 'image/jpeg'));
    }
    header("Content-Length: " . filesize($sourcePath));
    readfile($sourcePath);
    exit;
}

// 3. 캐시 로직 (썸네일 생성)
$photoCacheDir = "/volume1/etc/cache/photos/";
if (!is_dir($photoCacheDir)) @mkdir($photoCacheDir, 0777, true);

$cachePath = $photoCacheDir . "thumb_" . ($type === 'temp' ? "temp_" : "") . $file;

if (file_exists($cachePath)) {
    header("Content-Type: image/jpeg");
    header("Content-Length: " . filesize($cachePath));
    readfile($cachePath);
    exit;
}

// 4. 캐시 생성
ini_set('memory_limit', '512M');
$imgInfo = @getimagesize($sourcePath);
if (!$imgInfo) exit;

switch ($imgInfo['mime']) {
    case 'image/jpeg': $src = @imagecreatefromjpeg($sourcePath); break;
    case 'image/png':  $src = @imagecreatefrompng($sourcePath); break;
    case 'image/gif':  $src = @imagecreatefromgif($sourcePath); break;
    case 'image/webp': $src = @imagecreatefromwebp($sourcePath); break;
    default: exit;
}

if ($src) {
    $thumbSize = 400; 
    $width = imagesx($src);
    $height = imagesy($src);
    $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
    $minSide = min($width, $height);
    imagecopyresampled($thumb, $src, 0, 0, ($width-$minSide)/2, ($height-$minSide)/2, $thumbSize, $thumbSize, $minSide, $minSide);

    imagejpeg($thumb, $cachePath, 80); 
    header("Content-Type: image/jpeg");
    imagejpeg($thumb); 

    imagedestroy($src);
    imagedestroy($thumb);
}
?>