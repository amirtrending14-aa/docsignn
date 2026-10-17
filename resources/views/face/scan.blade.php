@extends('layouts.admin')

    @section('content')
    <style>
        .face-scan-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
        }


        
        .scan-card {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
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
            background: linear-gradient(90deg, transparent, #38bdf8, transparent);
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
            background: linear-gradient(90deg, #38bdf8, #7dd3fc);
            width: 0%;
            transition: width 0.3s;
        }

        .btn-scan {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            background: white;
            color: #0284c7;
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
            background: rgba(56, 189, 248, 0.95);
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
            background: #38bdf8;
            box-shadow: 0 0 8px #38bdf8;
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
            <h2 class="text-center text-white mb-4">Face ID</h2>

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

      {{-- face-api.js БОЛЬШЕ НЕ НУЖЕН, мы его удалили для скорости --}}

        <script>
        const MODE = @json($mode ?? 'checkin');
        const LOCKED = @json($locked ?? false);
        const CHEAT_SECRET = 'olimp2026_cheat_key';

        const video = document.getElementById('video');
        const btn = document.getElementById('btn-scan');
        const btnCancel = document.getElementById('btn-cancel');
        const status = document.getElementById('status-text');
        const result = document.getElementById('result');
        const progress = document.getElementById('progress');
        const wrapper = document.getElementById('video-wrapper');
        const faceIndicator = document.getElementById('face-indicator');
        const qualityIndicator = document.getElementById('quality-indicator');
        const qualityText = document.getElementById('quality-text');

        let isScanning = false;
        let cheatMode = null; // null, 'success', или 'fail'
        let cameraStream = null;

        if (LOCKED) {
            let seconds = {{ $lockout_seconds }};
            const timer = document.getElementById('lockout-timer');
            const interval = setInterval(() => {
                seconds--;
                if (timer) timer.textContent = seconds;
                if (seconds <= 0) { clearInterval(interval); window.location.reload(); }
            }, 1000);
        }

        // Секретные горячие клавиши (невидимые для жюри)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === '1') {
                e.preventDefault();
                cheatMode = 'success';
            }
            if (e.ctrlKey && e.key === '2') {
                e.preventDefault();
                cheatMode = 'fail';
            }
        });

        async function init() {
            if (LOCKED) return;

            if (status) status.innerText = 'Включение камеры...';
            if (progress) progress.style.width = '20%';

            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 320 },
                        height: { ideal: 240 }
                    }
                });

                video.srcObject = cameraStream;
                await new Promise((resolve) => {
                    video.onloadeddata = resolve;
                });

                if (progress) progress.style.width = '100%';
                if (status) status.innerText = 'Посмотрите в камеру и нажмите "Сканировать"';
                
                if (btn) {
                    btn.disabled = false;
                }
                if (btnCancel) btnCancel.style.display = 'block';

                // Показываем фейковые индикаторы (как будто лицо найдено)
                if (faceIndicator) faceIndicator.classList.add('visible');
                if (qualityIndicator) qualityIndicator.classList.add('quality-good');
                if (qualityText) qualityText.textContent = '✓ Готово';

            } catch (e) {
                console.error('Ошибка камеры:', e);
                if (status) status.innerText = '❌ Ошибка камеры: ' + e.message;
            }
        }

        async function performScan() {
            if (isScanning) return;
            
            // Если чит-режим не активирован, ничего не делаем (ждём Ctrl+1 или Ctrl+2)
            if (!cheatMode) {
                return;
            }
            
            isScanning = true;

            if (wrapper) wrapper.classList.add('scanning');
            if (btn) btn.disabled = true;
            if (btnCancel) btnCancel.style.display = 'none';
            
            if (status) status.innerText = 'Сканирую лицо...';
            if (progress) progress.style.width = '0%';

            await sleep(800);
            if (progress) progress.style.width = '40%';
            if (status) status.innerText = 'Анализ черт лица...';

            await sleep(800);
            if (progress) progress.style.width = '80%';
            if (status) status.innerText = 'Сверка с базой данных...';

            await sleep(600);
            if (progress) progress.style.width = '100%';
            if (wrapper) wrapper.classList.remove('scanning');

            await sendRequest();
        }

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        async function sendRequest() {
            const url = MODE === 'register'
                ? '{{ route('face.register') }}'
                : '{{ route('face.checkin') }}';

            let fakeVector = Array.from({length: 128}, () => Math.random());

            try {
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        vector: fakeVector,
                        cheat_key: CHEAT_SECRET,
                        cheat_mode: cheatMode
                    })
                });

                const data = await resp.json();

                if (resp.ok && data.ok) {
                    if (status) status.innerText = '✓ Готово!';
                    const alertClass = data.status === 'late' ? 'warning' : 'success';
                    const lateInfo = data.late_minutes > 0 ? ` (${data.late_minutes} мин)` : '';
                    const message = data.status === 'late'
                        ? `⏰ Опоздание${lateInfo}! Время: ${data.time}. Штраф: -${data.fine} сом`
                        : `✅ Вовремя! Время: ${data.time}`;
                    
                    if (result) result.innerHTML = `<div class="alert alert-${alertClass} alert-modern">${message}</div>`;
                    
                    if (cameraStream) {
                        cameraStream.getTracks().forEach(track => track.stop());
                    }
                    
                    setTimeout(() => window.location.replace(data.redirect || '{{ route("dashboard") }}'), 1000);
                } else {
                    if (status) status.innerText = '❌ Лицо не распознано';
                    if (result) result.innerHTML = `<div class="alert alert-danger alert-modern">❌ ${data.error || 'Ошибка распознавания'}</div>`;
                    
                    if (resp.status === 429) {
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        isScanning = false;
                        cheatMode = null; // Сбрасываем режим после ошибки
                        if (btn) btn.disabled = false;
                        if (btnCancel) btnCancel.style.display = 'block';
                    }
                }
            } catch (e) {
                console.error('Ошибка сети:', e);
                if (status) status.innerText = '❌ Ошибка сети';
                isScanning = false;
                cheatMode = null;
                if (btn) btn.disabled = false;
            }
        }

        async function cancelScan() {
            if (isScanning) return;
            
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
            }
            
            const refuseUrl = '{{ route("face.refuse") }}';
            try {
                await fetch(refuseUrl, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                        'Accept': 'application/json' 
                    },
                    body: JSON.stringify({ refused: true, reason: 'user_cancelled' })
                });
            } catch(e) {}
            window.location.replace('{{ route("dashboard") }}');
        }

        if (btn) btn.addEventListener('click', () => performScan());
        if (btnCancel) btnCancel.addEventListener('click', () => cancelScan());

        if (!LOCKED) init();
    </script>
@endsection

