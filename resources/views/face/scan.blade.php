@extends('layouts.admin')

@section('content')
<style>
    .face-scan-container {
        max-width: 500px;
        margin: 2rem auto;
        padding: 2rem;
    }

    .scan-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .video-wrapper {
        position: relative;
        width: 400px;
        height: 400px;
        margin: 0 auto 1.5rem;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0 0 0 4px rgba(255,255,255,0.2), 0 10px 40px rgba(0,0,0,0.4);
        background: #000;
    }

    #video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1);
        display: block;
    }

    #overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: scaleX(-1);
    }

    .face-mask {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        background: radial-gradient(
            ellipse at center,
            transparent 40%,
            rgba(0,0,0,0.7) 70%
        );
    }

    .scan-line {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, transparent, #00ff88, transparent);
        animation: scanLine 1.5s linear infinite;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .scanning .scan-line {
        opacity: 1;
    }

    @keyframes scanLine {
        0% { top: 0%; }
        100% { top: 100%; }
    }

    .status-text {
        font-size: 1.1rem;
        font-weight: 600;
        color: white;
        text-align: center;
        margin-bottom: 1rem;
        min-height: 1.5em;
    }

    .simple-instructions {
        background: rgba(0,0,0,0.4);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 1rem;
        color: white;
        font-size: 0.95rem;
        text-align: center;
    }

    .progress-bar {
        height: 6px;
        background: rgba(255,255,255,0.2);
        border-radius: 3px;
        overflow: hidden;
        margin: 1rem 0;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #00ff88, #00d4ff);
        width: 0%;
        transition: width 0.3s;
    }

    .btn-scan {
        width: 100%;
        padding: 1rem;
        font-size: 1.1rem;
        font-weight: 600;
        background: white;
        color: #667eea;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .btn-scan:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }

    .btn-scan:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-cancel {
        width: 100%;
        padding: 1rem;
        font-size: 1.1rem;
        font-weight: 600;
        background: rgba(255, 68, 68, 0.2);
        color: #ff8a8a;
        border: 2px solid rgba(255, 68, 68, 0.5);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        margin-top: 1rem;
    }

    .btn-cancel:hover:not(:disabled) {
        background: rgba(255, 68, 68, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }

    .btn-cancel:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .alert-modern {
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1rem;
        border: none;
        font-weight: 500;
    }

    .face-detected-indicator {
        position: absolute;
        bottom: 50px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,255,136,0.9);
        color: #000;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 10;
    }

    .face-detected-indicator.visible {
        opacity: 1;
    }

    .quality-indicator {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0,0,0,0.7);
        color: #fff;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .quality-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ff4444;
        transition: background 0.3s;
    }

    .quality-good .quality-dot {
        background: #00ff88;
    }

    .lockout-card {
        background: rgba(255,68,68,0.15);
        border: 1px solid rgba(255,68,68,0.5);
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
        color: white;
    }

    .lockout-icon {
        font-size: 64px;
        margin-bottom: 1rem;
        color: #ff4444;
    }

    .lockout-timer {
        font-family: 'JetBrains Mono', monospace;
        font-size: 48px;
        font-weight: 800;
        color: #ff4444;
        margin: 1rem 0;
    }
</style>

<div class="face-scan-container">
    <div class="scan-card">
        <h2 class="text-center text-white mb-4">🛡️ Breez Face ID</h2>

        @if($locked)
            <div class="lockout-card">
                <div class="lockout-icon">🔒</div>
                <h3 class="mb-3">Временно заблокировано</h3>
                <p class="mb-3">Слишком много неудачных попыток</p>
                <div class="lockout-timer" id="lockout-timer">{{ $lockout_seconds }}</div>
                <p class="mb-0">секунд до разблокировки</p>
                <button onclick="window.location.reload()" class="btn btn-light mt-4">
                    🔄 Обновить
                </button>
            </div>
        @else
            <p id="status-text" class="status-text">Инициализация...</p>

            <div class="simple-instructions">
                👀 Посмотрите в камеру и нажмите "Сканировать"
            </div>

            <div class="video-wrapper" id="video-wrapper">
                <video id="video" autoplay muted playsinline></video>
                <canvas id="overlay"></canvas>
                <div class="face-mask"></div>
                <div class="scan-line"></div>
                <div class="face-detected-indicator" id="face-indicator">
                    ✓ Лицо обнаружено
                </div>
                <div class="quality-indicator" id="quality-indicator">
                    <div class="quality-dot"></div>
                    <span id="quality-text">Ожидание</span>
                </div>
            </div>

            <div class="progress-bar">
                <div class="progress-fill" id="progress"></div>
            </div>

            <button id="btn-scan" class="btn-scan" disabled>
                Сканировать
            </button>
            <button id="btn-cancel" class="btn-cancel" style="display: none;">
                ❌ Отказаться от сканирования
            </button>

            <div id="result"></div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
    const CONFIG = {
        INPUT_SIZE: 416,
        SCORE_THRESHOLD: 0.5,
        MODELS_URL: 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights',
    };

    const MODE = @json($mode ?? 'checkin');
    const LOCKED = @json($locked ?? false);
    
    let isScanning = false;
    let isCancelling = false;
    let detectionLoop = null;
    let faceDetected = false;

    const video = document.getElementById('video');
    const overlay = document.getElementById('overlay');
    const overlayCtx = overlay ? overlay.getContext('2d') : null;
    const btn = document.getElementById('btn-scan');
    const btnCancel = document.getElementById('btn-cancel');
    const status = document.getElementById('status-text');
    const result = document.getElementById('result');
    const progress = document.getElementById('progress');
    const wrapper = document.getElementById('video-wrapper');
    const faceIndicator = document.getElementById('face-indicator');
    const qualityIndicator = document.getElementById('quality-indicator');
    const qualityText = document.getElementById('quality-text');

    if (LOCKED) {
        let seconds = {{ $lockout_seconds }};
        const timer = document.getElementById('lockout-timer');
        const interval = setInterval(() => {
            seconds--;
            if (timer) timer.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.reload();
            }
        }, 1000);
    }

    function debug(msg) {
        console.log('[FaceID]', msg);
    }

    async function loadModels() {
        debug('Загрузка моделей...');
        if (status) status.innerText = 'Загрузка AI...';
        if (progress) progress.style.width = '20%';
        
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri(CONFIG.MODELS_URL);
            if (progress) progress.style.width = '40%';
            
            await faceapi.nets.faceLandmark68Net.loadFromUri(CONFIG.MODELS_URL);
            if (progress) progress.style.width = '70%';
            
            await faceapi.nets.faceRecognitionNet.loadFromUri(CONFIG.MODELS_URL);
            if (progress) progress.style.width = '100%';
            
            debug('✓ Все модели загружены');
        } catch (e) {
            debug('✗ Ошибка: ' + e.message);
            throw new Error('Не удалось загрузить AI модели');
        }
    }

    async function startCamera() {
        debug('Включение камеры...');
        if (status) status.innerText = 'Включение камеры...';
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { 
                    facingMode: 'user', 
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            });
            
            video.srcObject = stream;
            await new Promise((resolve, reject) => {
                video.onloadeddata = resolve;
                video.onerror = () => reject(new Error('Ошибка видео'));
            });
            
            overlay.width = video.videoWidth;
            overlay.height = video.videoHeight;
            
            debug('✓ Камера готова');
        } catch (e) {
            debug('✗ Ошибка камеры: ' + e.message);
            throw new Error('Не удалось включить камеру. Проверьте разрешения.');
        }
    }

    function drawDetection(detection) {
        if (!overlayCtx) return;
        overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
        
        if (!detection || !detection.detection || !detection.detection.box) return;

        const box = detection.detection.box;
        const scaleX = overlay.width / video.videoWidth;
        const scaleY = overlay.height / video.videoHeight;

        overlayCtx.strokeStyle = '#00ff88';
        overlayCtx.lineWidth = 3;
        overlayCtx.strokeRect(
            box.x * scaleX, box.y * scaleY,
            box.width * scaleX, box.height * scaleY
        );
    }

    async function detectionLoop_fn() {
        if (isScanning || isCancelling) return;

        try {
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                    inputSize: CONFIG.INPUT_SIZE,
                    scoreThreshold: CONFIG.SCORE_THRESHOLD
                }))
                .withFaceLandmarks();

            if (detection && detection.detection.score >= CONFIG.SCORE_THRESHOLD) {
                faceDetected = true;
                if (faceIndicator) faceIndicator.classList.add('visible');
                if (qualityIndicator) qualityIndicator.classList.add('quality-good');
                if (qualityText) qualityText.textContent = '✓ Готово';
                drawDetection(detection);
                
                if (btn && btn.disabled) {
                    btn.disabled = false;
                }
            } else {
                faceDetected = false;
                if (faceIndicator) faceIndicator.classList.remove('visible');
                if (qualityIndicator) qualityIndicator.classList.remove('quality-good');
                if (qualityText) qualityText.textContent = 'Ищем лицо...';
                if (overlayCtx) overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
                
                if (btn && !btn.disabled) {
                    btn.disabled = true;
                }
            }
        } catch (e) {
            debug('Ошибка: ' + e.message);
        }

        detectionLoop = setTimeout(detectionLoop_fn, 300);
    }

    async function init() {
        if (LOCKED) return;
        
        try {
            await loadModels();
            await startCamera();

            if (btnCancel) btnCancel.style.display = 'block';
            if (status) status.innerText = 'Посмотрите в камеру и нажмите "Сканировать"';
            
            detectionLoop_fn();
        } catch (e) {
            debug('✗ Ошибка: ' + e.message);
            if (status) status.innerText = '❌ ' + e.message;
            if (result) result.innerHTML = '<div class="alert alert-danger alert-modern">' + e.message + '</div>';
        }
    }

    function forceRedirect(url) {
        if (detectionLoop) clearTimeout(detectionLoop);
        detectionLoop = null;
        isScanning = true;
        
        if (video && video.srcObject) {
            video.srcObject.getTracks().forEach(t => t.stop());
        }
        
        setTimeout(() => window.location.replace(url), 800);
    }

    async function cancelScan() {
        if (isCancelling) return;
        
        debug('Отказ от сканирования');
        isCancelling = true;
        
        if (detectionLoop) {
            clearTimeout(detectionLoop);
            detectionLoop = null;
        }
        
        if (btn) btn.disabled = true;
        if (btnCancel) {
            btnCancel.disabled = true;
            btnCancel.innerHTML = '⏳ Отправка отказа...';
        }
        
        if (wrapper) wrapper.classList.remove('scanning');
        if (overlayCtx) overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
        if (faceIndicator) faceIndicator.classList.remove('visible');
        if (qualityIndicator) qualityIndicator.classList.remove('quality-good');
        
        if (status) status.innerText = '⏳ Отказ от сканирования...';
        if (progress) progress.style.width = '50%';
        
        if (video && video.srcObject) {
            video.srcObject.getTracks().forEach(t => t.stop());
        }
        
        const dashboardUrl = '{{ route('dashboard') }}';
        const refuseUrl = '{{ route("face.refuse") }}';
        
        try {
            const resp = await fetch(refuseUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    refused: true,
                    reason: 'user_cancelled'
                })
            });
            
            if (!resp.ok) {
                throw new Error('Server returned ' + resp.status);
            }
            
            const data = await resp.json();
            debug('Ответ отказа: ' + JSON.stringify(data));
            
            if (progress) progress.style.width = '100%';
            
            if (data.ok) {
                if (status) status.innerText = '⚠️ Отказ зарегистрирован. Перенаправление...';
                if (result) result.innerHTML = '<div class="alert alert-warning alert-modern">⚠️ Отказ от сканирования зарегистрирован. Это будет отмечено в отчёте.</div>';
                
                const redirect = data.redirect || dashboardUrl;
                setTimeout(() => window.location.replace(redirect), 1200);
                return;
            }
            
            throw new Error('Server response not ok');
            
        } catch (e) {
            debug('Ошибка отправки отказа: ' + e.message);
            if (status) status.innerText = '⚠️ Перенаправление...';
            if (result) result.innerHTML = '<div class="alert alert-warning alert-modern">⚠️ Перенаправление на дашборд.</div>';
            if (progress) progress.style.width = '100%';
            setTimeout(() => window.location.replace(dashboardUrl), 1200);
        }
    }

    async function performFullScan() {
        if (isScanning) return;
        
        if (!faceDetected) {
            if (status) status.innerText = '⚠️ Лицо не обнаружено. Посмотрите в камеру';
            return;
        }
        
        isScanning = true;
        if (detectionLoop) { clearTimeout(detectionLoop); detectionLoop = null; }
        if (btn) btn.disabled = true;
        if (btnCancel) btnCancel.style.display = 'none';
        if (wrapper) wrapper.classList.add('scanning');
        if (status) status.innerText = 'Сканирую...';
        if (progress) progress.style.width = '30%';

        try {
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                    inputSize: CONFIG.INPUT_SIZE,
                    scoreThreshold: CONFIG.SCORE_THRESHOLD
                }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (progress) progress.style.width = '60%';

            if (!detection) {
                if (status) status.innerText = 'Лицо потеряно. Попробуйте ещё раз';
                isScanning = false;
                if (btn) btn.disabled = false;
                if (btnCancel) btnCancel.style.display = 'block';
                if (wrapper) wrapper.classList.remove('scanning');
                detectionLoop_fn();
                return;
            }

            debug('Отправка на сервер...');
            if (status) status.innerText = 'Проверка...';
            if (progress) progress.style.width = '90%';

            const url = MODE === 'register'
                ? '{{ route('face.register') }}'
                : '{{ route('face.checkin') }}';

            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    vector: Array.from(detection.descriptor)
                })
            });

            if (progress) progress.style.width = '100%';
            const data = await resp.json();
            debug('Ответ: ' + JSON.stringify(data));

            if (wrapper) wrapper.classList.remove('scanning');

            if (resp.ok && data.ok) {
                const redirect = data.redirect || '{{ route('dashboard') }}';
                const alertClass = data.status === 'late' ? 'warning' : 'success';
                const lateInfo = data.late_minutes > 0 ? ` (${data.late_minutes} мин)` : '';
                const message = data.status === 'late'
                    ? `⏰ Опоздание${lateInfo}! Время: ${data.time}. Штраф: -${data.fine} сом`
                    : `✅ Вовремя! Время: ${data.time}`;

                if (result) result.innerHTML = `<div class="alert alert-${alertClass} alert-modern">${message}</div>`;
                if (status) status.innerText = '✓ Готово!';
                forceRedirect(redirect);
                return;
            }

            if (data.status === 'already') {
                if (result) result.innerHTML = '<div class="alert alert-info alert-modern">✓ Вы уже отметились</div>';
                forceRedirect(data.redirect || '{{ route('dashboard') }}');
                return;
            }

            debug('Ошибка: ' + (data.error || 'Неизвестная'));
            if (result) result.innerHTML = `<div class="alert alert-danger alert-modern">❌ ${data.error || 'Ошибка'}</div>`;
            if (status) status.innerText = 'Попробуйте ещё раз';
            isScanning = false;
            if (btn) btn.disabled = false;
            if (btnCancel) btnCancel.style.display = 'block';
            
            if (resp.status === 429) {
                setTimeout(() => window.location.reload(), 1500);
            } else {
                detectionLoop_fn();
            }

        } catch (e) {
            debug('JS ошибка: ' + e.message);
            if (wrapper) wrapper.classList.remove('scanning');
            if (result) result.innerHTML = `<div class="alert alert-danger alert-modern">❌ ${e.message}</div>`;
            isScanning = false;
            if (btn) btn.disabled = false;
            if (btnCancel) btnCancel.style.display = 'block';
            detectionLoop_fn();
        }
    }

    if (btn) btn.addEventListener('click', () => performFullScan());
    if (btnCancel) btnCancel.addEventListener('click', () => cancelScan());

    if (!LOCKED) {
        debug('Запуск системы...');
        init();
    }
</script>
@endsection