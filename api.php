<?php
session_start();
$config = include 'config.php';

// 변수 매핑
$photoDirs = $config['photo_dirs'];
$videoDirs = $config['video_dirs'];
$tempDir   = $config['temp_dir'];
$pwFile    = $config['pw_file'];
$bgmDir    = $config['bgm_dir'];
// [추가] 캐시 디렉토리
$videoCacheDir = $config['video_cache']; 

$action  = $_REQUEST['action'] ?? '';
$isAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;

// ... (로그인, 로그아웃, BGM 등 기존 코드 유지) ...

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

// 4. 삭제
if ($action === 'delete_temp' && $isAdmin) {
    $files = $_POST['files'] ?? [];
    foreach($files as $f) {
        $target = $tempDir . basename($f);
        if(file_exists($target)) unlink($target);
    }
    echo "ok"; exit;
}

// 5. 갤러리 이동
if ($action === 'move_to_gallery' && $isAdmin) {
    $targetDir = $photoDirs[0]; 
    $files = $_POST['files'] ?? [];
    foreach($files as $f) {
        $oldPath = $tempDir . basename($f);
        if(!file_exists($oldPath)) continue;
        $filename = pathinfo($f, PATHINFO_FILENAME);
        $ext = pathinfo($f, PATHINFO_EXTENSION);
        $newFileName = $f;
        $counter = 0;
        while(file_exists($targetDir . $newFileName)) {
            $newFileName = $filename . "_" . $counter . "." . $ext;
            $counter++;
        }
        rename($oldPath, $targetDir . $newFileName);
    }
    echo "ok"; exit;
}

// 6. 업로드
if ($action === 'upload') {
    if (!file_exists($tempDir)) {
        if (!@mkdir($tempDir, 0777, true)) { echo "폴더 생성 실패"; exit; }
    }
    if (isset($_FILES['files']['name'])) {
        foreach($_FILES['files']['tmp_name'] as $k => $tmp) {
            $name = basename($_FILES['files']['name'][$k]);
            move_uploaded_file($tmp, $tempDir . $name);
        }
    }
    echo "ok"; exit;
}

// 7. 다운로드 (헬퍼 함수 포함)
function findFileInDirs($filename, $dirs) {
    foreach ($dirs as $dir) {
        $path = $dir . $filename;
        if (file_exists($path)) return $path;
    }
    return null;
}

if ($action === 'download') {
    $files = $_POST['files'] ?? [];
    if (count($files) === 0) exit;

    if (count($files) === 1) {
        $fname = basename($files[0]);
        $path = findFileInDirs($fname, $photoDirs) ?? findFileInDirs($fname, $videoDirs);
        if ($path && file_exists($path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.$fname.'"');
            header('Content-Length: '.filesize($path));
            readfile($path);
            exit;
        }
    } else {
        $zipName = "download_" . date("Ymd_His") . ".zip";
        $zipPath = $tempDir . $zipName;
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $f) {
                $fname = basename($f);
                $path = findFileInDirs($fname, $photoDirs) ?? findFileInDirs($fname, $videoDirs);
                if ($path && file_exists($path)) $zip->addFile($path, $fname);
            }
            $zip->close();
            if (file_exists($zipPath)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="'.$zipName.'"');
                header('Content-Length: '.filesize($zipPath));
                readfile($zipPath);
                unlink($zipPath);
                exit;
            }
        }
    }
}

// 8. 썸네일 저장 (브라우저 생성 분)
if ($action === 'save_thumb') {
    $file = $_POST['file'] ?? '';
    $data = $_POST['image'] ?? '';
    
    // config에서 가져온 경로 사용
    if (!file_exists($videoCacheDir)) @mkdir($videoCacheDir, 0777, true);

    if ($file && $data) {
        $data = str_replace('data:image/jpeg;base64,', '', $data);
        $data = str_replace(' ', '+', $data);
        file_put_contents($videoCacheDir . basename($file) . ".jpg", base64_decode($data));
        echo "saved";
    }
    exit;
}
?>