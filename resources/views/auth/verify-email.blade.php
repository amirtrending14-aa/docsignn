<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DocSign — Верификация</title>

    <!-- ✅ Значок сайта (Favicon) DocSign -->
    <link rel="icon" type="image/png" href="{{ asset('img/dss.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/dss.png') }}">

    <link href="https://fonts.bunny.net/css?family=figtree:500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--p:#4f46e5;--a:#06b6d4;--bg:#0f172a;--card:rgba(30,41,59,.8);--txt:#f1f5f9;--muted:#94a3b8;--brd:rgba(148,163,184,.15);--ok:#10b981}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Figtree',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--bg);color:var(--txt);padding:20px}
        .lang{position:fixed;top:16px;right:16px;display:flex;gap:4px;background:rgba(15,23,42,.9);border:1px solid var(--brd);border-radius:10px;padding:4px;z-index:10}
        .lang button{padding:6px 12px;border:none;background:transparent;color:var(--muted);font:600 12px Figtree,sans-serif;border-radius:8px;cursor:pointer;transition:.2s}
        .lang button.active,.lang button:hover{background:var(--p);color:#fff}
        .bg{position:fixed;inset:0;z-index:0;overflow:hidden}
        .bg::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(ellipse at 20% 50%,rgba(79,70,229,.15) 0%,transparent 50%),radial-gradient(ellipse at 80% 20%,rgba(6,182,212,.1) 0%,transparent 50%);animation:mv 15s infinite alternate}
        @keyframes mv{0%{transform:translate(0) rotate(0)}100%{transform:translate(-5%,-5%) rotate(3deg)}}
        .pts{position:fixed;inset:0;z-index:0;pointer-events:none}
        .pt{position:absolute;width:3px;height:3px;background:rgba(129,140,248,.5);border-radius:50%;animation:flt linear infinite}
        @keyframes flt{0%{transform:translateY(100vh) scale(0);opacity:0}10%{opacity:1}90%{opacity:1}100%{transform:translateY(-10vh) scale(1);opacity:0}}
        .card{width:100%;max-width:440px;background:var(--card);backdrop-filter:blur(20px);border:1px solid var(--brd);border-radius:20px;padding:36px 32px;box-shadow:0 20px 60px rgba(0,0,0,.4);position:relative;z-index:1}
        .card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--ok),var(--p),var(--a));border-radius:20px 20px 0 0}
        .logo{text-align:center;margin-bottom:24px}
        .logo img{width:64px;height:64px;border-radius:16px;margin:0 auto 12px;display:block;box-shadow:0 8px 24px rgba(16,185,129,.2)}
        .logo h1{font:800 24px Figtree,sans-serif;letter-spacing:-.5px}
        .logo h1 span{background:linear-gradient(135deg,var(--ok),var(--a));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .logo p{font:500 11px Figtree,sans-serif;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-top:4px}
        .msg{background:rgba(6,182,212,.08);border:1px solid rgba(6,182,212,.2);border-radius:12px;padding:14px;font:500 13px Figtree,sans-serif;color:var(--muted);line-height:1.5;margin-bottom:16px}
        .status{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);border-radius:12px;padding:12px;font:500 13px Figtree,sans-serif;color:var(--ok);text-align:center;margin-bottom:16px}
        .actions{display:flex;flex-direction:column;gap:12px}
        .btn{width:100%;padding:14px;background:linear-gradient(135deg,var(--ok),var(--p));border:none;border-radius:12px;color:#fff;font:700 15px Figtree,sans-serif;cursor:pointer;transition:.2s;display:flex;align-items:center;justify-content:center;gap:8px}
        .btn:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(16,185,129,.25)}
        .link{width:100%;padding:12px;background:transparent;border:1px solid var(--brd);border-radius:12px;color:var(--muted);font:600 14px Figtree,sans-serif;cursor:pointer;transition:.2s}
        .link:hover{border-color:var(--p);color:var(--txt)}
        .badges{display:flex;justify-content:center;gap:20px;margin-top:24px;font:600 11px Figtree,sans-serif;color:var(--muted);opacity:.8}
        .badges svg{width:14px;height:14px;color:var(--ok)}
        .copy{text-align:center;margin-top:16px;font:500 12px Figtree,sans-serif;color:rgba(148,163,184,.5)}

        /* ===== RESPONSIVE ===== */

        /* Планшеты и маленькие ноутбуки (до 768px) */
        @media (max-width: 768px) {
            body { padding: 16px; }
            .lang { top: 12px; right: 12px; padding: 3px; gap: 3px; }
            .lang button { padding: 5px 10px; font-size: 11px; }
            .card { padding: 32px 28px; border-radius: 18px; }
            .logo { margin-bottom: 20px; }
            .logo img { width: 56px; height: 56px; border-radius: 14px; margin-bottom: 10px; }
            .logo h1 { font-size: 22px; }
            .logo p { font-size: 10px; letter-spacing: 1.8px; }
            .msg { padding: 13px; font-size: 12px; border-radius: 10px; }
            .status { padding: 11px; font-size: 12px; border-radius: 10px; }
            .actions { gap: 10px; }
            .btn { padding: 13px; font-size: 14px; border-radius: 10px; gap: 7px; }
            .link { padding: 11px; font-size: 13px; border-radius: 10px; }
            .badges { gap: 16px; margin-top: 20px; flex-wrap: wrap; }
            .badges svg { width: 12px; height: 12px; }
            .copy { margin-top: 14px; font-size: 11px; }
        }

        /* Большие телефоны (до 576px) */
        @media (max-width: 576px) {
            body { padding: 12px; }
            .lang { top: 10px; right: 10px; padding: 3px; gap: 2px; border-radius: 8px; }
            .lang button { padding: 4px 8px; font-size: 10px; border-radius: 6px; }
            .card { padding: 28px 20px; border-radius: 16px; }
            .logo { margin-bottom: 18px; }
            .logo img { width: 52px; height: 52px; border-radius: 12px; margin-bottom: 9px; }
            .logo h1 { font-size: 20px; }
            .logo p { font-size: 10px; letter-spacing: 1.6px; }
            .msg { padding: 12px; font-size: 12px; border-radius: 9px; margin-bottom: 14px; }
            .status { padding: 10px; font-size: 12px; border-radius: 9px; margin-bottom: 14px; }
            .actions { gap: 9px; }
            .btn { padding: 12px; font-size: 13px; border-radius: 9px; gap: 6px; }
            .btn svg { width: 16px; height: 16px; }
            .link { padding: 10px; font-size: 13px; border-radius: 9px; }
            .badges { gap: 14px; margin-top: 18px; font-size: 10px; }
            .badges svg { width: 11px; height: 11px; }
            .copy { margin-top: 12px; font-size: 11px; }
        }

        /* Телефоны (до 480px) */
        @media (max-width: 480px) {
            body { padding: 10px; }
            .lang { top: 8px; right: 8px; padding: 2px; gap: 2px; }
            .lang button { padding: 4px 7px; font-size: 9px; }
            .card { padding: 24px 18px; border-radius: 14px; }
            .logo { margin-bottom: 16px; }
            .logo img { width: 48px; height: 48px; border-radius: 10px; margin-bottom: 8px; }
            .logo h1 { font-size: 19px; }
            .logo p { font-size: 9px; letter-spacing: 1.4px; margin-top: 3px; }
            .msg { padding: 11px; font-size: 11px; border-radius: 8px; margin-bottom: 12px; line-height: 1.45; }
            .status { padding: 9px; font-size: 11px; border-radius: 8px; margin-bottom: 12px; }
            .actions { gap: 8px; }
            .btn { padding: 11px; font-size: 12px; border-radius: 8px; gap: 5px; }
            .btn svg { width: 15px; height: 15px; }
            .link { padding: 9px; font-size: 12px; border-radius: 8px; }
            .badges { gap: 12px; margin-top: 16px; font-size: 9px; }
            .badges svg { width: 10px; height: 10px; }
            .copy { margin-top: 10px; font-size: 10px; }
        }

        /* Очень маленькие телефоны (до 380px) */
        @media (max-width: 380px) {
            body { padding: 8px; }
            .lang { top: 6px; right: 6px; padding: 2px; gap: 1px; }
            .lang button { padding: 3px 6px; font-size: 8px; }
            .card { padding: 20px 14px; border-radius: 12px; }
            .logo { margin-bottom: 14px; }
            .logo img { width: 44px; height: 44px; border-radius: 9px; margin-bottom: 7px; }
            .logo h1 { font-size: 18px; }
            .logo p { font-size: 8px; letter-spacing: 1.2px; }
            .msg { padding: 10px; font-size: 10px; border-radius: 7px; margin-bottom: 10px; line-height: 1.4; }
            .status { padding: 8px; font-size: 10px; border-radius: 7px; margin-bottom: 10px; }
            .actions { gap: 7px; }
            .btn { padding: 10px; font-size: 11px; border-radius: 7px; gap: 4px; }
            .btn svg { width: 14px; height: 14px; }
            .link { padding: 8px; font-size: 11px; border-radius: 7px; }
            .badges { gap: 10px; margin-top: 14px; font-size: 8px; flex-wrap: wrap; }
            .badges svg { width: 9px; height: 9px; }
            .copy { margin-top: 8px; font-size: 9px; }
        }
    </style>
