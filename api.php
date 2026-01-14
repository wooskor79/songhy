<?php
session_start();
// 경로 설정: volume1 확인 및 마지막 슬래시 포함
$photoDir = "/volume1/ShareFolder/Song-hayoung/사진/"; 
$videoDir = "/volume/ShareFolder/Song-hayoung/영상/"; // 경로 수정
$tempDir = "/volume1/etc/aim/photo/";
$pwFile = "/volume1/etc/aim/password.txt";
$bgmDir = "./bgm/";

$action = $_REQUEST['action'] ?? '';
$isAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;

// 1. 로그인
if ($action === 'login') {
    $inputPw = $_POST['pw'] ?? '';
    $savedPw = trim(@file_get_contents($pwFile));
    if (!empty($savedPw) && $inputPw === $savedPw) {
        $_SESSION['admin'] = true; 
        echo "ok";
    } else {
        echo "no";
    }
    exit;
}

// 2. 로그아웃
if ($action === 'logout') {
    session_destroy();
    echo "ok";
    exit;
}

// 3. BGM 목록
if ($action === 'get_bgm') {
    $bgms = glob($bgmDir . "*.mp3");
    header('Content-Type: application/json');
    echo json_encode(array_map('basename', $bgms ?: []));
    exit;
}

// 4. 업로드 (업로드 버튼 반응 관련)
if ($action === 'upload') {
    if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
    foreach($_FILES['files']['tmp_name'] as $k => $tmp) {
        if (is_uploaded_file($tmp)) {
            move_uploaded_file($tmp, $tempDir . basename($_FILES['files']['name'][$k]));
        }
    }
    echo "ok"; exit;
}

// 5. 다운로드
if ($action === 'download') {
    $files = $_POST['files'] ?? [];
    if (count($files) === 0) exit;

    if (count($files) === 1) {
        $filePath = $photoDir . basename($files[0]);
        if (file_exists($filePath)) {
            if (ob_get_level()) ob_end_clean();
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }
    } else {
        $zip = new ZipArchive();
        $zipFileName = "SongHaYoung_Photos_" . date("Ymd_His") . ".zip";
        $zipFilePath = $tempDir . $zipFileName;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $f) {
                $filePath = $photoDir . basename($f);
                if (file_exists($filePath)) $zip->addFile($filePath, basename($f));
            }
            $zip->close();
            if (file_exists($zipFilePath)) {
                if (ob_get_level()) ob_end_clean();
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
                header('Content-Length: ' . filesize($zipFilePath));
                readfile($zipFilePath);
                unlink($zipFilePath);
                exit;
            }
        }
    }
}
?>