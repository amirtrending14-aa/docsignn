<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>DocSign — Электронный документооборот</title>

    {{-- ✅ ДОБАВЛЕНО: Favicon DocSign --}}
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
            } catch(e) {}
        })();
    </script>

    <style>
        :root{
          --bg-0:#06070b;
          --bg-1:#0b0d14;
          --bg-2:#10131c;
          --bg-3:#161a26;
          --line:rgba(255,255,255,0.06);
          --text:#e7ecf3;
          --muted:#8892a6;
          --accent:#4f8cff;
          --glow: 79,140,255;
          --glow-soft: rgba(var(--glow),0.18);
          --radius:14px;
          --glow-intensity: 80;
          --glow-spread: 60;
        }
        *{box-sizing:border-box}
        html,body{margin:0;padding:0;height:100%}
        body{
          font-family:'Inter',system-ui,sans-serif;
          background:
            radial-gradient(1200px 600px at 80% -10%, rgba(var(--glow),0.18), transparent 60%),
            radial-gradient(900px 500px at -10% 110%, rgba(var(--glow),0.12), transparent 60%),
            var(--bg-0);
          color:var(--text);
          min-height:100vh;
          overflow-x:hidden;
          transition: background 0.8s ease;
        }
        body::before{
          content:"";
          position:fixed; inset:0;
          background-image:
            linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
          background-size: 42px 42px;
          mask-image: radial-gradient(ellipse at 50% 30%, black 40%, transparent 80%);
          pointer-events:none;
          z-index:0;
        }

        /* ===== AMBIENT LIGHT STRIP ===== */
        .ambient{
          position:fixed;
          top:64px; left:0; right:0;
          height:2px;
          background: linear-gradient(90deg,
            transparent 0%,
            rgba(var(--glow),0.0) 5%,
            rgba(var(--glow),0.9) 30%,
            rgba(var(--glow),1) 50%,
            rgba(var(--glow),0.9) 70%,
            rgba(var(--glow),0.0) 95%,
            transparent 100%);
          box-shadow:
            0 0 10px rgba(var(--glow),0.8),
            0 0 24px rgba(var(--glow),0.6),
            0 0 60px rgba(var(--glow),0.35),
            0 0 120px rgba(var(--glow),0.2);
          z-index:50;
          animation: pulseGlow 4s ease-in-out infinite;
          transition: all 0.4s ease;
        }
        .ambient::after{
          content:"";
          position:absolute;
          top:2px; left:0; right:0;
          height:80px;
          background: linear-gradient(180deg, rgba(var(--glow),0.25), transparent);
          filter: blur(10px);
          pointer-events:none;
        }
        @keyframes pulseGlow{
          0%,100%{ opacity:0.95 }
          50%{ opacity:1 }
        }
        @keyframes breatheGlow{
          0%,100%{ opacity:0.4; transform:scaleY(0.8) }
          50%{ opacity:1; transform:scaleY(1.2) }
        }
        @keyframes pulseFast{
          0%,100%{ opacity:0.6 }
          50%{ opacity:1 }
        }

        /* ===== TOP BAR ===== */
        .topbar{
          position:sticky; top:0; z-index:40;
          display:flex; align-items:center; gap:16px;
          padding:12px 22px;
          background: linear-gradient(180deg, rgba(10,12,18,0.85), rgba(10,12,18,0.55));
          backdrop-filter: blur(14px);
          -webkit-backdrop-filter: blur(14px);
          border-bottom:1px solid var(--line);
        }

        /* ===== БУРГЕР-МЕНЮ (только для мобильных) ===== */
        .burger-btn{
          display:none;
          width:38px; height:38px;
          border-radius:10px;
          background: rgba(255,255,255,0.04);
          border:1px solid var(--line);
          cursor:pointer;
          position:relative;
          transition:all .25s ease;
          flex-shrink:0;
        }
        .burger-btn:hover{
          border-color:rgba(var(--glow),0.4);
          box-shadow:0 0 14px rgba(var(--glow),0.25);
        }
        .burger-btn span{
          position:absolute;
          left:50%;
          width:20px;
          height:2px;
          background:var(--text);
          border-radius:2px;
          transform:translateX(-50%);
          transition:all .3s ease;
        }
        .burger-btn span:nth-child(1){ top:12px; }
        .burger-btn span:nth-child(2){ top:18px; }
        .burger-btn span:nth-child(3){ top:24px; }
        .burger-btn.active span:nth-child(1){
          top:18px;
          transform:translateX(-50%) rotate(45deg);
        }
        .burger-btn.active span:nth-child(2){
          opacity:0;
        }
        .burger-btn.active span:nth-child(3){
          top:18px;
          transform:translateX(-50%) rotate(-45deg);
        }

        /* ===== OVERLAY (затемнение при открытом меню) ===== */
        /* ВАЖНО: оверлей затемняет ТОЛЬКО область справа от сайдбара (left = ширина сайдбара),
           поэтому он физически не может перекрывать кнопки в меню и блокировать клики */
        .sidebar-overlay{
          display:none;
          position:fixed;
          top:0; right:0; bottom:0;
          left:280px;
          background:rgba(0,0,0,0.6);
          backdrop-filter:blur(4px);
          z-index:999;
          opacity:0;
          transition:opacity .3s ease;
          pointer-events:none;
        }
        .sidebar-overlay.active{
          display:block;
          opacity:1;
          pointer-events:auto;
        }

        .brand{
          display:flex; align-items:center; gap:10px;
          font-weight:700; letter-spacing:1px; font-size:15px;
        }
        .brand-dot{
          width:32px; height:32px; border-radius:9px;
          background: linear-gradient(135deg, rgba(var(--glow),0.95), rgba(var(--glow),0.4));
          box-shadow: 0 0 18px rgba(var(--glow),0.6), inset 0 0 10px rgba(255,255,255,0.2);
          display:grid; place-items:center;
          transition: all .6s ease;
          position:relative;
        }
        .brand-dot svg{width:18px;height:18px;color:#0a0d14}
        .brand small{
          display:block; font-size:9px; letter-spacing:3px;
          color:var(--muted); font-weight:500; margin-top:1px;
        }
        .nav{
          display:flex; gap:4px; margin-left:18px;
          background: rgba(255,255,255,0.03);
          border:1px solid var(--line);
          padding:4px; border-radius:12px;
        }
        .nav button{
          appearance:none; border:0; background:transparent;
          color:var(--muted); font:600 13px 'Inter',sans-serif;
          padding:8px 14px; border-radius:9px; cursor:pointer;
          display:flex; align-items:center; gap:8px;
          transition: all .25s ease;
        }
        .nav button svg{width:15px;height:15px}
        .nav button:hover{color:var(--text); background:rgba(255,255,255,0.04)}
        .nav button.active{
          color:#fff;
          background: linear-gradient(180deg, rgba(var(--glow),0.25), rgba(var(--glow),0.08));
          box-shadow: inset 0 0 0 1px rgba(var(--glow),0.35), 0 0 18px rgba(var(--glow),0.25);
        }
        .spacer{flex:1}
        .search{
          display:flex; align-items:center; gap:8px;
          background: rgba(255,255,255,0.04);
          border:1px solid var(--line);
          padding:8px 12px; border-radius:10px;
          min-width:260px;
        }
        .search input{
          background:transparent; border:0; outline:0; color:var(--text);
          font:13px 'Inter',sans-serif; width:100%;
        }
        .search svg{width:14px;height:14px;color:var(--muted)}
        .search kbd{
          font-family:'JetBrains Mono',monospace; font-size:10px;
          padding:2px 6px; border-radius:5px;
          background:rgba(255,255,255,0.06); color:var(--muted);
          border:1px solid var(--line);
        }
        .icon-btn{
          width:38px; height:38px; border-radius:10px;
          background: rgba(255,255,255,0.04);
          border:1px solid var(--line);
          display:grid; place-items:center; cursor:pointer;
          color:var(--muted); transition:all .25s ease;
          position:relative;
        }
        .icon-btn:hover{color:var(--text); border-color:rgba(var(--glow),0.4); box-shadow:0 0 14px rgba(var(--glow),0.25)}
        .icon-btn svg{width:16px;height:16px}
        .icon-btn .dot{
          position:absolute; top:8px; right:9px;
          width:7px; height:7px; border-radius:50%;
          background: rgba(var(--glow),1);
          box-shadow: 0 0 8px rgba(var(--glow),0.9);
        }
        .avatar{
          width:38px; height:38px; border-radius:10px;
          background: linear-gradient(135deg, rgba(var(--glow),0.5), rgba(var(--glow),0.15));
          border:1px solid rgba(var(--glow),0.4);
          display:grid; place-items:center; font-weight:700; font-size:13px;
          cursor:pointer;
          box-shadow: 0 0 14px rgba(var(--glow),0.25);
          transition: all .6s ease;
        }

        /* ===== LANGUAGE SWITCHER ===== */
        .lang-switcher{ position:relative; }
        .lang-btn{
          display:flex; align-items:center; gap:8px;
          padding:8px 12px;
          background: rgba(255,255,255,0.04);
          border:1px solid var(--line);
          border-radius:10px;
          color:var(--text);
          font:600 12px 'Inter',sans-serif;
          cursor:pointer;
          transition:all .25s ease;
          min-width:90px;
          justify-content:space-between;
        }
        .lang-btn:hover{
          border-color:rgba(var(--glow),0.4);
          box-shadow:0 0 14px rgba(var(--glow),0.2);
          color:#fff;
        }
        .lang-btn .flag{ font-size:16px; line-height:1; }
        .lang-btn .arrow{
          width:12px; height:12px;
          transition: transform .25s ease;
        }
        .lang-btn.open .arrow{ transform: rotate(180deg); }
        .lang-menu{
          position:absolute;
          top:calc(100% + 8px);
          right:0;
          min-width:180px;
          background: linear-gradient(180deg, rgba(18,21,30,0.98), rgba(13,15,22,0.98));
          backdrop-filter: blur(20px);
          border:1px solid var(--line);
          border-radius:12px;
          padding:6px;
          box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 30px rgba(var(--glow),0.15);
          opacity:0;
          visibility:hidden;
          transform: translateY(-8px);
          transition: all .25s cubic-bezier(.2,.9,.3,1.2);
          z-index:1000;
        }
        .lang-menu.open{
          opacity:1;
          visibility:visible;
          transform: translateY(0);
        }
        .lang-menu-title{
          font-size:10px;
          letter-spacing:1.5px;
          color:var(--muted);
          text-transform:uppercase;
          padding:8px 10px 6px;
          font-weight:600;
        }
        .lang-option{
          display:flex; align-items:center; gap:10px;
          padding:9px 10px;
          border-radius:8px;
          cursor:pointer;
          color:var(--text);
          font-size:13px;
          font-weight:500;
          transition: all .2s ease;
          border:1px solid transparent;
        }
        .lang-option:hover{
          background:rgba(255,255,255,0.05);
          color:#fff;
        }
        .lang-option.active{
          background: linear-gradient(90deg, rgba(var(--glow),0.2), transparent);
          border-color: rgba(var(--glow),0.3);
          color:#fff;
          box-shadow: inset 0 0 0 1px rgba(var(--glow),0.25);
        }
        .lang-option .flag{ font-size:18px; line-height:1; }
        .lang-option .name{ flex:1; }
        .lang-option .check{
          width:14px; height:14px;
          color:rgba(var(--glow),1);
          opacity:0;
          transition: opacity .2s ease;
        }
        .lang-option.active .check{ opacity:1; }

        /* ===== AMBIENT BUTTON IN TOPBAR ===== */
        .amb-btn{
          position:relative;
          display:flex; align-items:center; gap:8px;
          padding:8px 14px;
          background: linear-gradient(135deg, rgba(var(--glow),0.15), rgba(var(--glow),0.05));
          border:1px solid rgba(var(--glow),0.35);
          border-radius:10px;
          color:rgba(var(--glow),1);
          font:600 12px 'Inter',sans-serif;
          cursor:pointer;
          transition:all .3s ease;
          overflow:hidden;
        }
        .amb-btn::before{
          content:"";
          position:absolute; inset:0;
          background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
          transform: translateX(-100%);
          transition: transform 0.6s ease;
        }
        .amb-btn:hover::before{ transform: translateX(100%); }
        .amb-btn:hover{
          border-color:rgba(var(--glow),0.7);
          box-shadow: 0 0 20px rgba(var(--glow),0.4), inset 0 0 12px rgba(var(--glow),0.15);
          transform: translateY(-1px);
        }
        .amb-btn svg{ width:16px; height:16px; }
        .amb-btn .amb-swatch{
          width:14px; height:14px; border-radius:50%;
          background: rgb(var(--glow));
          box-shadow: 0 0 10px rgba(var(--glow),0.9), inset 0 0 4px rgba(255,255,255,0.4);
          animation: swatchPulse 2s ease-in-out infinite;
        }
        @keyframes swatchPulse{
          0%,100%{ box-shadow: 0 0 8px rgba(var(--glow),0.7), inset 0 0 4px rgba(255,255,255,0.4); }
          50%{ box-shadow: 0 0 16px rgba(var(--glow),1), inset 0 0 6px rgba(255,255,255,0.6); }
        }

        /* ===== AMBIENT PANEL (DROPDOWN FROM TOPBAR) ===== */
        .amb-panel{
          position:absolute;
          top:calc(100% + 12px);
          right:0;
          width:340px;
          background: linear-gradient(180deg, rgba(18,21,30,0.98), rgba(13,15,22,0.98));
          backdrop-filter: blur(24px);
          -webkit-backdrop-filter: blur(24px);
          border:1px solid var(--line);
          border-radius:18px;
          padding:20px;
          box-shadow: 0 30px 80px rgba(0,0,0,0.6), 0 0 50px rgba(var(--glow),0.18);
          opacity:0;
          visibility:hidden;
          transform: translateY(-10px) scale(0.96);
          transform-origin: top right;
          transition: all .3s cubic-bezier(.2,.9,.3,1.2);
          z-index:1000;
        }
        .amb-panel::before{
          content:"";
          position:absolute;
          top:-6px; right:30px;
          width:12px; height:12px;
          background: rgba(18,21,30,0.98);
          border-left:1px solid var(--line);
          border-top:1px solid var(--line);
          transform: rotate(45deg);
        }
        .amb-panel.open{
          opacity:1;
          visibility:visible;
          transform: translateY(0) scale(1);
        }
        .amb-panel-head{
          display:flex; align-items:center; justify-content:space-between;
          margin-bottom:4px;
        }
        .amb-panel h4{
          margin:0; font-size:15px; font-weight:700;
          display:flex; align-items:center; gap:8px;
        }
        .amb-panel h4 .live-dot{
          width:8px; height:8px; border-radius:50%;
          background: rgb(var(--glow));
          box-shadow: 0 0 10px rgba(var(--glow),1);
          animation: swatchPulse 1.5s ease-in-out infinite;
        }
        .amb-panel p{
          margin:0 0 16px; font-size:11px; color:var(--muted);
        }
        .amb-preview{
          height:70px; border-radius:12px;
          background: linear-gradient(90deg, rgba(var(--glow),0.1), rgba(var(--glow),0.6), rgba(var(--glow),0.1));
          border:1px solid rgba(var(--glow),0.3);
          box-shadow: inset 0 0 30px rgba(var(--glow),0.3), 0 0 20px rgba(var(--glow),0.2);
          margin-bottom:16px;
          position:relative; overflow:hidden;
          transition: all .4s ease;
        }
        .amb-preview::before{
          content:""; position:absolute; inset:0;
          background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
          animation: sweep 2.5s ease-in-out infinite;
        }
        @keyframes sweep{
          0%{transform:translateX(-100%)}
          100%{transform:translateX(100%)}
        }
        .amb-preview-label{
          position:absolute;
          bottom:8px; left:12px;
          font-size:10px;
          font-family:'JetBrains Mono',monospace;
          color:rgba(255,255,255,0.85);
          letter-spacing:1px;
          text-shadow: 0 1px 4px rgba(0,0,0,0.5);
          z-index:2;
        }
        .colors{
          display:flex;
          flex-wrap:wrap;
          gap:8px;
          margin-bottom:16px;
        }
        .color{
          width:38px;
          height:38px;
          flex:0 0 auto;
          border-radius:10px; cursor:pointer;
          border:2px solid transparent;
          position:relative;
          transition: all .25s cubic-bezier(.2,.9,.3,1.2);
          box-shadow: 0 4px 14px rgba(0,0,0,0.3);
        }
        .color:hover{
          transform:translateY(-3px) scale(1.08);
          box-shadow: 0 8px 20px currentColor;
        }
        .color.active{
          border-color:#fff;
          box-shadow: 0 0 20px currentColor, 0 0 0 2px rgba(255,255,255,0.2);
          transform: scale(1.05);
        }
        .color.active::after{
          content:"✓"; position:absolute; inset:0;
          display:grid; place-items:center; color:#fff; font-weight:700;
          text-shadow:0 1px 3px rgba(0,0,0,0.7);
          font-size:14px;
        }
        .intensity{display:flex; flex-direction:column; gap:6px}
        .intensity label{
          font-size:11px; color:var(--muted);
          display:flex; justify-content:space-between;
          font-weight:500;
        }
        .intensity label span:last-child{
          color:rgba(var(--glow),1);
          font-family:'JetBrains Mono',monospace;
          font-weight:700;
        }
        .intensity input[type=range]{
          -webkit-appearance:none; appearance:none;
          width:100%; height:6px; border-radius:3px;
          background: linear-gradient(90deg, rgba(var(--glow),0.3), rgba(var(--glow),1));
          outline:none;
          transition: all .3s ease;
        }
        .intensity input[type=range]::-webkit-slider-thumb{
          -webkit-appearance:none; appearance:none;
          width:18px; height:18px; border-radius:50%;
          background:#fff; cursor:pointer;
          box-shadow: 0 0 12px rgba(var(--glow),0.9), 0 2px 8px rgba(0,0,0,0.4);
          border:2px solid rgba(var(--glow),0.7);
          transition: all .2s ease;
        }
        .intensity input[type=range]::-webkit-slider-thumb:hover{
          transform: scale(1.2);
        }
        .intensity input[type=range]::-moz-range-thumb{
          width:18px; height:18px; border-radius:50%;
          background:#fff; cursor:pointer;
          box-shadow: 0 0 12px rgba(var(--glow),0.9);
          border:2px solid rgba(var(--glow),0.7);
        }
        .modes{
          display:grid;
          grid-template-columns:repeat(3,1fr);
          gap:6px;
          margin-top:14px;
        }
        .mode{
          padding:10px 8px; border-radius:10px;
          font-size:11px; font-weight:600;
          background:rgba(255,255,255,0.03);
          border:1px solid var(--line);
          color:var(--muted); cursor:pointer; text-align:center;
          transition:all .25s ease;
          display:flex; align-items:center; justify-content:center; gap:6px;
        }
        .mode svg{ width:12px; height:12px; }
        .mode:hover{ color:var(--text); border-color:rgba(var(--glow),0.3); }
        .mode.active{
          color:#fff;
          background: linear-gradient(135deg, rgba(var(--glow),0.25), rgba(var(--glow),0.08));
          border-color:rgba(var(--glow),0.5);
          box-shadow: inset 0 0 0 1px rgba(var(--glow),0.3), 0 0 14px rgba(var(--glow),0.2);
        }
        .amb-footer{
          margin-top:14px;
          padding-top:12px;
          border-top:1px solid var(--line);
          display:flex; justify-content:space-between; align-items:center;
          font-size:10px; color:var(--muted);
        }
        .amb-footer .saved{
          display:flex; align-items:center; gap:5px;
          color:#4cd982;
          opacity:0;
          transition: opacity .3s ease;
        }
        .amb-footer .saved.show{ opacity:1; }

        /* ===== MOBILE SEARCH TOGGLE (topbar icon + dropdown bar) ===== */
        .mobile-search-btn{ display:none; }
        .mobile-search-bar{
          display:none;
          position:fixed;
          top:64px; left:0; right:0;
          z-index:38;
          padding:10px 14px;
          background: linear-gradient(180deg, rgba(10,12,18,0.97), rgba(10,12,18,0.9));
          backdrop-filter: blur(14px);
          -webkit-backdrop-filter: blur(14px);
          border-bottom:1px solid var(--line);
          opacity:0;
          transform: translateY(-8px);
          transition: opacity .2s ease, transform .2s ease;
        }
        .mobile-search-bar.active{
          display:block;
          opacity:1;
          transform: translateY(0);
        }
        .mobile-search-bar-form{
          display:flex; align-items:center; gap:8px;
          background: rgba(255,255,255,0.05);
          border:1px solid var(--line);
          padding:9px 12px; border-radius:10px;
        }
        .mobile-search-bar-form input{
          background:transparent; border:0; outline:0; color:var(--text);
          font:13px 'Inter',sans-serif; width:100%;
        }
        .mobile-search-bar-form svg{ width:14px; height:14px; color:var(--muted); flex-shrink:0; }

        /* ===== LAYOUT ===== */
        .layout{
          position:relative; z-index:1;
          display:grid; grid-template-columns: 240px 1fr;
          gap:20px; padding:22px;
          max-width:1600px; margin:0 auto;
        }
        .sidebar{
          background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
          border:1px solid var(--line);
          border-radius:var(--radius);
          padding:16px;
          height:fit-content;
          position:sticky; top:88px;
          max-height:calc(100vh - 110px);
          overflow-y:auto;
          transition: transform .3s ease;
        }
        .sidebar::-webkit-scrollbar{ width:4px; }
        .sidebar::-webkit-scrollbar-track{ background:transparent; }
        .sidebar::-webkit-scrollbar-thumb{ background:rgba(var(--glow),0.3); border-radius:10px; }
        .side-title{
          font-size:10px; letter-spacing:2px; color:var(--muted);
          text-transform:uppercase; margin:12px 8px 8px;
        }
        .side-item{
          display:flex; align-items:center; gap:10px;
          padding:9px 10px; border-radius:9px;
          color:var(--muted); font-size:13px; font-weight:500;
          cursor:pointer; transition: all .2s ease;
          position:relative;
        }
        .side-item svg{width:15px;height:15px}
        .side-item:hover{color:var(--text); background:rgba(255,255,255,0.04)}
        .side-item.active{
          color:#fff;
          background: linear-gradient(90deg, rgba(var(--glow),0.18), transparent);
        }
        .side-item.active::before{
          content:""; position:absolute; left:-16px; top:8px; bottom:8px;
          width:3px; border-radius:0 3px 3px 0;
          background: rgba(var(--glow),1);
          box-shadow: 0 0 10px rgba(var(--glow),0.8);
        }
        .side-badge{
          margin-left:auto; font-size:10px;
          padding:2px 7px; border-radius:10px;
          background: rgba(var(--glow),0.18);
          color: rgba(var(--glow),1);
          border:1px solid rgba(var(--glow),0.3);
          font-family:'JetBrains Mono',monospace;
        }
        .storage{
          margin-top:16px; padding:14px;
          background: rgba(255,255,255,0.02);
          border:1px solid var(--line);
          border-radius:10px;
        }
        .storage-head{display:flex; justify-content:space-between; font-size:11px; margin-bottom:8px}
        .storage-head b{color:var(--text)}
        .storage-head span{color:var(--muted)}
        .storage-bar{height:6px; background:rgba(255,255,255,0.06); border-radius:3px; overflow:hidden}
        .storage-fill{
          height:100%; width:62%;
          background: linear-gradient(90deg, rgba(var(--glow),0.6), rgba(var(--glow),1));
          box-shadow: 0 0 10px rgba(var(--glow),0.6);
          border-radius:3px;
          transition: all .6s ease;
        }
        .storage small{display:block; color:var(--muted); font-size:10px; margin-top:8px}

        /* ===== MAIN ===== */
        .main{display:flex; flex-direction:column; gap:20px; min-width:0}
        .page-head{
          display:flex; align-items:flex-end; justify-content:space-between; gap:16px;
          flex-wrap:wrap;
        }
        .page-head h1{
          margin:0; font-size:26px; font-weight:700; letter-spacing:-0.5px;
        }
        .page-head p{margin:4px 0 0; color:var(--muted); font-size:13px}
        .actions{display:flex; gap:10px}
        .btn{
          appearance:none; border:1px solid var(--line);
          background: rgba(255,255,255,0.04);
          color:var(--text); font:600 13px 'Inter',sans-serif;
          padding:10px 14px; border-radius:10px; cursor:pointer;
          display:inline-flex; align-items:center; gap:8px;
          transition:all .25s ease;
        }
        .btn svg{width:14px;height:14px}
        .btn:hover{border-color:rgba(var(--glow),0.4); box-shadow:0 0 14px rgba(var(--glow),0.2)}
        .btn.primary{
          background: linear-gradient(180deg, rgba(var(--glow),0.95), rgba(var(--glow),0.65));
          color:#0a0d14; border-color: transparent;
          box-shadow: 0 8px 24px rgba(var(--glow),0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        }
        .btn.primary:hover{filter:brightness(1.08); box-shadow:0 10px 28px rgba(var(--glow),0.5)}

        /* ===== STATS GRID ===== */
        .stats{display:grid; grid-template-columns:repeat(4,1fr); gap:16px}
        .card{
          position:relative;
          background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
          border:1px solid var(--line);
          border-radius:var(--radius);
          padding:18px;
          overflow:hidden;
          transition: all .3s ease;
        }
        .card::before{
          content:""; position:absolute; inset:-1px;
          border-radius:var(--radius);
          padding:1px;
          background: linear-gradient(135deg, rgba(var(--glow),0.4), transparent 40%, transparent 60%, rgba(var(--glow),0.2));
          -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
          -webkit-mask-composite: xor; mask-composite: exclude;
          opacity:0; transition:opacity .3s ease;
          pointer-events:none;
        }
        .card:hover::before{opacity:1}
        .card:hover{transform:translateY(-2px); box-shadow:0 20px 40px -20px rgba(var(--glow),0.3)}
        .stat-top{display:flex; align-items:center; justify-content:space-between}
        .stat-label{font-size:12px; color:var(--muted); font-weight:500}
        .stat-icon{
          width:34px; height:34px; border-radius:9px;
          background: rgba(var(--glow),0.12);
          border:1px solid rgba(var(--glow),0.25);
          display:grid; place-items:center; color:rgba(var(--glow),1);
          box-shadow: inset 0 0 10px rgba(var(--glow),0.15);
          transition: all .6s ease;
        }
        .stat-icon svg{width:16px;height:16px}
        .stat-value{font-size:26px; font-weight:700; margin-top:12px; letter-spacing:-0.5px}
        .stat-delta{
          display:inline-flex; align-items:center; gap:4px;
          font-size:11px; font-family:'JetBrains Mono',monospace;
          padding:3px 7px; border-radius:6px; margin-top:8px;
        }
        .delta-up{background:rgba(76,217,130,0.12); color:#4cd982; border:1px solid rgba(76,217,130,0.25)}
        .delta-down{background:rgba(255,99,99,0.12); color:#ff7a7a; border:1px solid rgba(255,99,99,0.25)}
        .stat-spark{
          margin-top:14px; height:40px;
          background: linear-gradient(180deg, rgba(var(--glow),0.15), transparent);
          border-radius:8px;
          position:relative; overflow:hidden;
        }
        .stat-spark svg{width:100%; height:100%; display:block}

        /* ===== ROWS ===== */
        .row{display:grid; grid-template-columns: 1.6fr 1fr; gap:16px}
        .panel{
          background: linear-gradient(180deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
          border:1px solid var(--line);
          border-radius:var(--radius);
          padding:20px;
        }
        .panel-head{display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; gap:10px; flex-wrap:wrap}
        .panel-head h3{margin:0; font-size:15px; font-weight:600}
        .panel-head .sub{font-size:11px; color:var(--muted); margin-top:2px}
        .tabs{display:flex; gap:4px; background:rgba(255,255,255,0.03); padding:3px; border-radius:8px; border:1px solid var(--line)}
        .tabs button{
          border:0; background:transparent; color:var(--muted);
          font:600 11px 'Inter',sans-serif; padding:5px 10px; border-radius:6px; cursor:pointer;
        }
        .tabs button.active{color:#fff; background:rgba(var(--glow),0.2); box-shadow:inset 0 0 0 1px rgba(var(--glow),0.3)}

        /* chart */
        .chart{height:260px; position:relative}
        .chart svg{width:100%; height:100%}

        /* table */
        table{width:100%; border-collapse:collapse}
        th,td{text-align:left; padding:10px 8px; font-size:12.5px; border-bottom:1px solid var(--line)}
        th{color:var(--muted); font-weight:500; font-size:11px; text-transform:uppercase; letter-spacing:1px}
        td{color:var(--text)}
        tbody tr{transition:background .2s ease}
        tbody tr:hover{background:rgba(255,255,255,0.03)}
        .doc-cell{display:flex; align-items:center; gap:10px}
        .doc-icon{
          width:32px; height:38px; border-radius:5px;
          background: linear-gradient(135deg, rgba(var(--glow),0.25), rgba(var(--glow),0.05));
          border:1px solid rgba(var(--glow),0.3);
          display:grid; place-items:center;
          font-size:9px; font-weight:700; letter-spacing:0.5px;
          color:rgba(var(--glow),1);
          font-family:'JetBrains Mono',monospace;
          flex-shrink:0;
          position:relative;
        }
        .doc-icon::after{
          content:""; position:absolute; top:0; right:0;
          width:8px; height:8px;
          background: linear-gradient(135deg, transparent 50%, rgba(var(--glow),0.4) 50%);
          border-bottom-left-radius:3px;
        }
        .doc-meta small{display:block; color:var(--muted); font-size:11px; margin-top:2px}
        .pill{
          display:inline-flex; align-items:center; gap:5px;
          padding:3px 8px; border-radius:20px; font-size:11px; font-weight:500;
          border:1px solid var(--line);
        }
        .pill::before{content:""; width:6px; height:6px; border-radius:50%; background:currentColor}
        .pill.signed{color:#4cd982; background:rgba(76,217,130,0.08); border-color:rgba(76,217,130,0.25)}
        .pill.pending{color:#ffb547; background:rgba(255,181,71,0.08); border-color:rgba(255,181,71,0.25)}
        .pill.rejected{color:#ff7a7a; background:rgba(255,122,122,0.08); border-color:rgba(255,122,122,0.25)}
        .pill.draft{color:#8892a6; background:rgba(136,146,166,0.08); border-color:rgba(136,146,166,0.2)}

        /* activity */
        .activity{display:flex; flex-direction:column; gap:14px}
        .act{display:flex; gap:12px; align-items:flex-start}
        .act-dot{
          width:10px; height:10px; border-radius:50%; margin-top:6px;
          background: rgba(var(--glow),1);
          box-shadow: 0 0 10px rgba(var(--glow),0.8);
          flex-shrink:0;
        }
        .act-body{flex:1; min-width:0}
        .act-body p{margin:0; font-size:13px}
        .act-body small{color:var(--muted); font-size:11px}

        /* ===== SIGNERS LIST ===== */
        .signers{display:flex; flex-direction:column; gap:10px}
        .signer{
          display:flex; align-items:center; gap:12px;
          padding:10px; border-radius:10px;
          background: rgba(255,255,255,0.02);
          border:1px solid var(--line);
          transition: all .2s ease;
        }
        .signer:hover{border-color:rgba(var(--glow),0.3); background:rgba(var(--glow),0.04)}
        .signer-avatar{
          width:36px; height:36px; border-radius:50%;
          background: linear-gradient(135deg, rgba(var(--glow),0.5), rgba(var(--glow),0.15));
          border:1px solid rgba(var(--glow),0.3);
          display:grid; place-items:center; font-weight:700; font-size:12px;
          flex-shrink:0;
        }
        .signer-info{flex:1; min-width:0}
        .signer-info b{display:block; font-size:13px; font-weight:600}
        .signer-info small{color:var(--muted); font-size:11px}
        .signer-status{
          font-size:10px; padding:3px 8px; border-radius:10px;
          font-weight:600; letter-spacing:0.5px;
        }
        .status-ok{background:rgba(76,217,130,0.15); color:#4cd982; border:1px solid rgba(76,217,130,0.3)}
        .status-wait{background:rgba(255,181,71,0.15); color:#ffb547; border:1px solid rgba(255,181,71,0.3)}

        /* ===== NOTIFICATIONS ===== */
        .notif-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 340px;
            background: linear-gradient(180deg, rgba(18,21,30,0.98), rgba(13,15,22,0.98));
            backdrop-filter: blur(20px);
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 30px rgba(var(--glow),0.15);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        .notif-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .notif-header {
            padding: 14px 18px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notif-header strong { font-size: 0.95rem; color: var(--text); }
        .notif-list { max-height: 320px; overflow-y: auto; }
        .notif-list::-webkit-scrollbar { width: 4px; }
        .notif-list::-webkit-scrollbar-thumb { background: rgba(var(--glow),0.4); border-radius: 10px; }
        .notif-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--text);
            border-bottom: 1px solid var(--line);
            transition: background 0.2s;
            position: relative;
        }
        .notif-item:hover { background: rgba(var(--glow),0.05); }
        .notif-item.unread { background: rgba(var(--glow),0.08); }
        .notif-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.1rem;
        }
        .notif-body { flex: 1; min-width: 0; }
        .notif-text { font-size: 0.82rem; line-height: 1.4; color: var(--text); }
        .notif-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: rgba(var(--glow),1);
            box-shadow: 0 0 8px rgba(var(--glow),1);
            margin-top: 6px;
        }
        .notif-empty { padding: 40px 20px; text-align: center; color: var(--muted); }
        .notif-footer {
            padding: 10px;
            border-top: 1px solid var(--line);
            text-align: center;
            background: rgba(255,255,255,0.02);
        }
        .notif-footer a {
            color: rgba(var(--glow),1);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .notif-footer a:hover { text-decoration: underline; }
        @keyframes notif-ping {
            0% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.5); opacity: 0; }
            100% { transform: scale(1); opacity: 0; }
        }
        .bell-shaking { animation: shake 2s infinite; }
        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            10%, 30%, 50%, 70%, 90% { transform: rotate(-10deg); }
            20%, 40%, 60%, 80% { transform: rotate(10deg); }
        }

        /* ===== PROFILE ===== */
        .profile-dropdown{ position:relative; }
        .profile-btn{
            width:40px; height:40px; border-radius:12px; border:none;
            background:linear-gradient(135deg, rgba(var(--glow),0.9), rgba(var(--glow),0.5));
            color:#0a0d14; font-weight:700; font-size:15px;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; transition:all 0.3s ease;
            box-shadow:0 4px 15px rgba(var(--glow),0.4);
        }
        .profile-btn:hover{
            transform:translateY(-2px) scale(1.05);
            box-shadow:0 8px 25px rgba(var(--glow),0.6);
        }
        .profile-btn:active{ transform:translateY(0) scale(1); }
        .profile-menu{
            position:absolute;
            top:calc(100% + 8px);
            right:0;
            min-width:200px;
            background: linear-gradient(180deg, rgba(18,21,30,0.98), rgba(13,15,22,0.98));
            backdrop-filter: blur(20px);
            border:1px solid var(--line);
            border-radius:16px;
            padding:8px;
            box-shadow:0 10px 40px rgba(0,0,0,0.5), 0 0 30px rgba(var(--glow),0.15);
            opacity:0;
            visibility:hidden;
            transform:translateY(-10px);
            transition:all 0.3s ease;
            z-index:1000;
        }
        .profile-menu.active{
            opacity:1;
            visibility:visible;
            transform:translateY(0);
        }
        .profile-menu .menu-item{
            display:flex;
            align-items:center;
            gap:10px;
            padding:10px 14px;
            color:var(--text);
            text-decoration:none;
            border-radius:10px;
            transition:all 0.2s ease;
            font-size:14px;
        }
        .profile-menu .menu-item:hover{
            background:rgba(var(--glow),0.1);
            transform:translateX(4px);
        }
        .profile-menu .menu-item.logout{ color:#ff6b6b; }
        .profile-menu .menu-item.logout:hover{ background:rgba(255,107,107,0.15); }
        .profile-menu .menu-divider{
            border:0;
            border-top:1px solid var(--line);
            margin:6px 0;
            opacity:0.5;
        }

        /* ===== MOBILE-ONLY ДУБЛИКАТ ВЕРХНЕГО МЕНЮ В САЙДБАРЕ ===== */
        .mobile-nav-group{
            display:none;
            flex-direction:column;
            gap:4px;
            margin-bottom:14px;
            padding-bottom:14px;
            border-bottom:1px solid var(--line);
        }

        /* ===== MOBILE-ONLY ПОИСК В САЙДБАРЕ (дублирует .search из топбара) ===== */
        .mobile-search{
            display:none;
            align-items:center; gap:8px;
            background: rgba(255,255,255,0.05);
            border:1px solid var(--line);
            padding:9px 12px; border-radius:10px;
            margin-bottom:14px;
        }
        .mobile-search svg{ width:14px; height:14px; color:var(--muted); flex-shrink:0; }
        .mobile-search input{
            background:transparent; border:0; outline:0;
            color:var(--text); font:13px 'Inter',sans-serif; width:100%;
        }
        .mobile-search input::placeholder{ color:var(--muted); }

        /* ===== ПОЛНАЯ АДАПТИВНОСТЬ ===== */

        /* Маленькие десктопы (до 1200px) */
        @media (max-width: 1200px) {
            .topbar { padding: 12px 18px; gap: 12px; }
            .search { min-width: 200px; }
            .layout { padding: 18px; gap: 18px; }
            .sidebar { padding: 14px; }
            .panel { padding: 18px; }
            .card { padding: 16px; }
            .stat-value { font-size: 24px; }
            .page-head h1 { font-size: 24px; }
        }

        /* Планшеты в ландшафте (до 1100px) - ПОКАЗЫВАЕМ БУРГЕР */
        @media (max-width: 1100px) {
            .burger-btn { display: flex; align-items: center; justify-content: center; }
            .mobile-search-btn{ display:grid; place-items:center; }

            .layout {
                grid-template-columns: 1fr;
                padding: 16px;
                gap: 16px;
            }

            /* Sidebar становится off-canvas */
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                width: 280px;
                height: 100vh;
                max-height: 100vh;
                z-index: 1000;
                border-radius: 0;
                transform: translateX(-100%);
                padding-top: 80px;
                background: #0a0c12;
            }
            .sidebar.active {
                transform: translateX(0);
            }

            /* На мобильных верхнее меню дублируется в сайдбаре */
            .mobile-nav-group{ display:flex; }

            /* На мобильных строка поиска тоже переезжает в сайдбар */
            .mobile-search{ display:flex; }

            .sidebar{
                background: #0a0c12;
            }
            .sidebar .side-item{
                color: #e7ecf3;
            }
            .sidebar .side-item:hover,
            .sidebar .side-item.active{
                color:#fff;
            }
            .sidebar .side-title{
                color: rgba(180,188,204,0.95);
            }
            /* Пока мобильное меню открыто, полоса подсветки не должна просвечивать поверх сайдбара */
            body.menu-open .ambient{
                display:none;
            }

            .stats { grid-template-columns: repeat(2, 1fr); }
            .row { grid-template-columns: 1fr; }
            .search { display: none; }
            .topbar { padding: 12px 16px; }
            .nav button { padding: 7px 12px; font-size: 12px; }
            .nav button svg { width: 14px; height: 14px; }
            .brand { font-size: 14px; gap: 8px; }
            .brand-dot { width: 30px; height: 30px; }
            .brand-dot svg { width: 16px; height: 16px; }
            .lang-btn { min-width: 80px; padding: 7px 10px; font-size: 11px; }
            .lang-btn .flag { font-size: 15px; }
            .amb-btn { padding: 7px 12px; font-size: 11px; }
            .amb-btn .amb-swatch { width: 12px; height: 12px; }
            .icon-btn { width: 36px; height: 36px; }
            .icon-btn svg { width: 15px; height: 15px; }
            .profile-btn { width: 36px; height: 36px; font-size: 13px; }
            .side-item { padding: 10px 12px; font-size: 13px; }
            .side-item svg { width: 15px; height: 15px; }
            .side-badge { font-size: 9px; padding: 2px 6px; }
            .notif-dropdown { width: 320px; }
            .amb-panel {
    position: fixed;
    top: 64px;
    left: 50%;
    right: auto;
    transform: translateX(-50%) translateY(-10px) scale(0.96);
    width: 320px;
    padding: 18px;
}
.amb-panel::before{ display:none; } /* стрелочка больше не нужна, панель не привязана к кнопке */
.amb-panel.open{
    transform: translateX(-50%) translateY(0) scale(1);
}
            .color{ width:34px; height:34px; }
            .profile-menu { min-width: 180px; }
            .profile-menu .menu-item { padding: 9px 12px; font-size: 13px; }
        }

        /* Большие телефоны (до 768px) */
        @media (max-width: 768px) {
            .topbar { padding: 10px 12px; gap: 8px; }
            .brand { font-size: 12px; gap: 6px; }
            .brand-dot { width: 26px; height: 26px; border-radius: 7px; }
            .brand-dot svg { width: 14px; height: 14px; }
            .brand small { font-size: 7px; letter-spacing: 1.5px; }
            .nav { display: none; }
            .layout { padding: 12px; gap: 12px; }
            .sidebar { width: 260px; padding: 10px; border-radius: 0; }
            .sidebar-overlay { left: 260px; }
            .side-title { font-size: 9px; letter-spacing: 1.2px; margin: 8px 5px 5px; }
            .side-item { padding: 9px 10px; font-size: 12px; gap: 7px; border-radius: 7px; }
            .side-item svg { width: 14px; height: 14px; }
            .side-badge { font-size: 8px; padding: 1px 5px; }
            .storage { padding: 10px; margin-top: 10px; }
            .storage-head { font-size: 9px; margin-bottom: 6px; }
            .storage-bar { height: 4px; }
            .storage small { font-size: 8px; margin-top: 5px; }
            .main { gap: 14px; }
            .page-head { gap: 12px; }
            .page-head h1 { font-size: 20px; letter-spacing: -0.3px; }
            .page-head p { font-size: 11px; margin-top: 3px; }
            .actions { gap: 8px; }
            .btn { padding: 8px 12px; font-size: 12px; border-radius: 8px; gap: 6px; }
            .btn svg { width: 12px; height: 12px; }
            .stats { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .card { padding: 12px; border-radius: 10px; }
            .stat-label { font-size: 10px; }
            .stat-icon { width: 30px; height: 30px; border-radius: 7px; }
            .stat-icon svg { width: 14px; height: 14px; }
            .stat-value { font-size: 20px; margin-top: 8px; }
            .stat-delta { font-size: 9px; padding: 2px 5px; margin-top: 5px; border-radius: 5px; }
            .stat-spark { height: 32px; margin-top: 10px; border-radius: 6px; }
            .row { gap: 12px; }
            .panel { padding: 14px; border-radius: 10px; }
            .panel-head { margin-bottom: 14px; gap: 8px; }
            .panel-head h3 { font-size: 13px; }
            .panel-head .sub { font-size: 10px; }
            .tabs { padding: 2px; border-radius: 7px; }
            .tabs button { font-size: 10px; padding: 4px 8px; border-radius: 5px; }
            .chart { height: 220px; }
            table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            th, td { padding: 7px 6px; font-size: 11px; white-space: nowrap; }
            th { font-size: 9px; letter-spacing: 0.8px; }
            .doc-cell { gap: 8px; }
            .doc-icon { width: 26px; height: 32px; font-size: 8px; border-radius: 4px; }
            .doc-meta small { font-size: 9px; }
            .pill { font-size: 9px; padding: 2px 6px; gap: 4px; border-radius: 16px; }
            .pill::before { width: 5px; height: 5px; }
            .activity { gap: 10px; }
            .act { gap: 8px; }
            .act-dot { width: 8px; height: 8px; margin-top: 4px; }
            .act-body p { font-size: 11px; }
            .act-body small { font-size: 9px; }
            .signers { gap: 7px; }
            .signer { padding: 7px; border-radius: 7px; gap: 8px; }
            .signer-avatar { width: 30px; height: 30px; font-size: 10px; }
            .signer-info b { font-size: 11px; }
            .signer-info small { font-size: 9px; }
            .signer-status { font-size: 9px; padding: 2px 6px; border-radius: 8px; }
            .notif-dropdown { width: calc(100vw - 20px); right: -10px; max-width: 300px; }
            .notif-header { padding: 10px 14px; }
            .notif-header strong { font-size: 0.85rem; }
            .notif-list { max-height: 260px; }
            .notif-item { padding: 9px 12px; gap: 7px; }
            .notif-icon { width: 32px; height: 32px; font-size: 0.95rem; border-radius: 8px; }
            .notif-text { font-size: 0.75rem; }
            .notif-dot { width: 6px; height: 6px; }
            .notif-empty { padding: 28px 14px; }
            .notif-footer { padding: 8px; }
            .notif-footer a { font-size: 0.78rem; }
           .amb-panel { width: calc(100vw - 20px); max-width: 300px; left: 50%; right: auto; padding: 14px; border-radius: 14px; }
            .amb-panel h4 { font-size: 13px; gap: 6px; }
            .amb-panel h4 .live-dot { width: 7px; height: 7px; }
            .amb-panel p { font-size: 9px; margin-bottom: 12px; }
            .amb-preview { height: 55px; border-radius: 8px; margin-bottom: 12px; }
            .amb-preview-label { font-size: 8px; bottom: 6px; left: 10px; }
            .colors { gap: 6px; margin-bottom: 12px; }
            .color { width:32px; height:32px; border-radius: 8px; }
            .color.active::after { font-size: 12px; }
            .intensity { gap: 5px; }
            .intensity label { font-size: 9px; }
            .intensity input[type=range] { height: 5px; }
            .intensity input[type=range]::-webkit-slider-thumb { width: 15px; height: 15px; }
            .modes { gap: 4px; margin-top: 10px; }
            .mode { padding: 7px 5px; font-size: 9px; border-radius: 7px; gap: 4px; }
            .mode svg { width: 10px; height: 10px; }
            .amb-footer { font-size: 8px; margin-top: 10px; padding-top: 8px; }
            .lang-menu { min-width: 160px; padding: 4px; border-radius: 10px; }
            .lang-menu-title { font-size: 8px; letter-spacing: 1.2px; padding: 5px 7px 3px; }
            .lang-option { padding: 7px 8px; font-size: 11px; gap: 7px; border-radius: 6px; }
            .lang-option .flag { font-size: 15px; }
            .lang-option .check { width: 11px; height: 11px; }
            .profile-menu { min-width: 160px; padding: 5px; border-radius: 12px; }
            .profile-menu .menu-item { padding: 7px 10px; font-size: 11px; gap: 7px; border-radius: 8px; }
            .profile-menu .menu-divider { margin: 4px 0; }
            .lang-btn { min-width: 70px; padding: 6px 9px; font-size: 10px; border-radius: 8px; }
            .lang-btn .flag { font-size: 14px; }
            .lang-btn .arrow { width: 10px; height: 10px; }
            .amb-btn { padding: 6px 10px; font-size: 10px; border-radius: 8px; gap: 6px; }
            .amb-btn svg { width: 14px; height: 14px; }
            .amb-btn .amb-swatch { width: 11px; height: 11px; }
            .amb-btn .amb-label { display: none; }
            .icon-btn { width: 34px; height: 34px; border-radius: 8px; }
            .icon-btn svg { width: 14px; height: 14px; }
            .profile-btn { width: 34px; height: 34px; font-size: 12px; border-radius: 10px; }
            .mobile-search-bar{ top:57px; }
        }

        /* Маленькие телефоны (до 480px) */
        @media (max-width: 480px) {
            .topbar { padding: 7px 8px; gap: 5px; }
            .brand { font-size: 10px; gap: 4px; }
            .brand-dot { width: 22px; height: 22px; border-radius: 5px; }
            .brand-dot svg { width: 12px; height: 12px; }
            .layout { padding: 8px; gap: 8px; }
            .sidebar { width: 220px; padding: 7px; border-radius: 0; }
            .sidebar-overlay { left: 220px; }
            .side-title { font-size: 8px; letter-spacing: 0.8px; margin: 5px 3px 3px; }
            .side-item { padding: 7px 8px; font-size: 10px; gap: 5px; border-radius: 5px; }
            .side-item svg { width: 12px; height: 12px; }
            .storage { padding: 7px; margin-top: 7px; }
            .storage-head { font-size: 8px; }
            .storage-bar { height: 3px; }
            .storage small { font-size: 7px; }
            .main { gap: 10px; }
            .page-head h1 { font-size: 16px; }
            .page-head p { font-size: 9px; }
            .btn { padding: 6px 9px; font-size: 10px; border-radius: 6px; gap: 4px; }
            .btn svg { width: 10px; height: 10px; }
            .card { padding: 10px; border-radius: 8px; }
            .stat-label { font-size: 9px; }
            .stat-icon { width: 26px; height: 26px; border-radius: 6px; }
            .stat-icon svg { width: 12px; height: 12px; }
            .stat-value { font-size: 18px; margin-top: 7px; }
            .stat-delta { font-size: 8px; padding: 1px 4px; margin-top: 4px; }
            .stat-spark { height: 28px; margin-top: 8px; }
            .panel { padding: 10px; border-radius: 8px; }
            .panel-head h3 { font-size: 12px; }
            .panel-head .sub { font-size: 9px; }
            .tabs button { font-size: 9px; padding: 3px 6px; }
            .chart { height: 180px; }
            th, td { padding: 5px 4px; font-size: 9px; }
            .doc-icon { width: 22px; height: 28px; font-size: 7px; }
            .pill { font-size: 8px; padding: 1px 4px; }
            .act-body p { font-size: 10px; }
            .act-body small { font-size: 8px; }
            .signer { padding: 5px; gap: 6px; }
            .signer-avatar { width: 26px; height: 26px; font-size: 9px; }
            .signer-info b { font-size: 10px; }
            .signer-info small { font-size: 8px; }
            .notif-dropdown { width: calc(100vw - 16px); right: -3px; }
            .notif-item { padding: 7px 9px; }
            .notif-icon { width: 28px; height: 28px; font-size: 0.85rem; }
            .notif-text { font-size: 0.7rem; }
         .amb-panel { width: calc(100vw - 16px); left: 50%; right: auto; padding: 10px; }
            .amb-panel h4 { font-size: 11px; }
            .amb-panel p { font-size: 8px; }
            .amb-preview { height: 45px; }
            .colors { gap: 4px; }
            .color{ width:26px; height:26px; border-radius:7px; }
            .mode { font-size: 8px; padding: 5px 3px; }
            .lang-menu { min-width: 140px; }
            .lang-option { padding: 5px 6px; font-size: 9px; }
            .profile-menu { min-width: 140px; }
            .profile-menu .menu-item { padding: 5px 8px; font-size: 9px; }
            .lang-btn { padding: 5px 7px; }
            .icon-btn { width: 30px; height: 30px; }
            .icon-btn svg { width: 12px; height: 12px; }
            .profile-btn { width: 30px; height: 30px; font-size: 10px; }
            .mobile-search-bar{ top:50px; }
        }
    </style>
</head>
<body>

<!-- TOP BAR -->
<header class="topbar">
    <!-- БУРГЕР-КНОПКА (для мобильных) -->
    <button type="button" class="burger-btn" id="burgerBtn" aria-label="Открыть меню" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="brand">

        <div>
            DocSign
            <small data-i18n="brand_sub">ЭДО</small>
        </div>
    </div>

    <nav class="nav" id="nav">
        <a href="/dashboard" class="button-link" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center;">
            <button type="button" class="active" data-tab="dashboard">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                <span data-i18n="dashboard">Обзор</span>
            </button>
        </a>
        <button type="button" data-tab="docs" onclick="window.location.href='/documents'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <span data-i18n="documents">Документы</span>
        </button>
        <a href="/signatures" class="button-link" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center;">
            <button type="button" data-tab="sign" style="pointer-events: none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
                <span data-i18n="signatures">Подписание</span>
            </button>
        </a>
        <a href="/users" data-tab="counter" style="text-decoration: none; display: inline-block;">
            <button type="button" style="pointer-events: none;">
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
        <input
                type="text"
                name="query"
                value="{{ request('query') }}"
                data-i18n-placeholder="search_placeholder"
                placeholder="Поиск..."
        />
        <button type="submit" style="display: none;"></button>
    </form>

    <!-- КНОПКА-ЛУПА ДЛЯ МОБИЛЬНЫХ (открывает выпадающую строку поиска под шапкой) -->
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

    {{-- ===== AMBIENT BUTTON В TOPBAR ===== --}}
    <div class="lang-switcher" id="ambWrapper" style="position:relative;">
        <button type="button" class="amb-btn" id="ambBtn" data-i18n-title="color_title" title="Цвет подсветки">
            <div class="amb-swatch"></div>
            <span class="amb-label" data-i18n="color_title">Цвет</span>
            <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;">
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
            <span class="flag" id="langFlag">🇷🇺</span>
            <span class="lang-name" id="langName">Русский</span>
            <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </button>
        <div class="lang-menu" id="langMenu">
            <div class="lang-menu-title" data-i18n="choose_language">Выберите язык</div>
            <div class="lang-option active" data-lang="ru">
                <span class="flag">🇷🇺</span>
                <span class="name">Русский</span>
                <svg class="check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="lang-option" data-lang="tj">
                <span class="flag">🇹🇯</span>
                <span class="name">Тоҷикӣ</span>
                <svg class="check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="lang-option" data-lang="en">
                <span class="flag">🇬🇧</span>
                <span class="name">English</span>
                <svg class="check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
        </div>
    </div>

    <div id="notifWrapper" class="position-relative d-inline-block" style="position:relative;">
        <button type="button" id="notifBtn" class="icon-btn position-relative {{ $unreadCount > 0 ? 'bell-shaking' : '' }}"
                data-i18n-title="notifications"
                title="Уведомления"
                style="background: none; border: none; padding: 8px; cursor: pointer; color: inherit;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            @if($unreadCount > 0)
            <span id="notifPing" class="position-absolute bg-danger rounded-circle"
                  style="top: 4px; right: 4px; width: 10px; height: 10px; animation: notif-ping 1.5s infinite; opacity: 0.7;"></span>
            <span id="notifBadge" class="position-absolute rounded-pill notif-badge"
                  style="top: -5px; right: -5px; font-size: 10px; padding: 2px 6px;
                     background: #dc3545; color: #fff; border: 2px solid #fff;
                     font-weight: bold; min-width: 18px; text-align: center;
                     box-shadow: 0 2px 4px rgba(220,53,69,0.4);">
            {{ $unreadCount }}
        </span>
            @endif
        </button>

        <div id="notifDropdown" class="notif-dropdown">
            <div class="notif-header">
                <strong data-i18n="notifications">Уведомления</strong>
                <span id="headerBadge" class="badge rounded-pill {{ $unreadCount > 0 ? '' : 'd-none' }}"
                      style="background: #dc3545; color: #fff;">{{ $unreadCount }}</span>
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
                $iconColor = $isComment ? '#22c55e' : '#f97316';
                $isUnread = !$notification->is_read;
                $url = $data['url'] ?? (isset($data['document_id']) ? route('documents.show', $data['document_id']) : '#');
                @endphp
                <a href="{{ $url }}" class="notif-item {{ $isUnread ? 'unread' : '' }}"
                   data-id="{{ $notification->id }}" data-url="{{ $url }}" data-ts="{{ $notification->created_at->timestamp }}">
                    <div class="notif-icon" style="background: {{ $isComment ? 'rgba(34,197,94,0.1)' : 'rgba(249,115,22,0.1)' }};">
                        <i class="bi {{ $iconClass }}" style="color: {{ $iconColor }};"></i>
                    </div>
                    <div class="notif-body">
                        <div class="notif-text">
                            <strong>{{ $sender }}</strong>
                            <span class="notif-action">{{ $action }}</span>
                            <strong>{{ $docName }}</strong>
                        </div>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                    @if($isUnread)
                    <span class="notif-dot"></span>
                    @endif
                </a>
                @empty
                <div class="notif-empty">
                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                    <div class="mt-2" data-i18n="no_notifications">Нет уведомлений</div>
                </div>
                @endforelse
            </div>
            <div class="notif-footer">
                <a href="{{ $notifRoute }}" data-i18n="all_notifications">Все уведомления →</a>
            </div>
        </div>
    </div>

    <style>
        @keyframes notif-fresh-pulse {
            0% { box-shadow: 0 0 0 0 rgba(79, 140, 255, 0.6); }
            70% { box-shadow: 0 0 0 10px rgba(79, 140, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(79, 140, 255, 0); }
        }
        .notif-item.fresh {
            animation: notif-fresh-pulse 1.5s ease-out 2;
            background: rgba(79, 140, 255, 0.08) !important;
            border-left: 3px solid #4f8cff !important;
        }
        .notif-item.fresh:hover {
            background: rgba(79, 140, 255, 0.15) !important;
        }
        @keyframes bell-ring {
            0%, 100% { transform: rotate(0); }
            10%, 30% { transform: rotate(-15deg); }
            20%, 40% { transform: rotate(15deg); }
        }
        #notifBtn.ringing svg {
            animation: bell-ring 0.8s ease-in-out;
            transform-origin: top center;
        }
    </style>

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
                if (!wrapper.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
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
                    if (!badge) {
                        const newBadge = document.createElement('span');
                        newBadge.id = 'notifBadge';
                        newBadge.className = 'position-absolute rounded-pill notif-badge';
                        newBadge.style.cssText = 'top:-5px;right:-5px;font-size:10px;padding:2px 6px;background:#dc3545;color:#fff;border:2px solid #fff;font-weight:bold;min-width:18px;text-align:center;box-shadow:0 2px 4px rgba(220,53,69,0.4);';
                        newBadge.textContent = count;
                        btn.appendChild(newBadge);
                    } else {
                        badge.textContent = count;
                    }

                    if (!ping) {
                        const newPing = document.createElement('span');
                        newPing.id = 'notifPing';
                        newPing.className = 'position-absolute bg-danger rounded-circle';
                        newPing.style.cssText = 'top:4px;right:4px;width:10px;height:10px;animation:notif-ping 1.5s infinite;opacity:0.7;';
                        btn.appendChild(newPing);
                    }

                    headerBadge.textContent = count;
                    headerBadge.classList.remove('d-none');
                    btn.classList.add('bell-shaking');
                } else {
                    const b = document.getElementById('notifBadge');
                    const p = document.getElementById('notifPing');
                    if (b) b.remove();
                    if (p) p.remove();
                    headerBadge.classList.add('d-none');
                    btn.classList.remove('bell-shaking');
                }
            }

            function renderNotification(n) {
                const isComment = n.type === 'comment' || /коммент/i.test(n.message || '');
                const action = isComment ? 'оставил комментарий к' : 'назначил вам документ';
                const iconClass = isComment ? 'bi-chat-left-text' : 'bi-pin-angle-fill';
                const iconColor = isComment ? '#22c55e' : '#f97316';
                const bgColor = isComment ? 'rgba(34,197,94,0.1)' : 'rgba(249,115,22,0.1)';

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
                        <small class="text-muted">${escapeHtml(n.time)}</small>
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
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
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

    <div class="profile-dropdown" id="profileWrapper">
        <button type="button" class="profile-btn" id="profileBtn">
            @if(auth()->user()->avatar)
            <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                 alt="{{ auth()->user()->name }}"
                 class="profile-avatar">
            @else
            <span class="profile-initials">
                {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}{{ Str::upper(Str::substr(explode(' ', auth()->user()->name)[1] ?? '', 0, 1)) }}
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

    <style>
        .profile-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            overflow: hidden;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(var(--glow), 0.8), rgba(var(--glow), 0.4));
            border: none;
            cursor: pointer;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
        }

        .profile-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 50%;
        }

        .profile-initials {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            line-height: 1;
        }
    </style>
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
                if (!wrapper.contains(e.target)) {
                    menu.classList.remove('active');
                }
            });
        })();
    </script>
</header>

<!-- ВЫПАДАЮЩАЯ СТРОКА ПОИСКА ДЛЯ МОБИЛЬНЫХ (открывается кнопкой-лупой) -->
<div class="mobile-search-bar" id="mobileSearchBar">
    <form action="{{ route('search') }}" method="GET" class="mobile-search-bar-form">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
                type="text"
                name="query"
                value="{{ request('query') }}"
                data-i18n-placeholder="search_placeholder"
                placeholder="Поиск..."
                id="mobileSearchInput"
        />
        <button type="submit" style="display: none;"></button>
    </form>
</div>

<!-- OVERLAY для мобильного меню -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- AMBIENT LIGHT STRIP -->
<div class="ambient" id="ambient"></div>

<!-- LAYOUT -->
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <!-- Поиск — виден только на мобильных (когда .search скрыт в топбаре) -->
        <form action="{{ route('search') }}" method="GET" class="mobile-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
                    type="text"
                    name="query"
                    value="{{ request('query') }}"
                    data-i18n-placeholder="search_placeholder"
                    placeholder="Поиск..."
            />
            <button type="submit" style="display: none;"></button>
        </form>

        <!-- Дубликат верхнего меню — виден только на мобильных (когда .nav скрыт бургером) -->
        <div class="mobile-nav-group">
            <a href="/dashboard" class="side-item" style="text-decoration: none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                <span data-i18n="dashboard">Обзор</span>
            </a>
            <a href="/documents" class="side-item" style="text-decoration: none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span data-i18n="documents">Документы</span>
            </a>
            <a href="/signatures" class="side-item" style="text-decoration: none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
                <span data-i18n="signatures">Подписание</span>
            </a>
            <a href="/users" class="side-item" style="text-decoration: none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span data-i18n="counterparties">Команда</span>
            </a>
        </div>

        <div class="side-title" data-i18n="workspace">Рабочее пространство</div>

        <a href="{{ route('documents.index', ['type' => 'incoming']) }}" class="side-item" style="text-decoration: none; color: inherit;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span data-i18n="incoming">Входящие</span>
        </a>
        <a href="{{ route('documents.index', ['type' => 'outgoing']) }}" class="side-item" style="text-decoration: none; color: inherit;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            <span data-i18n="outgoing">Исходящие</span>
        </a>
        <a href="{{ route('documents.index', ['status' => 'waiting']) }}" style="text-decoration: none; color: inherit; display: block;">
            <div class="side-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 19l7-7 3 3-7 7-3-3z"/>
                    <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/>
                </svg>
                <span data-i18n="on_signing">На подписании</span>
            </div>
        </a>
        <a href="{{ route('documents.index', ['status' => 'draft']) }}" style="text-decoration: none; color: inherit; display: block;">
            <div class="side-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="21 8 21 21 3 21 3 8"/>
                    <rect x="1" y="3" width="22" height="5"/>
                    <line x1="10" y1="12" x2="14" y2="12"/>
                </svg>
                <span data-i18n="archive">Архив</span>
            </div>
        </a>

        <div class="side-title" data-i18n="management">Управление</div>
        <a href="/analysis"
           class="side-item"
           data-page="analysis"
           style="text-decoration: none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="side-icon">
                <path d="M18 20V10M12 20V4M6 20v-6"/>
            </svg>
            <span data-i18n="analysis">Анализ</span>
        </a>

        <a href="{{ route('notifications.index') }}"
           class="side-item"
           data-page="notifications"
           style="text-decoration: none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="side-icon">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span data-i18n="notifications">Уведомления</span>
            @if(isset($unreadCount) && $unreadCount > 0)
            <span class="side-badge">{{ $unreadCount }}</span>
            @endif
        </a>
        <a href="/logs"
           class="side-item"
           style="text-decoration: none !important;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="side-icon">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
            <span data-i18n="history">История</span>
        </a>
        <a href="/strel"
           class="side-item"
           data-page="strel"
           style="text-decoration: none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="side-icon">
                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                <polyline points="16 7 22 7 22 13"/>
            </svg>
            <span data-i18n="strel">Стрелки</span>
        </a>
        <a href="/profile" style="text-decoration: none; color: inherit; display: block;">
            <div class="side-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span data-i18n="profile">Профиль</span>
            </div>
        </a>

        <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form" style="margin:0;">
            @csrf
            <a href="#" class="side-item" onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();" style="text-decoration: none !important; color: #ff6b6b;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="side-icon">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span data-i18n="logout">Выход</span>
            </a>
        </form>
    </aside>

    <!-- MAIN -->
    @yield('content')
</div>

<script>
    // ============================================================
    // ===== БУРГЕР-МЕНЮ (МОБИЛЬНОЕ) =====
    // ============================================================
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
            if (sidebar.classList.contains('active')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        overlay.addEventListener('click', closeMenu);

        // Закрытие по Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeMenu();
            }
        });

        // Закрытие при клике на пункт меню (на мобильных),
        // но НЕ мешаем полю поиска и его кнопке отправки
        sidebar.querySelectorAll('.side-item').forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 1100) {
                    closeMenu();
                }
            });
        });

        // Мобильная форма поиска в сайдбаре — тоже должна закрывать меню при отправке
        const mobileSearchForm = sidebar.querySelector('.mobile-search');
        if (mobileSearchForm) {
            mobileSearchForm.addEventListener('submit', function() {
                if (window.innerWidth <= 1100) {
                    closeMenu();
                }
            });
        }

        // Закрытие при изменении размера окна
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1100) {
                closeMenu();
            }
        });
    })();

    // ============================================================
    // ===== МОБИЛЬНАЯ ЛУПА В ШАПКЕ (выпадающая строка поиска) =====
    // ============================================================
    (function() {
        const btn = document.getElementById('mobileSearchBtn');
        const bar = document.getElementById('mobileSearchBar');
        const input = document.getElementById('mobileSearchInput');
        if (!btn || !bar) return;

        function openBar() {
            bar.classList.add('active');
            setTimeout(() => { if (input) input.focus(); }, 60);
        }
        function closeBar() {
            bar.classList.remove('active');
        }

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (bar.classList.contains('active')) {
                closeBar();
            } else {
                openBar();
            }
        });

        document.addEventListener('click', function(e) {
            if (bar.classList.contains('active') && !bar.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
                closeBar();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && bar.classList.contains('active')) {
                closeBar();
            }
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth > 1100) {
                closeBar();
            }
        });
    })();

    // ============================================================
    // ===== AMBIENT LIGHTING — ПОЛНАЯ СИСТЕМА С СОХРАНЕНИЕМ =====
    // ============================================================
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

    function saveAmbient(settings){
        localStorage.setItem(AMBIENT_KEY, JSON.stringify(settings));
    }

    function applyColor(rgb){
        document.documentElement.style.setProperty('--glow', rgb);

        const preview = document.getElementById('ambPreview');
        if (preview) {
            preview.style.background = `linear-gradient(90deg, rgba(${rgb},0.1), rgba(${rgb},0.7), rgba(${rgb},0.1))`;
            preview.style.boxShadow = `inset 0 0 30px rgba(${rgb},0.4), 0 0 20px rgba(${rgb},0.3)`;
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
        if (mode === 'pulse') {
            ambient.style.animation = 'pulseFast 1.5s ease-in-out infinite';
        } else if (mode === 'breathe') {
            ambient.style.animation = 'breatheGlow 4s ease-in-out infinite';
        } else {
            ambient.style.animation = 'pulseGlow 4s ease-in-out infinite';
        }
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
                el.style.background = `radial-gradient(circle at 30% 30%, rgba(${c.rgb},1), rgba(${c.rgb},0.5))`;
                el.style.color = `rgb(${c.rgb})`;
                el.style.boxShadow = `0 4px 14px rgba(${c.rgb},0.4), inset 0 0 10px rgba(255,255,255,0.15)`;
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

        document.querySelectorAll('.mode').forEach(m=>{
            m.classList.toggle('active', m.dataset.mode === settings.mode);
        });

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

        if (intInput) {
            intInput.addEventListener('input', (e)=>{
                const v = parseInt(e.target.value);
                if (intVal) intVal.textContent = v + '%';
                applyIntensity(v);
                const cur = loadAmbient();
                cur.intensity = v;
                saveAmbient(cur);
                flashSaved();
            });
        }

        if (spreadInput) {
            spreadInput.addEventListener('input', (e)=>{
                const v = parseInt(e.target.value);
                if (spreadVal) spreadVal.textContent = v + '%';
                applySpread(v);
                const cur = loadAmbient();
                cur.spread = v;
                saveAmbient(cur);
                flashSaved();
            });
        }

        document.querySelectorAll('.mode').forEach(m=>{
            m.addEventListener('click', ()=>{
                document.querySelectorAll('.mode').forEach(x=>x.classList.remove('active'));
                m.classList.add('active');
                const cur = loadAmbient();
                cur.mode = m.dataset.mode;
                saveAmbient(cur);
                applyMode(cur.mode);
                flashSaved();
            });
        });
    });

    // ============================================================
    // ===== ПЕРЕВОДЫ / TRANSLATIONS / ТАРҶУМАҲО =====
    // ============================================================

   const TRANSLATIONS = {
    ru: {
        brand_sub: 'ЭДО',
        dashboard: 'Обзор',
        documents: 'Документы',
        signatures: 'Подписание',
        counterparties: 'Команда ',
        reports: 'Отчёты',
        search_placeholder: 'Поиск...',
        choose_language: 'Выберите язык',
        notifications: 'Уведомления',
        all_notifications: 'Все уведомления →',
        no_notifications: 'Нет уведомлений',
        notif_action_comment: 'оставил комментарий к',
        notif_action_assign: 'назначил вам документ',
        system: 'Система',
        document: 'Документ',
        profile: 'Профиль',
        logout: 'Выйти',
        workspace: 'Рабочее пространство',
        control_panel: 'Панель управления',
        incoming: 'Входящие',
        outgoing: 'Исходящие',
        on_signing: 'На подписании',
        archive: 'Архив',
        management: 'Управление',
        analysis: 'Анализ',
        history: 'История',
        exit: 'Выход',
        ambient_title: 'Ambient Lighting',
        ambient_desc: 'Настройте подсветку как в премиальном авто',
        intensity: 'Интенсивность',
        spread: 'Распространение',
        mode_solid: 'Статичный',
        mode_pulse: 'Пульс',
        mode_breathe: 'Дыхание',
        color_title: 'Цвет',
        color_electric: 'Электрик',
        color_neon: 'Неон',
        color_cardinal: 'Кардинал',
        color_sunset: 'Закат',
        color_lime: 'Лайм',
        color_aqua: 'Аква',
        color_fuchsia: 'Фуксия',
        color_gold: 'Золото',
        color_ice: 'Лёд',
        color_mint: 'Мята',
        color_indigo: 'Индиго',
        color_rose: 'Роза',
        auto_save: 'Автосохранение',
        saved: 'Сохранено',
        status_signed: 'Подписан',
        status_pending: 'Ожидает',
        status_rejected: 'Отклонён',
        status_draft: 'Черновик',
         strel: 'Стрелки'
    },
    tj: {
        brand_sub: 'Ҳуҷҷатгардонӣ',
        dashboard: 'Панел',
        documents: 'Ҳуҷҷатҳо',
        signatures: 'Имзоҳо',
        counterparties: 'Даста',
        reports: 'Ҳисоботҳо',
        search_placeholder: 'Ҷустуҷӯи...',
        choose_language: 'Забонро интихоб кунед',
        notifications: 'Огоҳиҳо',
        all_notifications: 'Ҳамаи огоҳиҳо →',
        no_notifications: 'Огоҳиҳо нест',
        notif_action_comment: 'шарҳ гузошт ба',
        notif_action_assign: 'ба шумо ҳуҷҷат таъин кард',
        system: 'Система',
        document: 'Ҳуҷҷат',
        profile: 'Профил',
        logout: 'Баромад',
        workspace: 'Фазои корӣ',
        control_panel: 'Панели идоракунӣ',
        incoming: 'Воридшаванда',
        outgoing: 'Содиршаванда',
        on_signing: 'Дар раванди имзо',
        archive: 'Бойгонӣ',
        management: 'Идоракунӣ',
        analysis: 'Таҳлил',
        history: 'Таърих',
        exit: 'Баромад',
        ambient_title: 'Равшании муҳит',
        ambient_desc: 'Равшаниро мисли мошини премиум танзим кунед',
        intensity: 'Шиддат',
        spread: 'Паҳншавӣ',
        mode_solid: 'Собит',
        mode_pulse: 'Пулс',
        mode_breathe: 'Нафас',
        color_title: 'Ранг',
        color_electric: 'Электрик',
        color_neon: 'Неон',
        color_cardinal: 'Кардинал',
        color_sunset: 'Ғуруб',
        color_lime: 'Лайм',
        color_aqua: 'Аква',
        color_fuchsia: 'Фуксия',
        color_gold: 'Тилло',
        color_ice: 'Ях',
        color_mint: 'Наъно',
        color_indigo: 'Индиго',
        color_rose: 'Гулӣ',
        auto_save: 'Нигоҳдории худкор',
        saved: 'Нигоҳ дошта шуд',
        status_signed: 'Имзошуда',
        status_pending: 'Дар интизорӣ',
        status_rejected: 'Радшуда',
        status_draft: 'Лоиҳа',
          strel: 'Тирҳо'
    },
    en: {
        brand_sub: 'EDMS',
        dashboard: 'Dashboard',
        documents: 'Documents',
        signatures: 'Signatures',
        counterparties: 'Team',
        reports: 'Reports',
        search_placeholder: 'Search...',
        choose_language: 'Choose language',
        notifications: 'Notifications',
        all_notifications: 'All notifications →',
        no_notifications: 'No notifications',
        notif_action_comment: 'left a comment on',
        notif_action_assign: 'assigned you a document',
        system: 'System',
        document: 'Document',
        profile: 'Profile',
        logout: 'Logout',
        workspace: 'Workspace',
        control_panel: 'Control Panel',
        incoming: 'Incoming',
        outgoing: 'Outgoing',
        on_signing: 'Awaiting Signature',
        archive: 'Archive',
        management: 'Management',
        analysis: 'Analysis',
        history: 'History',
        exit: 'Exit',
        ambient_title: 'Ambient Lighting',
        ambient_desc: 'Set up lighting like in a premium car',
        intensity: 'Intensity',
        spread: 'Spread',
        mode_solid: 'Static',
        mode_pulse: 'Pulse',
        mode_breathe: 'Breathe',
        color_title: 'Color',
        color_electric: 'Electric',
        color_neon: 'Neon',
        color_cardinal: 'Cardinal',
        color_sunset: 'Sunset',
        color_lime: 'Lime',
        color_aqua: 'Aqua',
        color_fuchsia: 'Fuchsia',
        color_gold: 'Gold',
        color_ice: 'Ice',
        color_mint: 'Mint',
        color_indigo: 'Indigo',
        color_rose: 'Rose',
        auto_save: 'Auto-save',
        saved: 'Saved',
        status_signed: 'Signed',
        status_pending: 'Pending',
        status_rejected: 'Rejected',
        status_draft: 'Draft',
         strel: 'Arrows'
    }
};

    const LANG_META = {
        ru: { flag: '🇷🇺', name: 'Русский', html: 'ru' },
        tj: { flag: '🇹🇯', name: 'Тоҷикӣ', html: 'tj' },
        en: { flag: '🇬🇧', name: 'English', html: 'en' }
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
        if (flagEl) flagEl.textContent = meta.flag;
        if (nameEl) nameEl.textContent = meta.name;

        document.querySelectorAll('.lang-option').forEach(opt => {
            opt.classList.toggle('active', opt.dataset.lang === lang);
        });

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

        window.dispatchEvent(new CustomEvent('docsign:lang-changed', {
            detail: { lang, dict }
        }));
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
        document.querySelectorAll('.side-item').forEach(b=>{
          b.addEventListener('click',()=>{
            document.querySelectorAll('.side-item').forEach(x=>x.classList.remove('active'));
            b.classList.add('active');
          });
        });
    });
</script>

@if(auth()->check())
<script>
    setInterval(function() {
        const currentUrl = window.location.href;

        // 1. Стоп на страницах создания и редактирования (чтобы не мешать заполнению форм)
        if (currentUrl.includes('/create') || currentUrl.includes('/edit')) {
            return;
        }

        // 2. Бесшумная проверка ТОЛЬКО колокольчика уведомлений
        fetch(window.location.href)
            .then(response => {
                if (!response.ok) throw new Error('Сеть не отвечает');
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

              const newBell = doc.getElementById('notifBtn');
const oldBell = document.getElementById('notifBtn');

                // Если нашли колокольчик на текущей странице и в скачанном ответе
                if (newBell && oldBell) {
                    // Проверяем, изменилось ли количество уведомлений
                    if (oldBell.innerHTML.trim() !== newBell.innerHTML.trim()) {
                        // Точечно меняем только колокольчик и цифру (включаются новые анимации!)
                        oldBell.innerHTML = newBell.innerHTML;
                        console.log('Колокольчик уведомлений обновлен! Пришли новые данные 🔔');
                    }
                }
            })
            .catch(error => console.error('Ошибка проверки уведомлений:', error));

    }, 5000); // Строго каждые 5 секунд
</script>
@endif
</body>
</html>