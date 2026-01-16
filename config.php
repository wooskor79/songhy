<?php
// [설정 파일] 경로 관리
return [
    // 1. 사진 폴더 목록 (여러 개 가능)
    'photo_dirs' => [
        '/volume1/ShareFolder/Song-hayoung/사진/',
    ],

    // 2. 영상 폴더 목록 (여러 개 가능)
    'video_dirs' => [
        '/volume1/ShareFolder/Song-hayoung/영상/',
    ],

    // 3. 업로드 임시 폴더
    'temp_dir' => '/volume1/etc/song/photo/',

    // 4. [추가됨] 캐시 저장 위치 (사진/영상 썸네일)
    'photo_cache' => '/volume1/etc/song/cache/photos/',
    'video_cache' => '/volume1/etc/song/cache/videos/',

    // 5. 비밀번호 및 BGM
    'pw_file'  => '/volume1/etc/song/password.txt',
    'bgm_dir'  => './bgm/',
];
?>