</head>
<body>
<div class="bg"></div><div class="pts" id="pts"></div>
<div class="lang">
    <button class="active" data-lang="ru" onclick="setLang('ru')">🇺 РУ</button>
    <button data-lang="tj" onclick="setLang('tj')">🇹🇯 TJ</button>
    <button data-lang="en" onclick="setLang('en')">🇬🇧 EN</button>
</div>

<div class="card">
    <div class="logo">
        <img src="{{ asset('img/dss.png') }}" alt="DocSign">
        <h1>Doc<span>Sign</span></h1>
        <p data-i18n="sub">Верификация</p>
    </div>

    <div class="msg" data-i18n="msg">Спасибо за регистрацию! Проверьте почту и перейдите по ссылке для подтверждения аккаунта. Если письмо не пришло, мы отправим его повторно.</div>

    @if(session('status') == 'verification-link-sent')
    <div class="status" data-i18n="sent">✅ Ссылка отправлена повторно на вашу почту.</div>
    @endif

    <div class="actions">
        <form method="POST" action="{{ route('verification.send') }}">@csrf
            <button class="btn" data-i18n="resend">Отправить ссылку повторно</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="link" data-i18n="logout">Выйти</button>
        </form>
    </div>
</div>

<div class="badges">
    <div><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg> SSL</div>
    <div><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2z"/></svg> <span data-i18n="sec">Защита</span></div>
    <div><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg> <span data-i18n="sig">ЭЦП</span></div>
