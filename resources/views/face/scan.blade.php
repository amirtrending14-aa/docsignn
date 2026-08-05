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
        margin: 0 auto 2rem;
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
        font-size: 1.2rem;
        font-weight: 600;
        color: white;
        text-align: center;
        margin-bottom: 1rem;
        min-height: 1.5em;
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

    .alert-modern {
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1rem;
        border: none;
        font-weight: 500;
    }

    .debug-info {
        background: rgba(0,0,0,0.5);
        color: #00ff88;
        padding: 10px;
        border-radius: 8px;
        font-family: monospace;
        font-size: 11px;
        margin-top: 10px;
        max-height: 100px;
        overflow-y: auto;
    }

    .face-detected-indicator {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,255,136,0.9);
        color: #000;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 10;
    }

    .face-detected-indicator.visible {
        opacity: 1;
    }
</style>

<div class="face-scan-container">
    <div class="scan-card">
        <h2 class="text-center text-white mb-4">Breez Face ID</h2>

        <p id="status-text" class="status-text">Инициализация...</p>

        <div class="video-wrapper" id="video-wrapper">
            <video id="video" autoplay muted playsinline></video>
            <canvas id="overlay"></canvas>
            <div class="face-mask"></div>
            <div class="scan-line"></div>
            <div class="face-detected-indicator" id="face-indicator">
                ✓ Лицо обнаружено
            </div>
        </div>

        <div class="progress-bar">
            <div class="progress-fill" id="progress"></div>
        </div>

        <button id="btn-scan" class="btn-scan" disabled>
            Сканировать
        </button>

        <div id="result"></div>

        <div class="debug-info" id="debug"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    function debug(msg) {
        console.log('[FaceID]', msg);
        const el = document.getElementById('debug');
        if (el) {
            el.innerHTML += new Date().toLocaleTimeString() + ' — ' + msg + '<br>';
            el.scrollTop = el.scrollHeight;
        }
    }

    let MODE = @json($mode);
    let isScanning = false;
    let detectionLoop = null;
    let faceStableCount = 0;

    const video = document.getElementById('video');
    const overlay = document.getElementById('overlay');
    const overlayCtx = overlay.getContext('2d');
    const btn = document.getElementById('btn-scan');
    const status = document.getElementById('status-text');
    const result = document.getElementById('result');
    const progress = document.getElementById('progress');
    const wrapper = document.getElementById('video-wrapper');
    const faceIndicator = document.getElementById('face-indicator');

    debug('Режим: ' + MODE);

    // ===== ЗАГРУЗКА МОДЕЛЕЙ =====
    async function loadModels() {
        debug('Загрузка моделей...');
        status.innerText = 'Загрузка AI...';
        progress.style.width = '30%';
        
        await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
        debug('✓ tinyFaceDetector');
        progress.style.width = '60%';
        
        await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
        debug('✓ faceLandmark68Net');
        progress.style.width = '80%';
        
        await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
        debug('✓ faceRecognitionNet');
        progress.style.width = '100%';
    }

    // ===== КАМЕРА =====
    async function startCamera() {
        debug('Включение камеры...');
        status.innerText = 'Включение камеры...';
        
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: 640, height: 480 }
        });
        
        video.srcObject = stream;
        await new Promise(r => video.onloadeddata = r);
        
        overlay.width = video.videoWidth;
        overlay.height = video.videoHeight;
        
        debug('✓ Камера готова');
    }

    // ===== РИСОВАНИЕ (с защитой от null) =====
    function drawDetection(detection) {
        overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
        
        // ✅ КРИТИЧЕСКИ ВАЖНО: проверяем что detection существует
        if (!detection || !detection.detection || !detection.detection.box) {
            return;
        }

        const box = detection.detection.box;
        const scaleX = overlay.width / video.videoWidth;
        const scaleY = overlay.height / video.videoHeight;

        overlayCtx.strokeStyle = '#00ff88';
        overlayCtx.lineWidth = 3;
        overlayCtx.strokeRect(
            box.x * scaleX,
            box.y * scaleY,
            box.width * scaleX,
            box.height * scaleY
        );
    }

    // ===== НЕПРЕРЫВНЫЙ ПОИСК (исправлен) =====
    async function detectionLoop_fn() {
        if (isScanning) return;

        try {
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                    inputSize: 320,
                    scoreThreshold: 0.4
                }));

            if (detection) {
                faceIndicator.classList.add('visible');
                drawDetection(detection);
                
                faceStableCount++;
                if (faceStableCount >= 2 && MODE === 'checkin') {
                    debug('✓ Лицо стабильно, авто-скан');
                    performFullScan();
                    return;
                }
            } else {
                faceIndicator.classList.remove('visible');
                // ✅ Чистим канвас когда лица нет
                overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
                faceStableCount = 0;
            }
        } catch (e) {
            debug('Ошибка поиска: ' + e.message);
        }

        detectionLoop = setTimeout(detectionLoop_fn, 400);
    }

    // ===== ИНИЦИАЛИЗАЦИЯ =====
    async function init() {
        try {
            await loadModels();
            await startCamera();

            btn.disabled = false;
            status.innerText = MODE === 'register' 
                ? 'Смотрите в камеру' 
                : 'Смотрите в камеру — автоскан';

            debug('✓ Готово');
            detectionLoop_fn();
        } catch (e) {
            debug('ОШИБКА: ' + e.message);
            status.innerText = 'Ошибка: ' + e.message;
            result.innerHTML = '<div class="alert alert-danger alert-modern">' + e.message + '</div>';
        }
    }

    // ===== ⚡ БЫСТРЫЙ РЕДИРЕКТ (используем replace чтобы не возвращаться) =====
    function forceRedirect(url) {
        debug('Переход: ' + url);
        
        // Останавливаем всё
        if (detectionLoop) clearTimeout(detectionLoop);
        detectionLoop = null;
        isScanning = true;
        
        // Отключаем камеру
        if (video.srcObject) {
            video.srcObject.getTracks().forEach(t => t.stop());
        }
        
        // КРИТИЧНО: replace() вместо href — не оставляет скан в истории
        setTimeout(() => {
            window.location.replace(url);
        }, 800);
    }

    // ===== ПОЛНОЕ СКАНИРОВАНИЕ (ОПТИМИЗИРОВАНО — 1 запрос вместо 2) =====
    async function performFullScan() {
        if (isScanning) return;
        isScanning = true;

        if (detectionLoop) {
            clearTimeout(detectionLoop);
            detectionLoop = null;
        }

        btn.disabled = true;
        wrapper.classList.add('scanning');
        status.innerText = 'Сканирую...';
        progress.style.width = '20%';

        try {
            // ✅ ОДИН запрос вместо двух — сразу с landmarks + descriptor
            debug('Полное сканирование (быстрый режим)...');
            
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                    inputSize: 320,
                    scoreThreshold: 0.4
                }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            progress.style.width = '60%';

            if (!detection) {
                debug('✗ Лицо потеряно');
                isScanning = false;
                btn.disabled = false;
                wrapper.classList.remove('scanning');
                detectionLoop_fn();
                return;
            }

            debug('✓ Отправка на сервер');
            status.innerText = 'Проверка...';
            progress.style.width = '90%';

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
                body: JSON.stringify({ vector: Array.from(detection.descriptor) })
            });

            progress.style.width = '100%';
            const data = await resp.json();
            debug('Ответ: ' + JSON.stringify(data));

            wrapper.classList.remove('scanning');

            // ✅ УСПЕХ
            if (resp.ok) {
                const redirect = data.redirect || '{{ route('dashboard') }}';
                const alertClass = data.status === 'late' ? 'warning' : 'success';
                const lateInfo = data.late_minutes > 0 ? ` (${data.late_minutes} мин)` : '';
                
                const message = data.status === 'late'
                    ? `Опоздание${lateInfo}! Время: ${data.time}. Штраф: -${data.fine} сом`
                    : `Вовремя! Время: ${data.time}`;

                result.innerHTML = `<div class="alert alert-${alertClass} alert-modern">${message}</div>`;
                status.innerText = '✓ Готово! Переход...';
                debug('✓ Успех');

                forceRedirect(redirect);
                return;
            }

            // ✅ УЖЕ ОТМЕЧЕН — тоже редирект
            if (data.error && data.error.includes('уже отметились')) {
                debug('✓ Уже отмечен');
                result.innerHTML = '<div class="alert alert-info alert-modern">Уже отмечен. Переход...</div>';
                status.innerText = 'Переход на главную...';
                
                forceRedirect('{{ route('dashboard') }}');
                return;
            }

            // ✗ Ошибка
            debug('✗ Ошибка: ' + data.error);
            const resetUrl = '{{ route('face.scan') }}?reset=1';
            
            result.innerHTML = `
                <div class="alert alert-danger alert-modern">
                    ${data.error || 'Ошибка'}
                    <br><br>
                    <button onclick="window.location.href='${resetUrl}'" class="btn btn-sm btn-warning mt-2">
                        🔄 Перерегистрировать
                    </button>
                </div>`;
            
            status.innerText = 'Попробуйте ещё раз';
            isScanning = false;
            btn.disabled = false;
            detectionLoop_fn();

        } catch (e) {
            debug('✗ JS: ' + e.message);
            wrapper.classList.remove('scanning');
            result.innerHTML = `<div class="alert alert-danger alert-modern">Ошибка: ${e.message}</div>`;
            isScanning = false;
            btn.disabled = false;
            detectionLoop_fn();
        }
    }

    btn.addEventListener('click', () => {
        debug('Кнопка нажата');
        performFullScan();
    });

    debug('Запуск...');
    init();
</script>
@endsection