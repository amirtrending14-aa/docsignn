<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>DocSign — Электронный документооборот</title>
{{-- ✅ Favicon DocSign --}}
<link rel="icon" type="image/png" href="{{ asset('img/dss.png') }}">
<link rel="apple-touch-icon" href="{{ asset('img/dss.png') }}">
<link rel="shortcut icon" href="{{ asset('img/dss.png') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/inter@5.0.0/index.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/jetbrains-mono@5.0.0/index.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
(function(){
try {
const theme = localStorage.getItem('docsign_theme') || 'light';
document.documentElement.dataset.theme = theme;
const s = JSON.parse(localStorage.getItem('docsign_ambient') || '{}');
const rgb = s.color || '79,140,255';
const intensity = s.intensity || 80;
const spread = s.spread || 60;
const mode = s.mode || 'solid';
const lang = localStorage.getItem('docsign_lang') || 'ru';
document.documentElement.style.setProperty('--glow', rgb);
document.documentElement.style.setProperty('--glow-intensity', intensity);
document.documentElement.style.setProperty('--glow-spread', spread);
document.documentElement.style.setProperty('--glow-mode', mode);
document.documentElement.lang = lang;
if (localStorage.getItem('docsign_sidebar_collapsed') === 'true') {
    document.documentElement.classList.add('sidebar-collapsed');
}
} catch(e) {}
})();
</script>
<style>
    /* ============================================================ */
    /* ===== DESIGN TOKENS ===== */
    /* ============================================================ */
    :root{
        --font-sans: 'Inter', 'Segoe UI', Arial, sans-serif;
        --font-mono: 'JetBrains Mono', 'SFMono-Regular', Consolas, monospace;
        --radius: 6px;
        --topbar-h: 56px;
        --side-w: 227px;
        --side-w-c: 64px;
        --bg-0:#12161c;
        --bg-1:#1a2028;
        --bg-2:#202730;
        --bg-3:#273039;
        --line:rgba(255,255,255,0.08);
        --text:#e6eaf0;
        --muted:#9aa5b1;
        --accent:#206bc4;
        --glow: 79,140,255;
        --glow-soft: rgba(var(--glow),0.06);
        --shadow-soft: 0 4px 16px rgba(0,0,0,0.18);
        --shadow-card: 0 1px 2px rgba(0,0,0,0.25);
    }
    html[data-theme="light"]{
        --bg-0:#f4f6fa;
        --bg-1:#ffffff;
        --bg-2:#f1f4f9;
        --bg-3:#e7ebf2;
        --line:rgba(98,105,118,0.18);
        --text:#1e293b;
        --muted:#626b76;
        --shadow-soft: 0 4px 16px rgba(15,23,42,0.06);
        --shadow-card: 0 1px 2px rgba(15,23,42,0.04);
    }
    *{box-sizing:border-box}
    html,body{margin:0;padding:0;height:100%}
    html{scroll-behavior:smooth}
    body{
        font-family:var(--font-sans);
        -webkit-font-smoothing:antialiased;
        -moz-osx-font-smoothing:grayscale;
        text-rendering:optimizeLegibility;
        background:var(--bg-0);
        color:var(--text);
        min-height:100vh;
        overflow-x:hidden;
        transition:background .3s ease,color .3s ease;
        letter-spacing:-0.01em;
        font-size:14px;
    }
    body,button,input,select,textarea,code,pre,kbd,samp{font-family:var(--font-sans)}
    h1,h2,h3,h4,h5,h6{font-family:var(--font-sans);letter-spacing:-0.02em;margin:0;font-weight:600}
    p,span,div,label,small,strong,b,a{font-family:inherit}
    a{text-decoration:none;color:inherit}

    .theme-switcher{display:flex;align-items:center;gap:4px}

    /* ===== AMBIENT LIGHT STRIP ===== */
    .ambient{
        position:fixed;top:var(--topbar-h);left:0;right:0;height:1px;
        background:linear-gradient(90deg,transparent 0%,rgba(var(--glow),0.15) 20%,rgba(var(--glow),0.65) 50%,rgba(var(--glow),0.15) 80%,transparent 100%);
        z-index:50;animation:pulseGlow 5s ease-in-out infinite;transition:all .4s ease;opacity:.6;
    }
    .ambient::after{
        content:"";position:absolute;top:1px;left:0;right:0;height:20px;
        background:linear-gradient(180deg,rgba(var(--glow),0.08),transparent);
        filter:blur(8px);pointer-events:none;
    }
    @keyframes pulseGlow{0%,100%{opacity:.85}50%{opacity:1}}
    @keyframes breatheGlow{0%,100%{opacity:.4;transform:scaleY(.8)}50%{opacity:1;transform:scaleY(1.2)}}
    @keyframes pulseFast{0%,100%{opacity:.6}50%{opacity:1}}

    /* ===== TOPBAR ===== */
    .topbar{
        position:sticky;top:0;z-index:40;
        display:flex;align-items:center;gap:10px;
        padding:0 16px;height:var(--topbar-h);
        background:var(--bg-1);
        border-bottom:1px solid var(--line);
    }

      /* ===== МАЛЕНЬКАЯ КНОПКА СВОРАЧИВАНИЯ САЙДБАРА ===== */
    .sidebar-toggle-item:hover{background:var(--bg-2);color:var(--text)}
     .sidebar-toggle svg{width:13px;height:13px}
    .sidebar-toggle .expand-icon{display:none}
    html.sidebar-collapsed .sidebar-toggle .collapse-icon{display:none}
    html.sidebar-collapsed .sidebar-toggle .expand-icon{display:block}

    /* ===== БУРГЕР ===== */
    .burger-btn{
        display:none;width:34px;height:34px;border-radius:6px;
        background:transparent;border:1px solid var(--line);
        cursor:pointer;position:relative;transition:all .2s ease;flex-shrink:0;
    }
    .burger-btn:hover{background:var(--bg-2)}
    .burger-btn span{position:absolute;left:50%;width:16px;height:2px;background:var(--text);border-radius:2px;transform:translateX(-50%);transition:all .3s ease}
    .burger-btn span:nth-child(1){top:10px}
    .burger-btn span:nth-child(2){top:16px}
    .burger-btn span:nth-child(3){top:22px}
    .burger-btn.active span:nth-child(1){top:16px;transform:translateX(-50%) rotate(45deg)}
    .burger-btn.active span:nth-child(2){opacity:0}
    .burger-btn.active span:nth-child(3){top:16px;transform:translateX(-50%) rotate(-45deg)}

    .sidebar-overlay{
        display:none;position:fixed;top:0;right:0;bottom:0;left:280px;
        background:rgba(0,0,0,.5);z-index:999;opacity:0;transition:opacity .3s ease;pointer-events:none;
    }
    .sidebar-overlay.active{display:block;opacity:1;pointer-events:auto}

    .brand{display:flex;align-items:center;gap:10px;font-weight:700;letter-spacing:.4px;font-size:15px;margin-right:6px}
    .brand-dot{
        width:32px;height:32px;border-radius:8px;
        background:rgba(var(--glow),0.14);border:1px solid rgba(var(--glow),0.3);
        display:grid;place-items:center;flex-shrink:0;
    }
    .brand-dot svg{width:17px;height:17px;color:rgb(var(--glow))}

    /* ===== NAV ===== */
    .nav{display:flex;gap:2px}
    .nav a{display:inline-flex;align-items:center}
    .nav button{
        appearance:none;border:0;background:transparent;
        color:var(--muted);font:500 13px 'Inter',sans-serif;
        padding:7px 12px;border-radius:6px;cursor:pointer;
        display:flex;align-items:center;gap:7px;transition:all .15s ease;
    }
    .nav button svg{width:15px;height:15px}
    .nav button:hover{color:var(--text);background:rgba(128,139,155,0.10)}
    .nav button.active{color:rgb(var(--glow));background:rgba(var(--glow),0.10);font-weight:600}
    .spacer{flex:1}

    /* ===== SEARCH ===== */
    .search{
        display:flex;align-items:center;gap:8px;
        background:var(--bg-2);border:1px solid var(--line);
        padding:7px 12px;border-radius:8px;min-width:240px;transition:border-color .15s ease;
    }
    .search:focus-within{border-color:rgba(var(--glow),0.5)}
    .search input{background:transparent;border:0;outline:0;color:var(--text);font:13px 'Inter',sans-serif;width:100%}
    .search svg{width:14px;height:14px;color:var(--muted);flex-shrink:0}

    /* ===== ICON BTN (ghost, чистый стиль) ===== */
    .icon-btn{
        width:34px;height:34px;border-radius:8px;
        background:transparent;border:none;
        display:grid;place-items:center;cursor:pointer;
        color:var(--muted);transition:all .15s ease;position:relative;flex-shrink:0;padding:0;
    }
    .icon-btn:hover{color:var(--text);background:var(--bg-2)}
    .icon-btn svg{width:17px;height:17px}

    /* ===== PROFILE (фото круглое, не овальное) ===== */
    .profile-dropdown{position:relative}
    .profile-btn{
        width:34px;height:34px;flex-shrink:0;
        border-radius:50%;
        border:1px solid rgba(var(--glow),.35);
        background:rgba(var(--glow),.14);
        color:rgb(var(--glow));font-weight:700;font-size:12px;
        display:grid;place-items:center;cursor:pointer;
        transition:all .2s ease;padding:0;overflow:hidden;
        position:relative;aspect-ratio:1/1;
    }
    .profile-btn:hover{background:rgba(var(--glow),.24)}
    .profile-avatar{
        position:absolute;inset:0;
        width:100%;height:100%;
        object-fit:cover;object-position:center;
        border-radius:50%;display:block;
    }
    .profile-initials{display:flex;align-items:center;justify-content:center;width:100%;height:100%;line-height:1}
    .profile-menu{
        position:absolute;top:calc(100% + 6px);right:0;min-width:200px;
        background:var(--bg-1);border:1px solid var(--line);border-radius:8px;padding:4px;
        box-shadow:0 8px 24px rgba(0,0,0,.18);opacity:0;visibility:hidden;transform:translateY(-6px);
        transition:all .2s ease;z-index:1000;
    }
    .profile-menu.active{opacity:1;visibility:visible;transform:translateY(0)}
    .profile-menu .menu-item{display:flex;align-items:center;gap:10px;padding:9px 12px;color:var(--text);border-radius:6px;transition:all .15s ease;font-size:13px}
    .profile-menu .menu-item:hover{background:rgba(128,139,155,0.10)}
    .profile-menu .menu-item.logout{color:#d63939}
    .profile-menu .menu-item.logout:hover{background:rgba(214,57,57,0.10)}
    .profile-menu .menu-divider{border:0;border-top:1px solid var(--line);margin:4px 0}

    /* ===== LANGUAGE ===== */
    .lang-switcher{position:relative}
    .lang-btn{
        display:flex;align-items:center;gap:7px;padding:6px 10px;
        background:transparent;border:1px solid var(--line);border-radius:8px;
        color:var(--text);font:500 12px 'Inter',sans-serif;cursor:pointer;transition:all .15s ease;min-width:86px;justify-content:space-between;
    }
    .lang-btn:hover{background:var(--bg-2)}
    .flag{
    width:18px;height:13px;border-radius:3px;overflow:hidden;
    display:inline-block;flex-shrink:0;line-height:0;
    box-shadow:0 0 0 1px rgba(0,0,0,.12);
}
.flag svg{width:100%;height:100%;display:block}
.lang-option .flag{width:20px;height:15px;border-radius:3px}
.lang-btn .arrow{width:12px;height:12px;transition:transform .2s ease}
    .lang-btn.open .arrow{transform:rotate(180deg)}
    .lang-menu{
        position:absolute;top:calc(100% + 6px);right:0;min-width:180px;
        background:var(--bg-1);border:1px solid var(--line);border-radius:8px;padding:4px;
        box-shadow:0 8px 24px rgba(0,0,0,.18);opacity:0;visibility:hidden;transform:translateY(-6px);transition:all .2s ease;z-index:1000;
    }
    .lang-menu.open{opacity:1;visibility:visible;transform:translateY(0)}
    .lang-menu-title{font-size:10px;letter-spacing:1px;color:var(--muted);text-transform:uppercase;padding:8px 10px 6px;font-weight:600}
    .lang-option{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:6px;cursor:pointer;color:var(--text);font-size:13px;font-weight:500;transition:all .15s ease;border:1px solid transparent}
    .lang-option:hover{background:rgba(128,139,155,0.10)}
    .lang-option.active{background:rgba(var(--glow),0.10);color:rgb(var(--glow));font-weight:600}
    .lang-option .name{flex:1}
    .lang-option .check{width:14px;height:14px;color:rgb(var(--glow));opacity:0;transition:opacity .15s ease}
    .lang-option.active .check{opacity:1}

    /* ===== AMBIENT BUTTON / PANEL ===== */
    .amb-btn{
        position:relative;display:flex;align-items:center;gap:7px;padding:6px 12px;
        background:transparent;border:1px solid var(--line);border-radius:8px;
        color:var(--text);font:500 12px 'Inter',sans-serif;cursor:pointer;transition:all .15s ease;
    }
    .amb-btn:hover{background:var(--bg-2)}
    .amb-btn .arrow{width:12px;height:12px}
    .amb-btn .amb-swatch{width:13px;height:13px;border-radius:50%;background:rgb(var(--glow));box-shadow:0 0 8px rgba(var(--glow),.7);animation:swatchPulse 2s ease-in-out infinite}
    @keyframes swatchPulse{0%,100%{box-shadow:0 0 6px rgba(var(--glow),.6)}50%{box-shadow:0 0 12px rgba(var(--glow),.9)}}
    .amb-panel{
        position:absolute;top:calc(100% + 8px);right:0;width:340px;
        background:var(--bg-1);border:1px solid var(--line);border-radius:10px;padding:20px;
        box-shadow:0 8px 24px rgba(0,0,0,.18);opacity:0;visibility:hidden;transform:translateY(-8px) scale(.98);
        transform-origin:top right;transition:all .2s ease;z-index:1000;
    }
    .amb-panel::before{content:"";position:absolute;top:-6px;right:30px;width:12px;height:12px;background:var(--bg-1);border-left:1px solid var(--line);border-top:1px solid var(--line);transform:rotate(45deg)}
    .amb-panel.open{opacity:1;visibility:visible;transform:translateY(0) scale(1)}
    .amb-panel-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:4px}
    .amb-panel h4{margin:0;font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px}
    .amb-panel h4 .live-dot{width:8px;height:8px;border-radius:50%;background:rgb(var(--glow));box-shadow:0 0 8px rgba(var(--glow),.8);animation:swatchPulse 1.5s ease-in-out infinite}
    .amb-panel p{margin:0 0 16px;font-size:11px;color:var(--muted)}
    .amb-preview{height:64px;border-radius:6px;background:linear-gradient(90deg,rgba(var(--glow),.1),rgba(var(--glow),.5),rgba(var(--glow),.1));border:1px solid rgba(var(--glow),.3);margin-bottom:16px;position:relative;overflow:hidden}
    .amb-preview::before{content:"";position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);animation:sweep 2.5s ease-in-out infinite}
    @keyframes sweep{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
    .amb-preview-label{position:absolute;bottom:8px;left:12px;font-size:10px;font-family:'JetBrains Mono',monospace;color:rgba(255,255,255,.9);letter-spacing:1px;text-shadow:0 1px 4px rgba(0,0,0,.5);z-index:2}
    .colors{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px}
    .color{width:34px;height:34px;flex:0 0 auto;border-radius:6px;cursor:pointer;border:2px solid transparent;position:relative;transition:all .2s ease;box-shadow:0 1px 3px rgba(0,0,0,.2)}
    .color:hover{transform:translateY(-2px)}
    .color.active{border-color:var(--text);transform:scale(1.05)}
    .color.active::after{content:"✓";position:absolute;inset:0;display:grid;place-items:center;color:#fff;font-weight:700;text-shadow:0 1px 3px rgba(0,0,0,.7);font-size:13px}
    .intensity{display:flex;flex-direction:column;gap:6px}
    .intensity label{font-size:11px;color:var(--muted);display:flex;justify-content:space-between;font-weight:500}
    .intensity label span:last-child{color:rgb(var(--glow));font-family:'JetBrains Mono',monospace;font-weight:700}
    .intensity input[type=range]{-webkit-appearance:none;appearance:none;width:100%;height:4px;border-radius:2px;background:linear-gradient(90deg,rgba(var(--glow),.3),rgba(var(--glow),1));outline:none}
    .intensity input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:16px;height:16px;border-radius:50%;background:#fff;cursor:pointer;box-shadow:0 1px 4px rgba(0,0,0,.3);border:2px solid rgba(var(--glow),.7)}
    .intensity input[type=range]::-moz-range-thumb{width:16px;height:16px;border-radius:50%;background:#fff;cursor:pointer;box-shadow:0 1px 4px rgba(0,0,0,.3);border:2px solid rgba(var(--glow),.7)}
    .modes{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:14px}
    .mode{padding:9px 8px;border-radius:6px;font-size:11px;font-weight:500;background:transparent;border:1px solid var(--line);color:var(--muted);cursor:pointer;text-align:center;transition:all .15s ease;display:flex;align-items:center;justify-content:center;gap:6px}
    .mode svg{width:12px;height:12px}
    .mode:hover{color:var(--text);background:var(--bg-2)}
    .mode.active{color:rgb(var(--glow));background:rgba(var(--glow),0.10);border-color:rgba(var(--glow),.4);font-weight:600}
    .amb-footer{margin-top:14px;padding-top:12px;border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;font-size:10px;color:var(--muted)}
    .amb-footer .saved{display:flex;align-items:center;gap:5px;color:#2fb344;opacity:0;transition:opacity .3s ease}
    .amb-footer .saved.show{opacity:1}

    /* ===== MOBILE SEARCH ===== */
    .mobile-search-btn{display:none}
    .mobile-search-bar{
        display:none;position:fixed;top:var(--topbar-h);left:0;right:0;z-index:38;
        padding:10px 14px;background:var(--bg-1);border-bottom:1px solid var(--line);
        opacity:0;transform:translateY(-6px);transition:opacity .2s ease,transform .2s ease;
    }
    .mobile-search-bar.active{display:block;opacity:1;transform:translateY(0)}
    .mobile-search-bar-form{display:flex;align-items:center;gap:8px;background:var(--bg-2);border:1px solid var(--line);padding:8px 12px;border-radius:8px}
    .mobile-search-bar-form input{background:transparent;border:0;outline:0;color:var(--text);font:13px 'Inter',sans-serif;width:100%}
    .mobile-search-bar-form svg{width:14px;height:14px;color:var(--muted);flex-shrink:0}

    /* ===== LAYOUT: сайдбар-колонка как на фото ===== */
    .layout{
        display:grid;
        grid-template-columns:var(--side-w) minmax(0,1fr);
        min-height:calc(100vh - var(--topbar-h));
    }
    .sidebar{
        background:var(--bg-1);
        border-right:1px solid var(--line);
       padding:12px 8px 16px;
        height:calc(100vh - var(--topbar-h));
        position:sticky;top:var(--topbar-h);
        overflow-y:auto;overflow-x:hidden;
        display:flex;flex-direction:column;gap:2px;
        transition:width .25s ease,padding .25s ease,transform .3s ease;
    }
    .sidebar::-webkit-scrollbar{width:4px}
    .sidebar::-webkit-scrollbar-track{background:transparent}
    .sidebar::-webkit-scrollbar-thumb{background:rgba(var(--glow),.3);border-radius:10px}

    /* свёрнутый сайдбар */
    @media (min-width:1101px){
        html.sidebar-collapsed .layout{grid-template-columns:var(--side-w-c) minmax(0,1fr)}
        html.sidebar-collapsed .sidebar{padding:14px 8px}
        html.sidebar-collapsed .side-title,
        html.sidebar-collapsed .side-item span,
        html.sidebar-collapsed .side-badge{display:none}
        html.sidebar-collapsed .side-item{justify-content:center;gap:0;padding:10px 0}
        html.sidebar-collapsed .side-item-inner{display:flex;justify-content:center}
        html.sidebar-collapsed .mobile-search,
        html.sidebar-collapsed .mobile-nav-group{display:none}
    }

    .side-title{
        font-size:10px;letter-spacing:1.4px;color:var(--muted);
        text-transform:uppercase;margin:16px 10px 6px;font-weight:600;white-space:nowrap;
    }
    .side-title:first-child{margin-top:4px}
    .side-item{
        display:flex;align-items:center;gap:10px;
        padding:8px 10px;border-radius:6px;
        color:var(--muted);font-size:13px;font-weight:500;
        cursor:pointer;transition:all .15s ease;position:relative;white-space:nowrap;
    }
    .side-item svg{width:16px;height:16px;flex-shrink:0}
    .side-item:hover{color:var(--text);background:rgba(128,139,155,0.10)}
         .side-item.active{
        color:rgb(var(--glow));
        background: linear-gradient(90deg, rgba(var(--glow), 0.18) 0%, rgba(var(--glow), 0.08) 100%);
        font-weight:600;
        box-shadow: 0 2px 10px rgba(var(--glow), 0.18);
    }
     .side-badge{
        margin-left:auto;font-size:10px;padding:2px 7px;border-radius:10px;
        background:rgba(var(--glow),0.12);color:rgb(var(--glow));
        border:1px solid rgba(var(--glow),.25);font-family:'JetBrains Mono',monospace;font-weight:600;
    }
    .side-item.logout-item{color:#d63939}
    .side-item.logout-item:hover{background:rgba(214,57,57,.10);color:#d63939}

    .mobile-nav-group{display:none;flex-direction:column;gap:2px;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--line)}
    .mobile-search{display:none;align-items:center;gap:8px;background:var(--bg-2);border:1px solid var(--line);padding:8px 12px;border-radius:8px;margin-bottom:10px}
    .mobile-search svg{width:14px;height:14px;color:var(--muted);flex-shrink:0}
    .mobile-search input{background:transparent;border:0;outline:0;color:var(--text);font:13px 'Inter',sans-serif;width:100%}
    .mobile-search input::placeholder{color:var(--muted)}

    /* ===== MAIN ===== */
    .main{display:flex;flex-direction:column;gap:20px;min-width:0;padding:22px 26px}
    .page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap}
    .page-head h1{margin:0;font-size:22px;font-weight:600;letter-spacing:-.3px}
    .page-head p{margin:4px 0 0;color:var(--muted);font-size:13px}
    .actions{display:flex;gap:10px}
    .btn{
        appearance:none;border:1px solid var(--line);background:var(--bg-1);color:var(--text);
        font:500 13px 'Inter',sans-serif;padding:9px 14px;border-radius:6px;cursor:pointer;
        display:inline-flex;align-items:center;gap:8px;transition:all .15s ease;
    }
    .btn svg{width:14px;height:14px}
    .btn:hover{background:var(--bg-2)}
    .btn.primary{background:rgb(var(--glow));color:#fff;border-color:rgb(var(--glow));font-weight:600}
    .btn.primary:hover{filter:brightness(1.06)}

    /* ===== CARDS / STATS ===== */
    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
    .card{position:relative;background:var(--bg-1);border:1px solid var(--line);border-radius:8px;padding:18px;overflow:hidden;transition:border-color .15s ease;box-shadow:var(--shadow-card)}
    .card:hover{border-color:rgba(128,139,155,.35)}
    .stat-top{display:flex;align-items:center;justify-content:space-between}
    .stat-label{font-size:12px;color:var(--muted);font-weight:500}
    .stat-icon{width:34px;height:34px;border-radius:8px;background:rgba(var(--glow),0.10);display:grid;place-items:center;color:rgb(var(--glow))}
    .stat-icon svg{width:16px;height:16px}
    .stat-value{font-size:24px;font-weight:600;margin-top:12px;letter-spacing:-.3px}
    .stat-delta{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-family:'JetBrains Mono',monospace;padding:2px 7px;border-radius:6px;margin-top:8px;font-weight:600}
    .delta-up{background:rgba(47,179,68,.12);color:#2fb344;border:1px solid rgba(47,179,68,.25)}
    .delta-down{background:rgba(214,57,57,.12);color:#d63939;border:1px solid rgba(214,57,57,.25)}
    .stat-spark{margin-top:14px;height:40px;position:relative;overflow:hidden}
    .stat-spark svg{width:100%;height:100%;display:block}

    .row{display:grid;grid-template-columns:1.6fr 1fr;gap:16px}
    .panel{background:var(--bg-1);border:1px solid var(--line);border-radius:8px;padding:20px;box-shadow:var(--shadow-card)}
    .panel-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:10px;flex-wrap:wrap}
    .panel-head h3{margin:0;font-size:14px;font-weight:600}
    .panel-head .sub{font-size:11px;color:var(--muted);margin-top:2px}
    .tabs{display:flex;gap:2px;border-radius:6px;border:1px solid var(--line);padding:0}
    .tabs button{border:0;background:transparent;color:var(--muted);font:500 11px 'Inter',sans-serif;padding:5px 10px;border-radius:6px;cursor:pointer}
    .tabs button:hover{color:var(--text);background:var(--bg-2)}
    .tabs button.active{color:rgb(var(--glow));background:rgba(var(--glow),0.10);font-weight:600}
    .chart{height:260px;position:relative}
    .chart svg{width:100%;height:100%}

    /* ===== TABLE ===== */
    table{width:100%;border-collapse:collapse}
    th,td{text-align:left;padding:10px 8px;font-size:12.5px;border-bottom:1px solid var(--line)}
    th{color:var(--muted);font-weight:600;font-size:10px;text-transform:uppercase;letter-spacing:.05em}
    td{color:var(--text)}
    tbody tr{transition:background .15s ease}
    tbody tr:hover{background:rgba(128,139,155,.06)}
    .doc-cell{display:flex;align-items:center;gap:10px}
    .doc-icon{width:32px;height:38px;border-radius:6px;background:rgba(var(--glow),0.10);border:1px solid rgba(var(--glow),.25);display:grid;place-items:center;font-size:9px;font-weight:700;letter-spacing:.5px;color:rgb(var(--glow));font-family:'JetBrains Mono',monospace;flex-shrink:0;position:relative}
    .doc-icon::after{content:"";position:absolute;top:0;right:0;width:8px;height:8px;background:linear-gradient(135deg,transparent 50%,rgba(var(--glow),.35) 50%);border-bottom-left-radius:3px}
    .doc-meta small{display:block;color:var(--muted);font-size:11px;margin-top:2px}
    .pill{display:inline-flex;align-items:center;gap:5px;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:500;border:1px solid var(--line)}
    .pill::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
    .pill.signed{color:#2fb344;background:rgba(47,179,68,.10);border-color:rgba(47,179,68,.25)}
    .pill.pending{color:#f76707;background:rgba(247,103,7,.10);border-color:rgba(247,103,7,.25)}
    .pill.rejected{color:#d63939;background:rgba(214,57,57,.10);border-color:rgba(214,57,57,.25)}
    .pill.draft{color:#8892a6;background:rgba(136,146,166,.10);border-color:rgba(136,146,166,.2)}

    .activity{display:flex;flex-direction:column;gap:14px}
    .act{display:flex;gap:12px;align-items:flex-start}
    .act-dot{width:8px;height:8px;border-radius:50%;margin-top:6px;background:rgb(var(--glow));flex-shrink:0}
    .act-body{flex:1;min-width:0}
    .act-body p{margin:0;font-size:13px}
    .act-body small{color:var(--muted);font-size:11px}

    .signers{display:flex;flex-direction:column;gap:10px}
    .signer{display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;background:var(--bg-1);border:1px solid var(--line);transition:all .15s ease}
    .signer:hover{border-color:rgba(var(--glow),.35);background:rgba(var(--glow),.04)}
    .signer-avatar{width:36px;height:36px;border-radius:50%;background:rgba(var(--glow),.15);border:1px solid rgba(var(--glow),.3);display:grid;place-items:center;font-weight:700;font-size:12px;color:rgb(var(--glow));flex-shrink:0}
    .signer-info{flex:1;min-width:0}
    .signer-info b{display:block;font-size:13px;font-weight:600}
    .signer-info small{color:var(--muted);font-size:11px}
    .signer-status{font-size:10px;padding:2px 8px;border-radius:6px;font-weight:600;letter-spacing:.3px}
    .status-ok{background:rgba(47,179,68,.12);color:#2fb344;border:1px solid rgba(47,179,68,.3)}
    .status-wait{background:rgba(247,103,7,.12);color:#f76707;border:1px solid rgba(247,103,7,.3)}

    /* ===== NOTIFICATIONS ===== */
    .notif-dropdown{position:absolute;top:calc(100% + 8px);right:0;width:340px;background:var(--bg-1);border:1px solid var(--line);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);z-index:1000;opacity:0;visibility:hidden;transform:translateY(-6px);transition:all .2s ease;overflow:hidden}
    .notif-dropdown.active{opacity:1;visibility:visible;transform:translateY(0)}
    .notif-header{padding:12px 16px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center}
    .notif-header strong{font-size:.875rem;color:var(--text);font-weight:600}
    .notif-list{max-height:320px;overflow-y:auto}
    .notif-list::-webkit-scrollbar{width:4px}
    .notif-list::-webkit-scrollbar-thumb{background:rgba(var(--glow),.4);border-radius:10px}
    .notif-item{display:flex;align-items:flex-start;gap:10px;padding:12px 16px;text-decoration:none;color:var(--text);border-bottom:1px solid var(--line);transition:background .15s;position:relative}
    .notif-item:hover{background:rgba(128,139,155,.06)}
    .notif-item.unread{background:rgba(var(--glow),.06)}
    .notif-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.05rem}
    .notif-body{flex:1;min-width:0}
    .notif-text{font-size:.8rem;line-height:1.45;color:var(--text)}
    .notif-dot{width:8px;height:8px;border-radius:50%;background:rgb(var(--glow));margin-top:6px}
    .notif-empty{padding:40px 20px;text-align:center;color:var(--muted)}
    .notif-footer{padding:10px;border-top:1px solid var(--line);text-align:center;background:var(--bg-2)}
    .notif-footer a{color:rgb(var(--glow));text-decoration:none;font-size:.82rem;font-weight:600}
    .notif-footer a:hover{text-decoration:underline}
    .notif-badge{top:-4px !important;right:-4px !important;font-size:10px;padding:1px 5px;background:#d63939;color:#fff;border:2px solid var(--bg-1);border-radius:20px;font-weight:700;min-width:18px;text-align:center;position:absolute}
    .notif-ping-dot{top:5px;right:6px;width:8px;height:8px;position:absolute;border-radius:50%;background:#d63939;animation:notif-ping 1.5s infinite;opacity:.8}
    @keyframes notif-ping{0%{transform:scale(1);opacity:.8}50%{transform:scale(1.5);opacity:0}100%{transform:scale(1);opacity:0}}
    .bell-shaking{animation:shake 2s infinite}
    @keyframes shake{0%,100%{transform:rotate(0)}10%,30%,50%,70%,90%{transform:rotate(-8deg)}20%,40%,60%,80%{transform:rotate(8deg)}}
    @keyframes notif-fresh-pulse{0%{box-shadow:0 0 0 0 rgba(var(--glow),.5)}70%{box-shadow:0 0 0 10px rgba(var(--glow),0)}100%{box-shadow:0 0 0 0 rgba(var(--glow),0)}}
    .notif-item.fresh{animation:notif-fresh-pulse 1.5s ease-out 2;background:rgba(var(--glow),.08) !important;border-left:3px solid rgb(var(--glow)) !important}
    @keyframes bell-ring{0%,100%{transform:rotate(0)}10%,30%{transform:rotate(-15deg)}20%,40%{transform:rotate(15deg)}}
    #notifBtn.ringing svg{animation:bell-ring .8s ease-in-out;transform-origin:top center}

    /* ===== СВЕТЛАЯ ТЕМА (как на фото) ===== */
    html[data-theme="light"] body{color:#1e293b;background:var(--bg-0)}
    html[data-theme="light"] .topbar{background:#ffffff;border-bottom-color:var(--line)}
    html[data-theme="light"] .sidebar,
    html[data-theme="light"] .card,
    html[data-theme="light"] .panel,
    html[data-theme="light"] .signer{background:#ffffff;border-color:var(--line);box-shadow:var(--shadow-card)}
    html[data-theme="light"] .search,
    html[data-theme="light"] .mobile-search,
    html[data-theme="light"] .mobile-search-bar-form{background:#f1f4f9;border-color:var(--line)}
    html[data-theme="light"] .icon-btn:hover,
    html[data-theme="light"] .btn:hover,
    html[data-theme="light"] .lang-btn:hover,
    html[data-theme="light"] .amb-btn:hover,
    html[data-theme="light"] .sidebar-toggle:hover,
    html[data-theme="light"] .burger-btn:hover{background:#eef2f7;color:#1e293b}
    html[data-theme="light"] .lang-menu,
    html[data-theme="light"] .amb-panel,
    html[data-theme="light"] .profile-menu,
    html[data-theme="light"] .notif-dropdown{background:#ffffff;border-color:var(--line);box-shadow:0 8px 24px rgba(15,23,42,.12)}
    html[data-theme="light"] .amb-panel::before{background:#ffffff}
    html[data-theme="light"] .mobile-search-bar{background:#ffffff}
    html[data-theme="light"] .nav button:hover,
    html[data-theme="light"] .tabs button:hover,
    html[data-theme="light"] .lang-option:hover,
    html[data-theme="light"] .profile-menu .menu-item:hover{background:rgba(98,105,118,.08)}
    html[data-theme="light"] .side-item{color:#4b5563}
    html[data-theme="light"] .side-item:hover{color:#0f172a;background:rgba(98,105,118,.08)}
         html[data-theme="light"] .side-item.active{
        color:rgb(var(--glow));
        background: linear-gradient(90deg, rgba(var(--glow), 0.16) 0%, rgba(var(--glow), 0.06) 100%);
        font-weight:600;
        box-shadow: 0 2px 8px rgba(var(--glow), 0.15);
    }
    html[data-theme="light"] .nav button.active,
    html[data-theme="light"] .tabs button.active,
    html[data-theme="light"] .mode.active,
    html[data-theme="light"] .lang-option.active{background:rgba(var(--glow),.10);color:rgb(var(--glow))}
    html[data-theme="light"] .side-title{color:#8a94a6}
    html[data-theme="light"] .page-head p,
    html[data-theme="light"] .panel-head .sub,
    html[data-theme="light"] .stat-label{color:#626b76}
    html[data-theme="light"] .stat-value,
    html[data-theme="light"] .panel-head h3,
    html[data-theme="light"] .doc-meta b{color:#1e293b;font-weight:600}
    html[data-theme="light"] .notif-text,
    html[data-theme="light"] .notif-header strong,
    html[data-theme="light"] .profile-menu .menu-item{color:#1e293b}
    html[data-theme="light"] .profile-menu .menu-item.logout{color:#d63939}
    html[data-theme="light"] .burger-btn span{background:#1e293b}
    html[data-theme="light"] .btn{background:#ffffff;border-color:var(--line)}
    html[data-theme="light"] .btn.primary{background:rgb(var(--glow));color:#fff;border-color:rgb(var(--glow))}
    html[data-theme="light"] .modal-box,
    html[data-theme="light"] .toast{background:#ffffff !important;color:#1e293b !important}
    html[data-theme="light"] .table-modern td,
    html[data-theme="light"] .table-modern th{border-bottom-color:var(--line) !important}
    html[data-theme="light"] .pagination-wrapper .page-link{background:#ffffff !important;color:#626b76 !important}

    /* ===== ТЕМЫ: КНОПКА ===== */
    .theme-btn{position:relative;overflow:hidden}
    .theme-btn svg{position:absolute;top:50%;left:50%;transition:opacity .3s ease,transform .4s cubic-bezier(.34,1.56,.64,1)}
    html[data-theme="dark"] .theme-btn .icon-sun{opacity:1;transform:translate(-50%,-50%) rotate(0) scale(1)}
    html[data-theme="dark"] .theme-btn .icon-moon{opacity:0;transform:translate(-50%,-50%) rotate(120deg) scale(.4)}
    html[data-theme="light"] .theme-btn .icon-moon{opacity:1;transform:translate(-50%,-50%) rotate(0) scale(1)}
    html[data-theme="light"] .theme-btn .icon-sun{opacity:0;transform:translate(-50%,-50%) rotate(-120deg) scale(.4)}

    /* ===== ДОСТУПНОСТЬ ===== */
    :focus-visible{outline:2px solid rgb(var(--glow));outline-offset:2px;border-radius:4px}
    @media (prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms !important;animation-iteration-count:1 !important;transition-duration:.01ms !important}}

    /* ===== АДАПТИВ ===== */
    @media (max-width:1200px){
        .search{min-width:180px}
        .main{padding:18px 20px}
    }
    @media (max-width:1100px){
        .sidebar-toggle{display:none}
        .burger-btn{display:flex;align-items:center;justify-content:center}
        .mobile-search-btn{display:grid}
        .search{display:none}
        .layout{grid-template-columns:1fr;min-height:auto}
        .main{padding:16px}
        .sidebar{
            position:fixed;top:0;left:0;width:280px;height:100vh;max-height:100vh;
            z-index:1000;transform:translateX(-100%);padding:70px 12px 20px;border-right:1px solid var(--line);
            box-shadow:4px 0 24px rgba(0,0,0,.12);
        }
        .sidebar.active{transform:translateX(0)}
        .mobile-nav-group{display:flex}
        .mobile-search{display:flex}
        body.menu-open .ambient{display:none}
        .stats{grid-template-columns:repeat(2,1fr)}
        .row{grid-template-columns:1fr}
        .nav button{padding:6px 9px;font-size:12px}
        .nav button svg{width:14px;height:14px}
        .lang-btn{min-width:76px;padding:5px 9px;font-size:11px}
        .amb-btn .amb-label{display:none}
        .notif-dropdown{width:320px}
        .amb-panel{position:fixed;top:56px;left:50%;right:auto;transform:translateX(-50%) translateY(-8px) scale(.98);width:320px}
        .amb-panel::before{display:none}
        .amb-panel.open{transform:translateX(-50%) translateY(0) scale(1)}
    }
    @media (max-width:768px){
        .topbar{padding:0 10px;gap:6px}
        .nav{display:none}
        .brand{font-size:13px;gap:8px}
        .brand-dot{width:28px;height:28px}
        .brand-dot svg{width:15px;height:15px}
        .main{padding:12px;gap:14px}
        .sidebar{width:260px}
        .sidebar-overlay{left:260px}
        .page-head h1{font-size:18px}
        .stats{gap:12px}
        .card{padding:14px}
        .stat-value{font-size:19px}
        .panel{padding:14px}
        .chart{height:220px}
        table{display:block;overflow-x:auto;-webkit-overflow-scrolling:touch}
        th,td{padding:7px 6px;font-size:11px;white-space:nowrap}
        .notif-dropdown{width:calc(100vw - 20px);right:-10px;max-width:320px}
        .amb-panel{width:calc(100vw - 20px);max-width:320px}
        .lang-btn .lang-name{display:none}
        .lang-btn{min-width:auto}
    }
    @media (max-width:480px){
        .topbar{padding:0 8px;gap:4px}
        .brand span{display:none}
        .main{padding:10px;gap:10px}
        .sidebar{width:240px}
        .sidebar-overlay{left:240px}
        .stats{grid-template-columns:1fr 1fr;gap:8px}
        .card{padding:12px}
        .stat-value{font-size:17px}
        .notif-dropdown{width:calc(100vw - 16px);right:-4px}
    }
</style>
</head>
<body>

<!-- TOP BAR -->
<header class="topbar">
    {{-- МАЛЕНЬКАЯ кнопка свернуть/развернуть сайдбар — теперь в топбаре --}}
   

    <button type="button" class="burger-btn" id="burgerBtn" aria-label="Открыть меню" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>

    <a href="/dashboard" class="brand">
       
        <span>DocSign</span>
    </a>

    <nav class="nav" id="nav">
        <a href="/dashboard">
            <button type="button" class="{{ request()->is('dashboard') || request()->is('/') ? 'active' : '' }}" data-tab="dashboard">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                <span data-i18n="dashboard">Обзор</span>
            </button>
        </a>
        <button type="button" data-tab="docs" class="{{ request()->is('documents*') ? 'active' : '' }}" onclick="window.location.href='{{ route('documents.index', ['tab' => 'all']) }}'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <span data-i18n="documents">Документы</span>
        </button>
        <a href="/signatures">
            <button type="button" data-tab="sign" class="{{ request()->is('signatures*') ? 'active' : '' }}" style="pointer-events:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
                <span data-i18n="signatures">Подписание</span>
            </button>
        </a>
        <a href="/users" data-tab="counter">
            <button type="button" class="{{ request()->is('users*') ? 'active' : '' }}" style="pointer-events:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span data-i18n="counterparties">Команда</span>
            </button>
        </a>
    </nav>

    <div class="spacer"></div>

    <form action="{{ route('search') }}" method="GET" class="search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" name="query" value="{{ request('query') }}"
               data-i18n-placeholder="search_placeholder" placeholder="Поиск..." />
        <button type="submit" style="display:none;"></button>
    </form>

    <button type="button" class="icon-btn mobile-search-btn" id="mobileSearchBtn" data-i18n-title="search_placeholder" title="Поиск" aria-label="Поиск">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
    </button>

    @php
    $user = auth()->user();
    $notifications = $user ? $user->notifications()->latest()->take(5)->get() : collect();
    $unreadCount = $user ? $user->unreadNotifications->count() : 0;
    $notifRoute = Route::has('notifications.index') ? route('notifications.index') : '#';
    @endphp

    {{-- ===== AMBIENT BUTTON ===== --}}
    <div class="lang-switcher" id="ambWrapper">
        <button type="button" class="amb-btn" id="ambBtn" data-i18n-title="color_title" title="Цвет подсветки">
            <div class="amb-swatch"></div>
            <span class="amb-label" data-i18n="color_title">Цвет</span>
            <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </button>

        <div class="amb-panel" id="ambPanel">
            <div class="amb-panel-head">
                <h4>
                    <span class="live-dot"></span>
                    <span data-i18n="ambient_title">Ambient Lighting</span>
                </h4>
            </div>
            <p data-i18n="ambient_desc">Настройте подсветку как в премиальном авто</p>
            <div class="amb-preview" id="ambPreview">
                <div class="amb-preview-label" id="ambPreviewLabel">RGB(79, 140, 255)</div>
            </div>
            <div class="colors" id="colors"></div>
            <div class="intensity">
                <label>
                    <span data-i18n="intensity">Интенсивность</span>
                    <span id="intVal">80%</span>
                </label>
                <input type="range" id="intensity" min="20" max="100" value="80"/>
            </div>
            <div class="intensity" style="margin-top:12px">
                <label>
                    <span data-i18n="spread">Распространение</span>
                    <span id="spreadVal">60%</span>
                </label>
                <input type="range" id="spread" min="20" max="100" value="60"/>
            </div>
            <div class="modes">
                <div class="mode active" data-mode="solid">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="4"/></svg>
                    <span data-i18n="mode_solid">Статичный</span>
                </div>
                <div class="mode" data-mode="pulse">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <span data-i18n="mode_pulse">Пульс</span>
                </div>
                <div class="mode" data-mode="breathe">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <span data-i18n="mode_breathe">Дыхание</span>
                </div>
            </div>
            <div class="amb-footer">
                <span data-i18n="auto_save">Автосохранение</span>
                <span class="saved" id="savedIndicator">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span data-i18n="saved">Сохранено</span>
                </span>
            </div>
        </div>
    </div>

    <!-- LANGUAGE SWITCHER -->
    <div class="lang-switcher" id="langSwitcher">
             <button type="button" class="lang-btn" id="langBtn" title="Язык / Language / Забон">
            <span class="flag" id="langFlag"><svg viewBox="0 0 24 18"><rect width="24" height="6" fill="#ffffff"/><rect y="6" width="24" height="6" fill="#0039a6"/><rect y="12" width="24" height="6" fill="#d52b1e"/></svg></span>
            <span class="lang-name" id="langName">Русский</span>
             <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </button>
        <div class="lang-menu" id="langMenu">
            <div class="lang-menu-title" data-i18n="choose_language">Выберите язык</div>
                       <div class="lang-option active" data-lang="ru">
                <span class="flag"><svg viewBox="0 0 24 18"><rect width="24" height="6" fill="#ffffff"/><rect y="6" width="24" height="6" fill="#0039a6"/><rect y="12" width="24" height="6" fill="#d52b1e"/></svg></span><span class="name">Русский</span>
                 <svg class="check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="lang-option" data-lang="tj">
              <span class="flag"><svg viewBox="0 0 24 18"><rect width="24" height="18" fill="#ffffff"/><rect width="24" height="5" fill="#cc0000"/><rect y="13" width="24" height="5" fill="#006600"/><circle cx="12" cy="9" r="2.2" fill="#f8c300"/></svg></span>  <span class="name">Тоҷикӣ</span>
                <svg class="check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="lang-option" data-lang="en">
             <span class="flag"><svg viewBox="0 0 24 18"><rect width="24" height="18" fill="#012169"/><path d="M0 0l24 18M24 0L0 18" stroke="#fff" stroke-width="3"/><path d="M12 0v18M0 9h24" stroke="#fff" stroke-width="5"/><path d="M12 0v18M0 9h24" stroke="#c8102e" stroke-width="3"/></svg></span>
               <span class="name">English</span>
                <svg class="check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
        </div>
    </div>

    <!-- THEME TOGGLE -->
    <div class="theme-switcher" aria-label="Тема интерфейса">
        <button type="button" class="icon-btn theme-btn" id="themeToggleBtn" title="Сменить тему" aria-label="Сменить тему">
            <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
            <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>
    </div>

    <!-- NOTIFICATIONS -->
    <div id="notifWrapper" style="position:relative;">
        <button type="button" id="notifBtn" class="icon-btn {{ $unreadCount > 0 ? 'bell-shaking' : '' }}"
                data-i18n-title="notifications" title="Уведомления">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            @if($unreadCount > 0)
            <span id="notifPing" class="notif-ping-dot"></span>
            <span id="notifBadge" class="notif-badge">{{ $unreadCount }}</span>
            @endif
        </button>

        <div id="notifDropdown" class="notif-dropdown">
            <div class="notif-header">
                <strong data-i18n="notifications">Уведомления</strong>
                <span id="headerBadge" class="notif-badge" style="position:static;border:none;{{ $unreadCount > 0 ? '' : 'display:none;' }}">{{ $unreadCount }}</span>
            </div>
            <div id="notifList" class="notif-list">
                @forelse($notifications as $notification)
                @php
                $data = is_string($notification->data) ? json_decode($notification->data, true) : ($notification->data ?? []);
                $sender = $data['sender_name'] ?? $data['user_name'] ?? 'Система';
                $docName = $data['document_name'] ?? $data['document_title'] ?? 'Документ';
                $message = $notification->messages ?? '';
                $isComment = str_contains(strtolower($message), 'коммент')
                || ($data['type'] ?? '') === 'comment'
                || $notification->type === 'comment';
                $action = $isComment ? 'оставил комментарий к' : 'назначил вам документ';
                $iconClass = $isComment ? 'bi-chat-left-text' : 'bi-pin-angle-fill';
                $iconColor = $isComment ? '#2fb344' : '#f76707';
                $isUnread = !$notification->is_read;
                $url = $data['url'] ?? (isset($data['document_id']) ? route('documents.show', $data['document_id']) : '#');
                @endphp
                <a href="{{ $url }}" class="notif-item {{ $isUnread ? 'unread' : '' }}"
                   data-id="{{ $notification->id }}" data-url="{{ $url }}" data-ts="{{ $notification->created_at->timestamp }}">
                    <div class="notif-icon" style="background: {{ $isComment ? 'rgba(47,179,68,0.1)' : 'rgba(247,103,7,0.1)' }};">
                        <i class="bi {{ $iconClass }}" style="color: {{ $iconColor }};"></i>
                    </div>
                    <div class="notif-body">
                        <div class="notif-text">
                            <strong>{{ $sender }}</strong>
                            <span class="notif-action">{{ $action }}</span>
                            <strong>{{ $docName }}</strong>
                        </div>
                        <small style="color:var(--muted)">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                    @if($isUnread)
                    <span class="notif-dot"></span>
                    @endif
                </a>
                @empty
                <div class="notif-empty">
                    <i class="bi bi-inbox" style="font-size:2rem;"></i>
                    <div class="mt-2" data-i18n="no_notifications">Нет уведомлений</div>
                </div>
                @endforelse
            </div>
            <div class="notif-footer">
                <a href="{{ $notifRoute }}" data-i18n="all_notifications">Все уведомления →</a>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const btn = document.getElementById('notifBtn');
            const dropdown = document.getElementById('notifDropdown');
            const wrapper = document.getElementById('notifWrapper');
            const list = document.getElementById('notifList');
            const badge = document.getElementById('notifBadge');
            const ping = document.getElementById('notifPing');
            const headerBadge = document.getElementById('headerBadge');

            let lastKnownTimestamp = Math.max(...Array.from(document.querySelectorAll('.notif-item[data-ts]'))
                .map(el => parseInt(el.dataset.ts) || 0), 0);

            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('active');
            });
            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) dropdown.classList.remove('active');
            });
            list.addEventListener('click', function(e) {
                const item = e.target.closest('.notif-item.unread');
                if (!item) return;
                const id = item.dataset.id;
                const url = item.dataset.url;
                item.classList.remove('unread');
                const dot = item.querySelector('.notif-dot');
                if (dot) dot.remove();
                fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).catch(() => {});
                if (url && url !== '#') {
                    e.preventDefault();
                    setTimeout(() => { window.location.href = url; }, 150);
                }
            });

            function updateBadge(count) {
                if (count > 0) {
                    if (!document.getElementById('notifBadge')) {
                        const newBadge = document.createElement('span');
                        newBadge.id = 'notifBadge';
                        newBadge.className = 'notif-badge';
                        newBadge.textContent = count;
                        btn.appendChild(newBadge);
                    } else { document.getElementById('notifBadge').textContent = count; }
                    if (!document.getElementById('notifPing')) {
                        const newPing = document.createElement('span');
                        newPing.id = 'notifPing';
                        newPing.className = 'notif-ping-dot';
                        btn.appendChild(newPing);
                    }
                    headerBadge.textContent = count;
                    headerBadge.style.display = '';
                    btn.classList.add('bell-shaking');
                } else {
                    const b = document.getElementById('notifBadge');
                    const p = document.getElementById('notifPing');
                    if (b) b.remove();
                    if (p) p.remove();
                    headerBadge.style.display = 'none';
                    btn.classList.remove('bell-shaking');
                }
            }

            function renderNotification(n) {
                const isComment = n.type === 'comment' || /коммент/i.test(n.message || '');
                const action = isComment ? 'оставил комментарий к' : 'назначил вам документ';
                const iconClass = isComment ? 'bi-chat-left-text' : 'bi-pin-angle-fill';
                const iconColor = isComment ? '#2fb344' : '#f76707';
                const bgColor = isComment ? 'rgba(47,179,68,0.1)' : 'rgba(247,103,7,0.1)';
                const item = document.createElement('a');
                item.href = n.url || '#';
                item.className = 'notif-item unread fresh';
                item.dataset.id = n.id;
                item.dataset.url = n.url || '#';
                item.dataset.ts = n.createdAt;
                item.innerHTML = `
                    <div class="notif-icon" style="background:${bgColor};">
                        <i class="bi ${iconClass}" style="color:${iconColor};"></i>
                    </div>
                    <div class="notif-body">
                        <div class="notif-text">
                            <strong>${escapeHtml(n.sender)}</strong>
                            <span class="notif-action">${action}</span>
                            <strong>${escapeHtml(n.docName)}</strong>
                        </div>
                        <small style="color:var(--muted)">${escapeHtml(n.time)}</small>
                    </div>
                    <span class="notif-dot"></span>
                `;
                const empty = list.querySelector('.notif-empty');
                if (empty) empty.remove();
                list.insertBefore(item, list.firstChild);
                setTimeout(() => item.classList.remove('fresh'), 3000);
            }

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str || '';
                return div.innerHTML;
            }

            function ringBell() {
                btn.classList.remove('ringing');
                void btn.offsetWidth;
                btn.classList.add('ringing');
                setTimeout(() => btn.classList.remove('ringing'), 800);
            }

            async function checkNotifications() {
                try {
                    const res = await fetch('/notifications/check', {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    updateBadge(data.unreadCount);
                    const newOnes = (data.notifications || [])
                        .filter(n => n.createdAt > lastKnownTimestamp)
                        .sort((a, b) => a.createdAt - b.createdAt);
                    if (newOnes.length > 0) {
                        newOnes.forEach(n => renderNotification(n));
                        lastKnownTimestamp = Math.max(...newOnes.map(n => n.createdAt));
                        ringBell();
                        if (!dropdown.classList.contains('active')) {
                            dropdown.classList.add('active');
                            setTimeout(() => dropdown.classList.remove('active'), 5000);
                        }
                    }
                } catch (e) {}
            }
            setInterval(checkNotifications, 15000);
            setTimeout(checkNotifications, 5000);
        })();
    </script>

    <!-- PROFILE -->
    <div class="profile-dropdown" id="profileWrapper">
        <button type="button" class="profile-btn" id="profileBtn" aria-label="Профиль">
            @if(auth()->check() && auth()->user()->avatar)
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="profile-avatar">
            @else
            <span class="profile-initials">
                {{ auth()->check() ? Str::upper(Str::substr(auth()->user()->name, 0, 1)) . Str::upper(Str::substr(explode(' ', auth()->user()->name)[1] ?? '', 0, 1)) : 'U' }}
            </span>
            @endif
        </button>
        <div class="profile-menu" id="profileMenu">
            <a class="menu-item" href="{{ route('profile.show') }}">
                <i class="bi bi-person"></i><span data-i18n="profile">Профиль</span>
            </a>
            <hr class="menu-divider">
            <form method="POST" action="{{ route('logout') }}" id="logout-form">@csrf</form>
            <a class="menu-item logout" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-left"></i><span data-i18n="logout">Выйти</span>
            </a>
        </div>
    </div>
    <script>
        (function() {
            const btn = document.getElementById('profileBtn');
            const menu = document.getElementById('profileMenu');
            const wrapper = document.getElementById('profileWrapper');
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('active');
            });
            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) menu.classList.remove('active');
            });
        })();
    </script>
</header>

<!-- МОБИЛЬНЫЙ ПОИСК -->
<div class="mobile-search-bar" id="mobileSearchBar">
    <form action="{{ route('search') }}" method="GET" class="mobile-search-bar-form">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" name="query" value="{{ request('query') }}"
               data-i18n-placeholder="search_placeholder" placeholder="Поиск..." id="mobileSearchInput" />
        <button type="submit" style="display:none;"></button>
    </form>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="ambient" id="ambient"></div>

<div class="layout">
      @php
        $isDocs = request()->is('documents*');
        $hasDocTab = request()->has('tab') && in_array(request('tab'), ['incoming', 'outgoing', 'processing', 'draft', 'rejected', 'archive']);
        $sideActive = [
            'dashboard'     => request()->is('dashboard') || request()->is('/'),
            'documents'     => $isDocs && !$hasDocTab,
            'signatures'    => request()->is('signatures*'),
            'users'         => request()->is('users*'),
            'messages'      => request()->is('messages*'),
            'incoming'      => $isDocs && request('tab', 'incoming') == 'incoming',
            'outgoing'      => $isDocs && request('tab') == 'outgoing',
            'processing'    => $isDocs && request('tab') == 'processing',
            'draft'         => $isDocs && request('tab') == 'draft',
            'rejected'      => $isDocs && request('tab') == 'rejected',
            'archive'       => $isDocs && request('tab') == 'archive',
            'analysis'      => request()->is('analysis*'),
            'notifications' => request()->is('notifications*'),
            'logs'          => request()->is('logs*'),
            'contracts'     => request()->is('contracts*'),
            'departments'   => request()->is('departments*'),
            'companies'     => request()->routeIs('companies.*'),
            'profile'       => request()->is('profile*'),
            'reports'       => request()->routeIs('admin.reports'),
        ];
    @endphp

    <aside class="sidebar" id="sidebar">
        <!-- Поиск — только на мобильных -->
        <form action="{{ route('search') }}" method="GET" class="mobile-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="query" value="{{ request('query') }}"
                   data-i18n-placeholder="search_placeholder" placeholder="Поиск..." />
            <button type="submit" style="display:none;"></button>
        </form>

        <!-- Дубликат верхнего меню — только на мобильных -->
        <div class="mobile-nav-group">
            <a href="/dashboard" class="side-item {{ $sideActive['dashboard'] ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                <span data-i18n="dashboard">Обзор</span>
            </a>
            <a href="/documents" class="side-item {{ $sideActive['documents'] ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span data-i18n="documents">Документы</span>
            </a>
            <a href="/signatures" class="side-item {{ $sideActive['signatures'] ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
                <span data-i18n="signatures">Подписание</span>
            </a>
            <a href="/users" class="side-item {{ $sideActive['users'] ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span data-i18n="counterparties">Команда</span>
            </a>
        
        </div>

      <a href="javascript:void(0)" class="side-item sidebar-toggle-item" id="sidebarToggle"
   role="button" aria-label="Свернуть меню" title="Свернуть меню" aria-expanded="true" aria-controls="sidebar">
    <svg class="collapse-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m11 17-5-5 5-5"/><path d="m18 17-5-5 5-5"/>
    </svg>
    <svg class="expand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
        <path d="m13 17 5-5-5-5"/><path d="m6 17 5-5-5-5"/>
    </svg>
    <span class="collapse-label" data-i18n="collapse">Свернуть меню</span>
    <span class="expand-label" data-i18n="expand" style="display:none;">Развернуть меню</span>
</a>
        <a href="{{ route('documents.index', ['tab' => 'incoming']) }}" class="side-item {{ $sideActive['incoming'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
            <span data-i18n="incoming">Входящие</span>
        </a>
        <a href="{{ route('documents.index', ['tab' => 'outgoing']) }}" class="side-item {{ $sideActive['outgoing'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            <span data-i18n="outgoing">Исходящие</span>
        </a>
         
        <a href="{{ route('documents.index', ['tab' => 'processing']) }}" class="side-item {{ $sideActive['processing'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/></svg>
            <span data-i18n="on_signing">На подписании</span>
        </a>
        <a href="{{ route('documents.index', ['tab' => 'draft']) }}" class="side-item {{ $sideActive['draft'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            <span data-i18n="drafts">Черновики</span>
        </a>
        <a href="{{ route('documents.index', ['tab' => 'rejected']) }}" class="side-item {{ $sideActive['rejected'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <span data-i18n="rejected">Отклонённые</span>
        </a>
        <a href="{{ route('documents.index', ['tab' => 'archive']) }}" class="side-item {{ $sideActive['archive'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="21 8 21 21 3 21 3 8"/>
                <rect x="1" y="3" width="22" height="5"/>
                <line x1="10" y1="12" x2="14" y2="12"/>
            </svg>
            <span data-i18n="archive">Архив</span>
        </a>

        <div class="side-title" data-i18n="management">Управление</div>

        <a href="{{ route('telegram.chat') }}" class="side-item {{ ($sideActive['telegram'] ?? false) ? 'active' : '' }}" data-page="telegram">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5z"/></svg>
            <span data-i18n="chats">Чаты</span>
        </a>
        <a href="/analysis" class="side-item {{ $sideActive['analysis'] ? 'active' : '' }}" data-page="analysis">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
            <span data-i18n="analysis">Анализ</span>
        </a>
        <a href="{{ route('notifications.index') }}" class="side-item {{ $sideActive['notifications'] ? 'active' : '' }}" data-page="notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span data-i18n="notifications">Уведомления</span>
            @if(isset($unreadCount) && $unreadCount > 0)
            <span class="side-badge">{{ $unreadCount }}</span>
            @endif
        </a>
        <a href="/contracts/index" class="side-item {{ $sideActive['contracts'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="10" rx="2"/>
                <circle cx="12" cy="5" r="2"/>
                <path d="M12 7v4"/>
            </svg>
            <span data-i18n="ai-contracts">ИИ Договоры</span>
        </a>
        <a href="/departments" class="side-item {{ $sideActive['departments'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span data-i18n="departments">Отделы</span>
        </a>
        <a href="{{ route('companies.index') }}" class="side-item {{ $sideActive['companies'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 21h18"/>
                <path d="M5 21V7l8-4v18"/>
                <path d="M19 21V11l-6-4"/>
                <path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/>
            </svg>
            <span data-i18n="companies">Мои компании</span>
        </a>
        <a href="/logs" class="side-item {{ $sideActive['logs'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            <span data-i18n="history">Журнал действий</span>
        </a>
        <a href="/profile" class="side-item {{ $sideActive['profile'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span data-i18n="profile">Профиль</span>
        </a>
        @if(auth()->check() && auth()->user()->company_id && (auth()->user()->role === 'admin' || auth()->user()->level == 1))
        <a href="{{ route('admin.reports') }}" class="side-item {{ $sideActive['reports'] ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3v18h18"/>
                <path d="M7 14l3-3 4 4 5-6"/>
            </svg>
            <span data-i18n="reports">Ҳисобот / Отчёт</span>
        </a>
        @endif
        <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form" style="margin:0;">
            @csrf
            <a href="#" class="side-item logout-item" onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span data-i18n="logout">Выход</span>
            </a>
        </form>
    </aside>

    <!-- MAIN -->
    <main class="main">
        @yield('content')
    </main>
</div>

<script>
    // ===== БУРГЕР-МЕНЮ (мобильные) =====
    (function() {
        const burgerBtn = document.getElementById('burgerBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        function openMenu() {
            burgerBtn.classList.add('active');
            burgerBtn.setAttribute('aria-expanded', 'true');
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('menu-open');
            document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
            burgerBtn.classList.remove('active');
            burgerBtn.setAttribute('aria-expanded', 'false');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('menu-open');
            document.body.style.overflow = '';
        }
        burgerBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.contains('active') ? closeMenu() : openMenu();
        });
        overlay.addEventListener('click', closeMenu);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) closeMenu();
        });
        sidebar.querySelectorAll('.side-item').forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 1100) closeMenu();
            });
        });
        const mobileSearchForm = sidebar.querySelector('.mobile-search');
        if (mobileSearchForm) {
            mobileSearchForm.addEventListener('submit', function() {
                if (window.innerWidth <= 1100) closeMenu();
            });
        }
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1100) closeMenu();
        });
    })();

    // ===== СВОРАЧИВАНИЕ САЙДБАРА (кнопка теперь в топбаре, маленькая) =====
    (function() {
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        if (!toggleBtn || !sidebar) return;

        const storageKey = 'docsign_sidebar_collapsed';
        const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
        const initialLabel = isCollapsed ? 'Развернуть меню' : 'Свернуть меню';
        toggleBtn.setAttribute('aria-expanded', String(!isCollapsed));
        toggleBtn.setAttribute('aria-label', initialLabel);
        toggleBtn.setAttribute('title', initialLabel);

    function updateMenuHints(collapsed) {
    sidebar.querySelectorAll('.side-item').forEach(function(item) {
        // пропускаем саму кнопку сворачивания
        if (item.id === 'sidebarToggle') return;
        const label = item.textContent.trim();
        if (collapsed && label) {
            item.setAttribute('title', label);
        } else {
            item.removeAttribute('title');
        }
    });
}

        updateMenuHints(document.documentElement.classList.contains('sidebar-collapsed'));
  toggleBtn.addEventListener('click', function() {
    const collapsed = document.documentElement.classList.toggle('sidebar-collapsed');
    const label = collapsed ? 'Развернуть меню' : 'Свернуть меню';
    toggleBtn.setAttribute('aria-expanded', String(!collapsed));
    toggleBtn.setAttribute('aria-label', label);
    toggleBtn.setAttribute('title', label);
    updateMenuHints(collapsed);
    try {
        localStorage.setItem(storageKey, String(collapsed));
    } catch (error) {
        console.warn('Не удалось сохранить настройку бокового меню.', error);
    }
});
    })();

    // ===== МОБИЛЬНАЯ ЛУПА =====
    (function() {
        const btn = document.getElementById('mobileSearchBtn');
        const bar = document.getElementById('mobileSearchBar');
        const input = document.getElementById('mobileSearchInput');
        if (!btn || !bar) return;
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            bar.classList.toggle('active');
            if (bar.classList.contains('active')) setTimeout(() => { if (input) input.focus(); }, 60);
        });
        document.addEventListener('click', function(e) {
            if (bar.classList.contains('active') && !bar.contains(e.target) && !btn.contains(e.target)) bar.classList.remove('active');
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && bar.classList.contains('active')) bar.classList.remove('active');
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1100) bar.classList.remove('active');
        });
    })();

    // ===== AMBIENT LIGHTING =====
    const AMBIENT_KEY = 'docsign_ambient';
    const COLORS = [
        { nameKey:'color_electric', rgb:'79,140,255',  hex:'#4f8cff' },
        { nameKey:'color_gold',     rgb:'234,179,8',   hex:'#eab308' },
        { nameKey:'color_sunset',   rgb:'249,115,22',  hex:'#f97316' },
        { nameKey:'color_cardinal', rgb:'239,68,68',   hex:'#ef4444' },
        { nameKey:'color_fuchsia',  rgb:'236,72,153',  hex:'#ec4899' },
        { nameKey:'color_rose',     rgb:'244,114,182', hex:'#f472b6' },
        { nameKey:'color_neon',     rgb:'168,85,247',  hex:'#a855f7' },
        { nameKey:'color_indigo',   rgb:'99,102,241',  hex:'#6366f1' },
        { nameKey:'color_aqua',     rgb:'6,182,212',   hex:'#06b6d4' },
        { nameKey:'color_mint',     rgb:'45,212,191',  hex:'#2dd4bf' },
        { nameKey:'color_lime',     rgb:'34,197,94',   hex:'#22c55e' },
        { nameKey:'color_ice',      rgb:'226,232,240', hex:'#e2e8f0' },
    ];
    function loadAmbient(){
        try {
            const s = JSON.parse(localStorage.getItem(AMBIENT_KEY));
            if (s && s.color) return s;
        } catch(e) {}
        return { color:'79,140,255', intensity:80, spread:60, mode:'solid' };
    }
    function saveAmbient(settings){ localStorage.setItem(AMBIENT_KEY, JSON.stringify(settings)); }
    function applyColor(rgb){
        document.documentElement.style.setProperty('--glow', rgb);
        const preview = document.getElementById('ambPreview');
        if (preview) {
            preview.style.background = `linear-gradient(90deg, rgba(${rgb},0.1), rgba(${rgb},0.6), rgba(${rgb},0.1))`;
            preview.style.borderColor = `rgba(${rgb},0.4)`;
        }
        const label = document.getElementById('ambPreviewLabel');
        if (label) label.textContent = `RGB(${rgb})`;
    }
    function applyMode(mode){
        const ambient = document.getElementById('ambient');
        if (!ambient) return;
        ambient.style.animation = 'none';
        void ambient.offsetWidth;
        if (mode === 'pulse') ambient.style.animation = 'pulseFast 1.5s ease-in-out infinite';
        else if (mode === 'breathe') ambient.style.animation = 'breatheGlow 4s ease-in-out infinite';
        else ambient.style.animation = 'pulseGlow 4s ease-in-out infinite';
    }
    function applyIntensity(val){
        const ambient = document.getElementById('ambient');
        if (ambient) ambient.style.opacity = val / 100;
    }
    function applySpread(val){
        const ambient = document.getElementById('ambient');
        if (!ambient) return;
        const scale = val / 60;
        ambient.style.transform = `scaleY(${scale})`;
        ambient.style.filter = `blur(${Math.max(0,(val-60)/20)}px)`;
    }
    let saveTimer = null;
    function flashSaved(){
        const ind = document.getElementById('savedIndicator');
        if (!ind) return;
        ind.classList.add('show');
        clearTimeout(saveTimer);
        saveTimer = setTimeout(()=> ind.classList.remove('show'), 1500);
    }
    document.addEventListener('DOMContentLoaded', () => {
        const settings = loadAmbient();
        const colorsEl = document.getElementById('colors');
        if (colorsEl) {
            COLORS.forEach((c)=>{
                const el = document.createElement('div');
                el.className = 'color' + (c.rgb === settings.color ? ' active' : '');
                el.style.background = c.hex;
                el.style.color = `rgb(${c.rgb})`;
                el.dataset.rgb = c.rgb;
                el.dataset.nameKey = c.nameKey;
                el.title = c.nameKey;
                el.addEventListener('click', ()=>{
                    document.querySelectorAll('.color').forEach(x=>x.classList.remove('active'));
                    el.classList.add('active');
                    const cur = loadAmbient();
                    cur.color = c.rgb;
                    saveAmbient(cur);
                    applyColor(c.rgb);
                    flashSaved();
                });
                colorsEl.appendChild(el);
            });
        }
        const intInput = document.getElementById('intensity');
        const intVal = document.getElementById('intVal');
        if (intInput) intInput.value = settings.intensity;
        if (intVal) intVal.textContent = settings.intensity + '%';
        const spreadInput = document.getElementById('spread');
        const spreadVal = document.getElementById('spreadVal');
        if (spreadInput) spreadInput.value = settings.spread;
        if (spreadVal) spreadVal.textContent = settings.spread + '%';
        document.querySelectorAll('.mode').forEach(m=> m.classList.toggle('active', m.dataset.mode === settings.mode));
        applyColor(settings.color);
        applyIntensity(settings.intensity);
        applySpread(settings.spread);
        applyMode(settings.mode);
        const ambBtn = document.getElementById('ambBtn');
        const ambPanel = document.getElementById('ambPanel');
        const ambWrapper = document.getElementById('ambWrapper');
        if (ambBtn && ambPanel) {
            ambBtn.addEventListener('click', (e)=>{
                e.stopPropagation();
                ambPanel.classList.toggle('open');
                ambBtn.classList.toggle('open');
            });
            document.addEventListener('click', (e)=>{
                if (!ambWrapper.contains(e.target)) {
                    ambPanel.classList.remove('open');
                    ambBtn.classList.remove('open');
                }
            });
        }
        if (intInput) intInput.addEventListener('input', (e)=>{
            const v = parseInt(e.target.value);
            if (intVal) intVal.textContent = v + '%';
            applyIntensity(v);
            const cur = loadAmbient(); cur.intensity = v; saveAmbient(cur); flashSaved();
        });
        if (spreadInput) spreadInput.addEventListener('input', (e)=>{
            const v = parseInt(e.target.value);
            if (spreadVal) spreadVal.textContent = v + '%';
            applySpread(v);
            const cur = loadAmbient(); cur.spread = v; saveAmbient(cur); flashSaved();
        });
        document.querySelectorAll('.mode').forEach(m=>{
            m.addEventListener('click', ()=>{
                document.querySelectorAll('.mode').forEach(x=>x.classList.remove('active'));
                m.classList.add('active');
                const cur = loadAmbient(); cur.mode = m.dataset.mode; saveAmbient(cur); applyMode(cur.mode); flashSaved();
            });
        });
    });

    // ===== ПЕРЕВОДЫ =====
    const TRANSLATIONS = {
        ru: {
            brand_sub: 'ЭДО', dashboard: 'Обзор', documents: 'Документы', signatures: 'Подписание',
            counterparties: 'Команда', reports: 'Отчёты', search_placeholder: 'Поиск...',
            choose_language: 'Выберите язык', notifications: 'Уведомления',
            all_notifications: 'Все уведомления →', no_notifications: 'Нет уведомлений',
            notif_action_comment: 'оставил комментарий к', notif_action_assign: 'назначил вам документ',
            system: 'Система', document: 'Документ', profile: 'Профиль', logout: 'Выйти',
            workspace: 'Рабочее пространство', control_panel: 'Панель управления',
            incoming: 'Входящие', outgoing: 'Исходящие', on_signing: 'На подписании',
            archive: 'Архив', management: 'Управление', analysis: 'Анализ', history: 'Журнал действий', exit: 'Выход',
            ambient_title: 'Ambient Lighting', ambient_desc: 'Настройте подсветку как в премиальном авто',
            intensity: 'Интенсивность', spread: 'Распространение',
            mode_solid: 'Статичный', mode_pulse: 'Пульс', mode_breathe: 'Дыхание',
            color_title: 'Цвет', color_electric: 'Электрик', color_neon: 'Неон', color_cardinal: 'Кардинал',
            color_sunset: 'Закат', color_lime: 'Лайм', color_aqua: 'Аква', color_fuchsia: 'Фуксия',
            color_gold: 'Золото', color_ice: 'Лёд', color_mint: 'Мята', color_indigo: 'Индиго', color_rose: 'Роза',
            auto_save: 'Автосохранение', saved: 'Сохранено',
            status_signed: 'Подписан', status_pending: 'Ожидает', status_rejected: 'Отклонён', status_draft: 'Черновик',
            departments: 'Отделы', companies: 'Мои компании', drafts: 'Черновики', rejected: 'Отклонённые',
            chats: 'Чаты',
            collapse: 'Свернуть меню',
expand: 'Развернуть меню',
        },
        tj: {
            brand_sub: 'Ҳуҷҷатгардонӣ', dashboard: 'Панел', documents: 'Ҳуҷҷатҳо', signatures: 'Имзоҳо',
            counterparties: 'Даста', reports: 'Ҳисоботҳо', search_placeholder: 'Ҷустуҷӯ...',
            choose_language: 'Забонро интихоб кунед', notifications: 'Огоҳиҳо',
            all_notifications: 'Ҳамаи огоҳиҳо →', no_notifications: 'Огоҳиҳо нест',
            notif_action_comment: 'шарҳ гузошт ба', notif_action_assign: 'ба шумо ҳуҷҷат таъин кард',
            system: 'Система', document: 'Ҳуҷҷат', profile: 'Профил', logout: 'Баромад',
            workspace: 'Фазои корӣ', control_panel: 'Панели идоракунӣ',
            incoming: 'Воридшаванда', outgoing: 'Содиршаванда', on_signing: 'Дар раванди имзо',
            archive: 'Бойгонӣ', management: 'Идоракунӣ', analysis: 'Таҳлил', history: 'Таърих', exit: 'Баромад',
            ambient_title: 'Равшании муҳит', ambient_desc: 'Равшаниро мисли мошини премиум танзим кунед',
            intensity: 'Шиддат', spread: 'Паҳншавӣ',
            mode_solid: 'Собит', mode_pulse: 'Пулс', mode_breathe: 'Нафас',
            color_title: 'Ранг', color_electric: 'Электрик', color_neon: 'Неон', color_cardinal: 'Кардинал',
            color_sunset: 'Ғуруб', color_lime: 'Лайм', color_aqua: 'Аква', color_fuchsia: 'Фуксия',
            color_gold: 'Тилло', color_ice: 'Ях', color_mint: 'Наъно', color_indigo: 'Индиго', color_rose: 'Гулӣ',
            auto_save: 'Нигоҳдории худкор', saved: 'Нигоҳ дошта шуд',
            status_signed: 'Имзошуда', status_pending: 'Дар интизорӣ', status_rejected: 'Радшуда', status_draft: 'Лоиҳа',
            departments: 'Шӯъбаҳо', companies: 'Ширкатҳои ман', drafts: 'Пешнависҳо', rejected: 'Радшудаҳо',
            chats: 'Чатҳо',
            collapse: 'Печонидани меню',
expand: 'Кушодани меню',
        },
        en: {
            collapse: 'Collapse menu',
expand: 'Expand menu',
            brand_sub: 'EDMS', dashboard: 'Dashboard', documents: 'Documents', signatures: 'Signatures',
            counterparties: 'Team', reports: 'Reports', search_placeholder: 'Search...',
            choose_language: 'Choose language', notifications: 'Notifications',
            all_notifications: 'All notifications →', no_notifications: 'No notifications',
            notif_action_comment: 'left a comment on', notif_action_assign: 'assigned you a document',
            system: 'System', document: 'Document', profile: 'Profile', logout: 'Logout',
            workspace: 'Workspace', control_panel: 'Control Panel',
            incoming: 'Incoming', outgoing: 'Outgoing', on_signing: 'Awaiting Signature',
            archive: 'Archive', management: 'Management', analysis: 'Analysis', history: 'Activity Log', exit: 'Exit',
            ambient_title: 'Ambient Lighting', ambient_desc: 'Set up lighting like in a premium car',
            intensity: 'Intensity', spread: 'Spread',
            mode_solid: 'Static', mode_pulse: 'Pulse', mode_breathe: 'Breathe',
            color_title: 'Color', color_electric: 'Electric', color_neon: 'Neon', color_cardinal: 'Cardinal',
            color_sunset: 'Sunset', color_lime: 'Lime', color_aqua: 'Aqua', color_fuchsia: 'Fuchsia',
            color_gold: 'Gold', color_ice: 'Ice', color_mint: 'Mint', color_indigo: 'Indigo', color_rose: 'Rose',
            auto_save: 'Auto-save', saved: 'Saved',
            status_signed: 'Signed', status_pending: 'Pending', status_rejected: 'Rejected', status_draft: 'Draft',
            departments: 'Departments', companies: 'My Companies', drafts: 'Drafts', rejected: 'Rejected',
            chats: 'Chats',
        }
    };
      const LANG_META = {
        tj: { name: 'Тоҷикӣ', html: 'tj' },
        ru: { name: 'Русский', html: 'ru' },
        en: { name: 'English', html: 'en' }
    };
    const FLAG_SVG = {
        ru: '<svg viewBox="0 0 24 18"><rect width="24" height="6" fill="#ffffff"/><rect y="6" width="24" height="6" fill="#0039a6"/><rect y="12" width="24" height="6" fill="#d52b1e"/></svg>',
        tj: '<svg viewBox="0 0 24 18"><rect width="24" height="18" fill="#ffffff"/><rect width="24" height="5" fill="#cc0000"/><rect y="13" width="24" height="5" fill="#006600"/><circle cx="12" cy="9" r="2.2" fill="#f8c300"/></svg>',
        en: '<svg viewBox="0 0 24 18"><rect width="24" height="18" fill="#012169"/><path d="M0 0l24 18M24 0L0 18" stroke="#fff" stroke-width="3"/><path d="M12 0v18M0 9h24" stroke="#fff" stroke-width="5"/><path d="M12 0v18M0 9h24" stroke="#c8102e" stroke-width="3"/></svg>'
    };
    let currentLang = localStorage.getItem('docsign_lang') || 'ru';
    function applyLanguage(lang) {
        if (!TRANSLATIONS[lang]) lang = 'ru';
        currentLang = lang;
        localStorage.setItem('docsign_lang', lang);
        const dict = TRANSLATIONS[lang];
        const meta = LANG_META[lang];
        document.documentElement.lang = meta.html;
        const flagEl = document.getElementById('langFlag');
        const nameEl = document.getElementById('langName');
              if (flagEl) flagEl.innerHTML = FLAG_SVG[lang] || FLAG_SVG.ru;
               if (nameEl) nameEl.textContent = meta.name;
        document.querySelectorAll('.lang-option').forEach(opt => opt.classList.toggle('active', opt.dataset.lang === lang));
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (dict[key] !== undefined) el.textContent = dict[key];
        });
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            if (dict[key] !== undefined) el.setAttribute('placeholder', dict[key]);
        });
        document.querySelectorAll('[data-i18n-title]').forEach(el => {
            const key = el.getAttribute('data-i18n-title');
            if (dict[key] !== undefined) el.setAttribute('title', dict[key]);
        });
        document.querySelectorAll('.notif-action').forEach(el => {
            const action = el.dataset.action;
            if (action === 'comment' && dict.notif_action_comment) el.textContent = dict.notif_action_comment;
            else if (action === 'assign' && dict.notif_action_assign) el.textContent = dict.notif_action_assign;
        });
        document.querySelectorAll('.color').forEach(el => {
            const key = el.dataset.nameKey;
            if (key && dict[key]) el.title = dict[key];
        });
        window.dispatchEvent(new CustomEvent('docsign:lang-changed', { detail: { lang, dict } }));
    }
    document.addEventListener('DOMContentLoaded', () => {
        const langBtn = document.getElementById('langBtn');
        const langMenu = document.getElementById('langMenu');
        const langSwitcher = document.getElementById('langSwitcher');
        if (langBtn) {
            langBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = langMenu.classList.toggle('open');
                langBtn.classList.toggle('open', isOpen);
            });
        }
        document.querySelectorAll('.lang-option').forEach(opt => {
            opt.addEventListener('click', function(e) {
                e.stopPropagation();
                applyLanguage(this.dataset.lang);
                langMenu.classList.remove('open');
                langBtn.classList.remove('open');
            });
        });
        document.addEventListener('click', function(e) {
            if (langSwitcher && !langSwitcher.contains(e.target)) {
                langMenu.classList.remove('open');
                langBtn.classList.remove('open');
            }
        });
        applyLanguage(currentLang);
        document.querySelectorAll('#nav button').forEach(b=>{
            b.addEventListener('click',()=>{
                document.querySelectorAll('#nav button').forEach(x=>x.classList.remove('active'));
                b.classList.add('active');
            });
        });
        document.querySelectorAll('.tabs button').forEach(b=>{
            b.addEventListener('click',()=>{
                b.parentElement.querySelectorAll('button').forEach(x=>x.classList.remove('active'));
                b.classList.add('active');
            });
        });
    });
</script>

@if(auth()->check())
<script>
    // ===== ТЕМА: ОДНА КНОПКА =====
    (function () {
        const root = document.documentElement;
        const toggleBtn = document.getElementById('themeToggleBtn');
        if (!toggleBtn) return;
        function applyTheme(theme) {
            root.dataset.theme = theme;
            localStorage.setItem('docsign_theme', theme);
            const hint = (theme === 'dark') ? 'Светлая тема' : 'Тёмная тема';
            toggleBtn.title = hint;
            toggleBtn.setAttribute('aria-label', hint);
        }
        applyTheme(root.dataset.theme || 'light');
        toggleBtn.addEventListener('click', function () {
            applyTheme(root.dataset.theme === 'dark' ? 'light' : 'dark');
        });
    })();
</script>
@endif

</body>
</html>
