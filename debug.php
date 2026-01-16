<?php
// debug.php - 파일이 왜 안 보이는지 진단
header('Content-Type: text/html; charset=utf-8');

// 설정 파일 로드
$config = include 'config.php';
$photoDir = $config['photo_dirs'][0]; // 첫 번째 사진 폴더

echo "<h2>1. 경로 확인</h2>";
echo "설정된 경로: <code>$photoDir</code><br>";

echo "<h2>2. open_basedir 확인</h2>";
$basedir = ini_get('open_basedir');
echo "현재 설정값: <code>$basedir</code><br>";

if (strpos($basedir, '/volume1/ShareFolder') !== false) {
    echo "<span style='color:green'>[성공] open_basedir에 ShareFolder 경로가 포함되어 있습니다.</span><br>";
} else {
    echo "<span style='color:red'>[실패] open_basedir에 '/volume1/ShareFolder'가 없습니다. Web Station 설정을 확인하세요.</span><br>";
}

echo "<h2>3. 폴더 읽기 테스트</h2>";
if (!file_exists($photoDir)) {
    echo "<span style='color:red'>[실패] PHP가 이 폴더를 찾을 수 없습니다 (존재하지 않거나 권한 없음).</span>";
} else {
    echo "<span style='color:green'>[성공] 폴더가 존재합니다.</span><br>";
    
    // 파일 목록 가져오기 시도
    $files = glob($photoDir . "*");
    echo "<h3>발견된 파일 개수: " . count($files) . "개</h3>";
    
    if (count($files) > 0) {
        echo "파일 리스트 예시:<br>";
        foreach (array_slice($files, 0, 5) as $f) {
            echo basename($f) . "<br>";
        }
    } else {
        echo "<span style='color:orange'>폴더는 열었지만 파일이 안 보입니다. (확장자 대소문자 문제일 수 있음)</span>";
    }
}
?>