/* wooskor79/songhy/songhy-bc0c7a97d1c10237fae2511fabe8ebbfa5652211/script.js */

let audio = new Audio();
let playlist = [];
let cur = 0;
let isStarted = false;
let scrollTimer = null; // 스크롤 멈춤 감지용 타이머

$(document).ready(function() {
    const savedTheme = localStorage.getItem('theme') || 'dark-mode';
    $('body').attr('class', savedTheme);
    $('#theme-checkbox').prop('checked', savedTheme === 'dark-mode');

    const lastPage = localStorage.getItem('lastPage') || 1;
    const lastView = localStorage.getItem('lastView') || 'gallery';
    loadPage(lastPage, lastView);

    audio.volume = 0.3;
    $('#vol-range').on('input', function() { audio.volume = this.value; });

    loadBgm();

    $(document).one('click', function() {
        if(!isStarted) { playBgm(); isStarted = true; }
    });

    // --- 스크롤 감지 로직 추가 ---
    $(window).on('scroll', function() {
        // 스크롤이 시작되면 클래스 추가
        $('#main-content').addClass('is-scrolling');

        // 이전 타이머 클리어
        clearTimeout(scrollTimer);

        // 200ms 동안 스크롤이 없으면 멈춘 것으로 간주하고 클래스 제거
        scrollTimer = setTimeout(function() {
            $('#main-content').removeClass('is-scrolling');
        }, 200);
    });
});

function loadPage(page, view) {
    localStorage.setItem('lastPage', page);
    localStorage.setItem('lastView', view);
    
    $.get('content.php', { page: page, view: view }, function(html) {
        $('#ajax-content').html(html);
        window.scrollTo(0, 0);
    });
}

function toggleTheme() {
    const isDark = $('#theme-checkbox').is(':checked');
    const theme = isDark ? 'dark-mode' : 'light-mode';
    $('body').attr('class', theme);
    localStorage.setItem('theme', theme);
}

function loadBgm() {
    $.getJSON('api.php?action=get_bgm', function(data) {
        if(data && data.length > 0) {
            playlist = data.sort(() => Math.random() - 0.5);
            renderNext();
        }
    });
}

function playBgm() {
    if(playlist.length === 0) return;
    audio.src = 'bgm/' + playlist[cur];
    audio.play().then(() => {
        $('#now-title').text("♬ " + playlist[cur]);
        cur = (cur + 1) % playlist.length;
        renderNext();
    }).catch(() => {});
}

function stopBgm() { audio.pause(); $('#now-title').text("BGM 중지됨"); }

function renderNext() {
    let h = "";
    for(let i=0; i<5; i++) {
        let idx = (cur + i) % playlist.length;
        if(playlist[idx]) h += `<li>${playlist[idx]}</li>`;
    }
    $('#next-list').html(h);
}

audio.onended = function() { playBgm(); };

function login() {
    const pwVal = $('#adminPw').val();
    $.post('api.php?action=login', {pw: pwVal}, function(res) {
        if(res.trim() === 'ok') location.reload();
        else alert('비밀번호가 올바르지 않습니다.');
    });
}

function logout() { $.post('api.php?action=logout', () => location.reload()); }

function openModal(src) { 
    $('#modal-video').hide().attr('src', '');
    $('#modal-img').attr('src', src).show(); 
    $('#modal').css('display', 'flex').hide().fadeIn(200); 
    $('body').css('overflow', 'hidden');
}

function openVideoModal(src) {
    stopBgm();
    $('#modal-img').hide();
    $('#modal-video').attr('src', src).show();
    $('#modal').css('display', 'flex').hide().fadeIn(200);
    $('body').css('overflow', 'hidden');
    $('#modal-video')[0].play();
}

function closeModal() {
    const videoElement = $('#modal-video')[0];
    if (videoElement) {
        videoElement.pause();
        videoElement.src = "";
        videoElement.load();
    }

    $('#modal').fadeOut(200, function() {
        $('body').css('overflow', 'auto');
        playBgm(); 
    });
}

function checkFiles(input) {
    $('#file-name-display').text(input.files.length + "개 선택됨");
    $('#up-btn').prop('disabled', input.files.length === 0);
}

function upload() {
    let fd = new FormData();
    let files = $('#upFiles')[0].files;
    for(let i=0; i<files.length; i++) {
        fd.append('files[]', files[i]);
    }
    
    $('#up-btn').prop('disabled', true).text('업로드 중...');
    
    $.ajax({
        url: 'api.php?action=upload', 
        data: fd, 
        type: 'POST', 
        processData: false, 
        contentType: false,
        success: function(res) {
            if(res.trim() === 'ok') {
                alert('업로드 완료!');
                loadPage(1, 'upload');
            } else {
                alert('업로드 실패');
                $('#up-btn').prop('disabled', false).text('서버로 업로드 시작');
            }
        }
    });
}

function confirmMove() {
    let checked = $('.temp-select:checked');
    if(checked.length === 0) return alert('이동할 파일을 선택하세요.');
    let files = [];
    checked.each(function() { files.push($(this).val()); });
    $.post('api.php?action=move_to_gallery', { files: files }, function(res) {
        if(res.trim() === 'ok') loadPage(1, 'upload');
    });
}

function confirmDelete() {
    let checked = $('.temp-select:checked');
    if(checked.length === 0) return alert('삭제할 파일을 선택하세요.');
    if(!confirm('정말 삭제하시겠습니까?')) return;
    let files = [];
    checked.each(function() { files.push($(this).val()); });
    $.post('api.php?action=delete_temp', { files: files }, function(res) {
        if(res.trim() === 'ok') loadPage(1, 'upload');
    });
}

function showMsgModal(text) {
    $('#msg-text').text(text);
    $('#msg-modal').addClass('show').css('display', 'flex');
    setTimeout(function() {
        $('#msg-modal').removeClass('show');
        setTimeout(() => $('#msg-modal').css('display', 'none'), 500); 
    }, 5000);
}