<?php
session_start();
$isAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;
$photoDir = "/volume1/ShareFolder/Song-hayoung/사진/";
$tempDir = "/volume1/etc/aim/photo/";

$view = $_GET['view'] ?? 'gallery';
$page = $_GET['page'] ?? 1;
$per = 150;

if ($view === 'gallery') {
    $files = glob($photoDir . "*.{jpg,jpeg,png,gif,webp,jfif}", GLOB_BRACE);

    if ($files) {
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
    } else {
        $files = [];
    }

    $total = count($files);
    $pages = ceil($total / $per);
    $items = array_slice($files, ($page-1)*$per, $per);
?>
    <?php drawPager($page, $pages, $view); ?>
    
    <div class="photo-grid">
        <?php foreach($items as $index => $item): 
            $realFileName = basename($item);
            // 1. 가장 첫 페이지의 가장 첫 번째 사진 이름을 song.jpg로 처리
            $displayFileName = ($page == 1 && $index == 0) ? "song.jpg" : $realFileName;
        ?>
            <div class="photo-card">
                <input type="checkbox" class="img-select" value="<?=htmlspecialchars($realFileName)?>">
                <img src="stream.php?file=<?=urlencode($realFileName)?>" 
                     onclick="openModal('stream.php?file=<?=urlencode($realFileName)?>&full=1')" 
                     alt="<?=htmlspecialchars($displayFileName)?>">
            </div>
        <?php endforeach; ?>
    </div>

    <?php drawPager($page, $pages, $view); ?>

<?php } elseif ($view === 'upload') { ?>
    <div style="padding:20px; background:var(--card-bg); border-radius:12px; border:1px solid var(--border-color); color:var(--text-color);">
        <h2 style="margin-top:0;">사진 업로드</h2>
        <p style="font-size:14px; color:#94a3b8;">갤러리에 추가할 사진을 선택해주세요.</p>
        
        <div style="margin:20px 0;">
            <input type="file" id="upFiles" multiple onchange="checkFiles(this)" style="display:none;">
            <label for="upFiles" class="css-btn" style="display:inline-block; width:auto; padding:10px 20px; background:#64748b; cursor:pointer;">파일 선택</label>
            <span id="file-name-display" style="margin-left:10px; font-size:14px;">선택된 파일 없음</span>
        </div>

        <button id="up-btn" class="css-btn" onclick="upload()" disabled>서버로 업로드 시작</button>

        <?php if($isAdmin): ?>
        <hr style="border:0; border-top:1px solid var(--border-color); margin:30px 0;">
        <h3>관리자 전용: 대기열 관리</h3>
        <div id="temp-list-area">
            <?php
            $tempFiles = glob($tempDir . "*.{jpg,jpeg,png,gif,webp,jfif}", GLOB_BRACE);
            if($tempFiles):
                echo '<div class="photo-grid" style="margin-bottom:20px;">';
                foreach($tempFiles as $tf):
                    $tn = basename($tf);
                    echo '<div class="photo-card">';
                    echo '<input type="checkbox" class="temp-select" value="'.htmlspecialchars($tn).'">';
                    echo '<img src="stream.php?type=temp&file='.urlencode($tn).'">';
                    echo '</div>';
                endforeach;
                echo '</div>';
                echo '<div style="display:flex; gap:10px;">';
                echo '<button class="css-btn" onclick="confirmMove()" style="flex:1;">갤러리로 이동</button>';
                echo '<button class="css-btn css-btn-gray" onclick="confirmDelete()" style="flex:1;">삭제</button>';
                echo '</div>';
            else:
                echo '<p>현재 대기 중인 사진이 없습니다.</p>';
            endif;
            ?>
        </div>
        <?php endif; ?>
    </div>
<?php }

function drawPager($p, $ts, $v) {
    if($ts <= 1) return;
    echo "<div class='pager'>";
    echo "<a href='javascript:void(0)' onclick='loadPage(1, \"$v\")'>&laquo;</a>";
    $prev = max(1, $p - 1);
    echo "<a href='javascript:void(0)' onclick='loadPage($prev, \"$v\")'>&lt;</a>";
    for($i=max(1, $p-2); $i<=min($ts, $p+2); $i++) {
        $active = ($i == $p) ? "active" : "";
        echo "<a href='javascript:void(0)' onclick='loadPage($i, \"$v\")' class='$active'>$i</a>";
    }
    $next = min($ts, $p + 1);
    echo "<a href='javascript:void(0)' onclick='loadPage($next, \"$v\")'>&gt;</a>";
    echo "<a href='javascript:void(0)' onclick='loadPage($ts, \"$v\")'>&raquo;</a>";
    echo "</div>";
}
?>