</div>
<p class="copy">© {{ date('Y') }} DocSign. <span data-i18n="copy">Все права защищены.</span></p>

<script>
    const t={ru:{sub:'Верификация',msg:'Спасибо за регистрацию! Проверьте почту и перейдите по ссылке для подтверждения аккаунта. Если письмо не пришло, мы отправим его повторно.',sent:'✅ Ссылка отправлена повторно.',resend:'Отправить ссылку повторно',logout:'Выйти',sec:'Защита',sig:'ЭЦП',copy:'Все права защищены.'},tj:{sub:'Тасдиқ',msg:'Ташаккур барои бақайдгирӣ! Почтаро санҷед ва барои тасдиқ ба пайванд гузаред. Агар нарасида бошад, мо дубора мефиристем.',sent:'✅ Пайванд дубора фиристода шуд.',resend:'Фиристодани пайванд',logout:'Баромадан',sec:'Ҳифз',sig:'ЭИИ',copy:'Ҳуқуқҳо ҳифз шудаанд.'},en:{sub:'Verification',msg:'Thanks for signing up! Check your email and click the link to verify your account. If you didn\'t receive it, we\'ll gladly send another.',sent:'✅ Link resent successfully.',resend:'Resend Verification Email',logout:'Log Out',sec:'Security',sig:'EDS',copy:'All rights reserved.'}};
    let lang='ru';
    function setLang(l){lang=l;document.querySelectorAll('.lang button').forEach(b=>b.classList.toggle('active',b.dataset.lang===l));document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.dataset.i18n;if(t[lang][k])el.textContent=t[lang][k]})}
    (function(){const c=document.getElementById('pts');for(let i=0;i<20;i++){const d=document.createElement('div');d.className='pt';d.style.left=Math.random()*100+'%';d.style.animationDuration=(Math.random()*8+5)+'s';d.style.animationDelay=(Math.random()*5)+'s';d.style.width=d.style.height=(Math.random()*3+1)+'px';c.appendChild(d)}})();
</script>
</body>
</html>