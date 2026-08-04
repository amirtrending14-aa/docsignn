@extends('layouts.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

@php
    $authUser = auth()->user();
    $isAdmin = $authUser && $authUser->isAdmin();
    $groupedByLevel = $departments->groupBy('level')->sortKeys();
    
    // ✅ ДОБАВЛЕНО: Подсчет общего количества участников во всех отделах
    $totalMembers = $departments->sum(fn($d) => $d->users->count());
    
    // Подсчитываем только тех, кто есть в выбранных отделах или всех, если ничего не выбрано
    $totalSelectedMembers = 0;
    foreach ($selectedDepartmentIds as $id) {
        $dept = $departments->firstWhere('id', $id);
        if ($dept) $totalSelectedMembers += $dept->users->count();
    }
    if (empty($selectedDepartmentIds)) {
        $totalSelectedMembers = $totalMembers;
    }

    $levelNames = [
        1 => 'Дивизион', 2 => 'Управление', 3 => 'Отдел',
        4 => 'Сектор',    5 => 'Группа',     6 => 'Подгруппа',
    ];

    // Готовим данные для JS (выбранные ID пользователей)
    $selectedUserIds = [];
    foreach ($selectedDepartmentIds as $deptId) {
        $dept = $departments->firstWhere('id', $deptId);
        if ($dept) {
            foreach ($dept->users as $u) {
                $selectedUserIds[] = $u->id;
            }
        }
    }
@endphp

<style>
    /* === БАЗОВЫЕ СТИЛИ ИЗ DEPARTMENTS.INDEX === */
    *{box-sizing:border-box;margin:0;padding:0;}
    :root{
        --room-w:300px; --room-h:286px; --sign-w:172px; --ppl-w:18px; --ppl-h:38px;
        --glow: 251, 191, 36; /* Оранжевый акцент для режима отделов */
        --text: #e7ecf3; --muted: #8892a6; --line: rgba(255,255,255,0.1); --bg: #0a0d14;
    }
    .dp{ min-height:100vh; padding:40px 24px 90px; font-family:'Inter',sans-serif; color:var(--text); width:100%; overflow-x:hidden; background: linear-gradient(180deg, #0a0d14 0%, #11151f 100%);}
    
    /* TOPBAR & STATS (Адаптировано под выбор) */
    .dp-top{ max-width:1360px; margin:0 auto 28px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
    .dp-top__l{ display:flex; align-items:center; gap:14px; }
    .dp-top__ic{ width:46px; height:46px; border-radius:12px; background:linear-gradient(135deg,rgba(var(--glow),0.9),rgba(var(--glow),0.5)); display:grid; place-items:center; box-shadow:0 4px 16px rgba(var(--glow),0.3); flex-shrink:0; }
    .dp-top__ic i{ font-size:20px; color:#0a0d14; }
    .dp-top__t{ font-size:22px; font-weight:800; letter-spacing:-.3px; color:var(--text); line-height:1.2; }
    .dp-top__s{ font-size:12px; color:var(--muted); font-weight:500; margin-top:2px; }
    
    .dp-stats{ max-width:1360px; margin:0 auto 36px; display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
    .dp-st{ background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.02)); border:1px solid var(--line); border-radius:14px; padding:18px 16px; display:flex; align-items:center; gap:14px; transition:all .3s; }
    .dp-st:hover{ transform:translateY(-3px); border-color:rgba(var(--glow),.3); box-shadow:0 12px 28px -12px rgba(var(--glow),.2); }
    .dp-st__ic{ width:36px; height:36px; border-radius:10px; display:grid; place-items:center; flex-shrink:0; background:rgba(var(--glow),.12); border:1px solid rgba(var(--glow),.25); }
    .dp-st__ic i{ font-size:16px; color:rgba(var(--glow),1); }
    .dp-st__v{ font-size:24px; font-weight:800; font-family:'JetBrains Mono',monospace; color:var(--text); line-height:1; }
    .dp-st__l{ font-size:10px; text-transform:uppercase; letter-spacing:.8px; color:var(--muted); font-weight:600; margin-top:3px; }

    /* BUILDING & ROOMS (Твой оригинальный CSS) */
    .iso-b{max-width:1360px;margin:0 auto;}
    .iso-lv{margin-bottom:40px;}
    .iso-lv:last-child{margin-bottom:0;}
    .iso-lv__hd{text-align:center;margin-bottom:6px;}
    .iso-lv__bg{ display:inline-flex;align-items:center;gap:10px; padding:9px 18px;border-radius:12px; background:rgba(255,255,255,.03);border:1px solid var(--line); font-size:13px;font-weight:800;color:var(--text); }
    .iso-lv__bg .cnt{ font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:700; padding:2px 8px;border-radius:6px; background:rgba(var(--glow),.12);color:rgba(var(--glow),1); }
    .iso-lnk{ width:2px;height:30px;margin:0 auto 30px; background:linear-gradient(180deg,rgba(var(--glow),.5),rgba(var(--glow),.08)); position:relative; }
    .iso-lnk::before,.iso-lnk::after{ content:'';position:absolute;left:50%;transform:translateX(-50%); width:8px;height:8px;border-radius:50%; background:var(--bg,#0a0d14);border:2px solid rgba(var(--glow),.6); }
    .iso-lnk::before{top:-4px;} .iso-lnk::after{bottom:-4px;}
    .iso-fl{ display:flex;flex-wrap:wrap;justify-content:center; gap:74px 44px;padding:88px 10px 30px; }

    .room{ --rc:var(--room-rgb,79,140,255); position:relative; width:var(--room-w);height:var(--room-h); flex:0 0 auto; transition:transform .4s cubic-bezier(.16,1,.3,1); animation:roomIn .5s ease both; cursor: pointer; }
    @keyframes roomIn{from{opacity:0;transform:translateY(14px) scale(.96);}to{opacity:1;transform:none;}}
    .room::before{ content:'';position:absolute;left:50%;top:74%;width:78%;height:30%; transform:translate(-50%,0); background:radial-gradient(ellipse at center,rgba(var(--rc),.30),rgba(var(--rc),.10) 45%,transparent 72%); filter:blur(8px);z-index:0;transition:all .4s; }
    .room:hover{transform:translateY(-8px);}
    .room:hover::before{top:78%;width:84%;filter:blur(11px);background:radial-gradient(ellipse at center,rgba(var(--rc),.45),rgba(var(--rc),.14) 45%,transparent 72%);}
    
    /* ✅ НОВЫЙ СТИЛЬ: Выбранная комната подсвечивается оранжевым */
    .room.selected { --rc: 251, 191, 36 !important; }
    .room.selected::before { background:radial-gradient(ellipse at center,rgba(251,191,36,.5),rgba(251,191,36,.2) 45%,transparent 72%); }

    .room__box{position:absolute;inset:0;width:100%;height:100%;display:block;z-index:1;overflow:visible;transition:filter .4s;}
    .room:hover .room__box{filter:brightness(1.12) saturate(1.1);}
    .room__box polygon,.room__box line,.room__box path{vector-effect:non-scaling-stroke;stroke-linejoin:round;stroke-linecap:round;}
    .room__box .face-floor{fill:rgba(var(--rc),.14);stroke:rgba(var(--rc),.55);stroke-width:1.4;}
    .room__box .face-left{fill:rgba(var(--rc),.34);stroke:rgba(var(--rc),.6);stroke-width:1.4;}
    .room__box .face-right{fill:rgba(var(--rc),.22);stroke:rgba(var(--rc),.55);stroke-width:1.4;}
    .room__box .face-thick-l{fill:rgba(var(--rc),.50);stroke:rgba(var(--rc),.6);stroke-width:1.2;}
    .room__box .face-thick-r{fill:rgba(var(--rc),.40);stroke:rgba(var(--rc),.6);stroke-width:1.2;}
    .room__box .grid-line{stroke:rgba(var(--rc),.16);stroke-width:1;fill:none;}
    .room__box .win{fill:rgba(180,220,255,.30);stroke:rgba(255,255,255,.35);stroke-width:1;}
    .room__box .fur-top{fill:rgba(255,255,255,.85);stroke:rgba(var(--rc),.5);stroke-width:1.1;}
    .room__box .fur-l{fill:rgba(255,255,255,.55);stroke:rgba(var(--rc),.5);stroke-width:1.1;}
    .room__box .fur-r{fill:rgba(255,255,255,.68);stroke:rgba(var(--rc),.5);stroke-width:1.1;}
    .room__box .board{fill:rgba(245,248,255,.92);stroke:rgba(var(--rc),.55);stroke-width:1.2;}
    .room__box .board-leg{stroke:rgba(120,130,150,.8);stroke-width:1.6;fill:none;}
    .room__box .board-line{stroke:rgba(var(--rc),.5);stroke-width:1;fill:none;}
    .room__box .pot{fill:rgba(120,90,70,.85);stroke:rgba(60,40,30,.6);stroke-width:1;}
    .room__box .leaf{fill:rgba(70,190,120,.92);stroke:rgba(30,120,70,.7);stroke-width:.8;}

    /* PEOPLE */
    .room__ppl{position:absolute;inset:0;z-index:3;pointer-events:none;}
    .ppl{ position:absolute; width:var(--ppl-w);height:var(--ppl-h); transform:translate(-50%,-100%); animation:pbr 4s ease-in-out infinite; }
    @keyframes pbr{0%,100%{transform:translate(-50%,-100%);}50%{transform:translate(-50%,calc(-100% - 1px));}}
    .ppl:nth-child(1){left:50%;top:58%;animation-delay:0s;} .ppl:nth-child(2){left:41%;top:62%;animation-delay:.4s;} .ppl:nth-child(3){left:59%;top:62%;animation-delay:.8s;} .ppl:nth-child(4){left:33%;top:68%;animation-delay:1.2s;} .ppl:nth-child(5){left:50%;top:67%;animation-delay:1.6s;} .ppl:nth-child(6){left:67%;top:68%;animation-delay:2s;} .ppl:nth-child(7){left:42%;top:75%;animation-delay:2.4s;} .ppl:nth-child(8){left:58%;top:75%;animation-delay:2.8s;} .ppl:nth-child(9){left:37%;top:81%;animation-delay:3.2s;} .ppl:nth-child(10){left:63%;top:81%;animation-delay:3.6s;}
    .ppl__shadow{position:absolute;left:50%;bottom:-1px;transform:translateX(-50%);width:14px;height:4px;border-radius:50%;background:rgba(0,0,0,.32);filter:blur(1.5px);}
    .ppl__head{position:absolute;left:50%;top:0;transform:translateX(-50%);width:10px;height:10px;border-radius:50%;background:#d4a574;box-shadow:0 1px 2px rgba(0,0,0,.35);z-index:2;}
    .ppl__head::before{content:'';position:absolute;top:-1px;left:1px;right:1px;height:5px;border-radius:5px 5px 0 0;background:#3a3a3a;}
    .ppl__body{position:absolute;left:50%;top:9px;transform:translateX(-50%);width:14px;height:22px;border-radius:6px 6px 4px 4px;background:#5a5f6b;box-shadow:0 2px 4px rgba(0,0,0,.3),inset 0 -4px 6px rgba(0,0,0,.15);z-index:1;}
    .ppl__body::before,.ppl__body::after{content:'';position:absolute;top:3px;width:4px;height:14px;border-radius:2px;background:inherit;box-shadow:inset 0 -2px 3px rgba(0,0,0,.15);}
    .ppl__body::before{left:-3px;transform:rotate(6deg);transform-origin:top center;} .ppl__body::after{right:-3px;transform:rotate(-6deg);transform-origin:top center;}
    .ppl__legs{position:absolute;left:50%;bottom:0;transform:translateX(-50%);width:12px;height:8px;display:flex;gap:2px;justify-content:center;}
    .ppl__legs::before,.ppl__legs::after{content:'';width:4px;height:8px;border-radius:2px 2px 1px 1px;background:#3d4149;}
    .ppl:nth-child(1) .ppl__body{background:#5a5f6b;} .ppl:nth-child(1) .ppl__head{background:#d4a574;} .ppl:nth-child(2) .ppl__body{background:#4e535e;} .ppl:nth-child(2) .ppl__head{background:#c9956a;} .ppl:nth-child(3) .ppl__body{background:#636873;} .ppl:nth-child(3) .ppl__head{background:#e0b48e;} .ppl:nth-child(4) .ppl__body{background:#52575f;} .ppl:nth-child(4) .ppl__head{background:#b8845c;} .ppl:nth-child(5) .ppl__body{background:#5e636e;} .ppl:nth-child(5) .ppl__head{background:#d4a574;} .ppl:nth-child(6) .ppl__body{background:#4a4f58;} .ppl:nth-child(6) .ppl__head{background:#c9956a;} .ppl:nth-child(7) .ppl__body{background:#666b76;} .ppl:nth-child(7) .ppl__head{background:#e0b48e;} .ppl:nth-child(8) .ppl__body{background:#555a64;} .ppl:nth-child(8) .ppl__head{background:#b8845c;} .ppl:nth-child(9) .ppl__body{background:#5c616c;} .ppl:nth-child(9) .ppl__head{background:#d4a574;} .ppl:nth-child(10) .ppl__body{background:#505560;} .ppl:nth-child(10) .ppl__head{background:#c9956a;}
    .ppl:nth-child(1) .ppl__head::before{background:#2c2c2c;} .ppl:nth-child(2) .ppl__head::before{background:#4a3728;} .ppl:nth-child(3) .ppl__head::before{background:#1a1a1a;} .ppl:nth-child(4) .ppl__head::before{background:#3d2b1f;} .ppl:nth-child(5) .ppl__head::before{background:#2c2c2c;} .ppl:nth-child(6) .ppl__head::before{background:#4a3728;} .ppl:nth-child(7) .ppl__head::before{background:#1a1a1a;} .ppl:nth-child(8) .ppl__head::before{background:#3d2b1f;} .ppl:nth-child(9) .ppl__head::before{background:#2c2c2c;} .ppl:nth-child(10) .ppl__head::before{background:#4a3728;}
    .ppl:nth-child(3n) .ppl__body::after{transform:rotate(-10deg);} .ppl:nth-child(3n) .ppl__body::before{transform:rotate(10deg);}
    @keyframes psw{0%,100%{transform:translate(-50%,-100%) rotate(0deg);}50%{transform:translate(-50%,calc(-100% - 1px)) rotate(.5deg);}} .ppl:nth-child(4n){animation-name:psw;}
    .ppl-badge{ position:absolute;left:50%;top:88%;transform:translate(-50%,-50%); font:700 10px 'JetBrains Mono',monospace;color:#0a0d14; background:rgba(var(--rc),.95);padding:2px 8px;border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.4);z-index:4; }
    .room--empty .room__ppl::after{ content:'';position:absolute;left:50%;top:64%;transform:translate(-50%,-50%); width:20px;height:20px;border:2px dashed rgba(var(--rc),.3);border-radius:50%;opacity:.5; }

    /* SIGN & ACTIONS */
    .room__sign{ position:absolute;left:50%;top:-66px;transform:translateX(-50%); width:var(--sign-w);z-index:5;text-align:center; background:linear-gradient(180deg,rgba(20,24,36,.97),rgba(12,14,22,.98)); border:1px solid rgba(var(--rc),.42);border-radius:14px;padding:10px 11px; box-shadow:0 16px 34px -14px rgba(0,0,0,.75),0 0 0 1px rgba(255,255,255,.02) inset; transition:all .35s cubic-bezier(.16,1,.3,1); }
    .room:hover .room__sign{border-color:rgba(var(--rc),.7);box-shadow:0 20px 40px -14px rgba(0,0,0,.8),0 0 22px rgba(var(--rc),.25);}
    .room__sign::after{content:'';position:absolute;left:50%;bottom:-15px;transform:translateX(-50%);width:2px;height:15px;background:linear-gradient(180deg,rgba(var(--rc),.6),rgba(var(--rc),.1));}
    .room__sign .ic{ width:36px;height:36px;margin:0 auto 6px;border-radius:10px; display:grid;place-items:center;font-size:18px; background:linear-gradient(135deg,rgba(var(--rc),.32),rgba(var(--rc),.08)); border:1px solid rgba(var(--rc),.4);transition:transform .35s; }
    .room:hover .room__sign .ic{transform:scale(1.05);}
    .room__sign .nm{font-size:13px;font-weight:800;color:var(--text);line-height:1.2;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .room__sign .lv{font-family:'JetBrains Mono',monospace;font-size:9px;font-weight:700;color:rgba(var(--rc),1);letter-spacing:.4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    
    /* ✅ КНОПКА ВЫБОРА СОТРУДНИКОВ В КОМНАТЕ */
    .room__select-btn {
        display:flex;align-items:center;justify-content:center;gap:6px;width:100%;
        margin-top:9px;padding:8px 8px;border-radius:8px;cursor:pointer;
        font-family:inherit;background:rgba(var(--rc),.10);
        border:1px solid rgba(var(--rc),.25);color:rgba(var(--rc),1);
        font-size:11px;font-weight:700;transition:all .2s;
    }
    .room__select-btn:hover{background:rgba(var(--rc),.22);box-shadow:0 0 14px rgba(var(--rc),.25);transform:translateY(-1px);}
    .room__select-btn.active { background: rgba(var(--glow), 0.25); border-color: rgba(var(--glow), 0.6); color: #fff; box-shadow: 0 0 16px rgba(var(--glow), 0.4); }
    .room__select-btn .num{font-family:'JetBrains Mono',monospace;}

    /* MODAL FOR USERS */
    .m-ov{ position:fixed;inset:0;background:rgba(4,6,12,.78);backdrop-filter:blur(6px); display:none;align-items:center;justify-content:center;z-index:9999;padding:20px; }
    .m-ov.active{display:flex;animation:mF .2s ease;}
    @keyframes mF{from{opacity:0;}to{opacity:1;}}
    .m-bx{ width:100%;max-width:480px;max-height:80vh; display:flex;flex-direction:column; background:linear-gradient(180deg,rgba(18,22,34,.98),rgba(12,14,22,.99)); border:1px solid rgba(var(--mg,251,191,36),.2);border-radius:16px; overflow:hidden;box-shadow:0 32px 64px -16px rgba(0,0,0,.6); animation:mP .25s cubic-bezier(.16,1,.3,1); }
    @keyframes mP{from{transform:translateY(16px) scale(.96);opacity:0;}to{transform:translateY(0) scale(1);opacity:1;}}
    .m-hd{ padding:18px 20px; background:linear-gradient(135deg,rgba(var(--mg,251,191,36),.2),rgba(168,85,247,.1)); display:flex;align-items:center;justify-content:space-between;gap:12px; border-bottom:1px solid rgba(255,255,255,.05); }
    .m-hd h4{margin:0 0 2px;font-size:15px;font-weight:800;color:#fff;} .m-hd span{font-size:11px;color:rgba(255,255,255,.55);font-weight:600;}
    .m-cl{ width:28px;height:28px;border-radius:7px; border:1px solid rgba(255,255,255,.15);background:rgba(0,0,0,.25); color:#fff;display:grid;place-items:center;cursor:pointer; transition:all .2s;flex-shrink:0;font-size:12px; }
    .m-cl:hover{background:rgba(248,113,113,.25);border-color:rgba(248,113,113,.4);}
    .m-bd{padding:10px;overflow-y:auto;}
    
    .m-user-row { display:flex;align-items:center;gap:12px;padding:10px;border-radius:10px;transition:background .2s; cursor: pointer; border: 1px solid transparent; }
    .m-user-row:hover { background:rgba(255,255,255,.03); }
    .m-user-row.selected { background: rgba(var(--mg,251,191,36), 0.08); border-color: rgba(var(--mg,251,191,36), 0.4); }
    
    .m-chk { width:20px;height:20px;border:2px solid rgba(255,255,255,0.2);border-radius:5px;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .2s;}
    .m-user-row.selected .m-chk { background: #fbbf24; border-color: #fbbf24; }
    .m-user-row.selected .m-chk::after { content: "\F26A"; font-family: "bootstrap-icons"; font-size: 12px; color: #0a0d14; font-weight: 900; }

    .m-av{ width:36px;height:36px;border-radius:9px;display:grid;place-items:center; font-size:12px;font-weight:800;color:#0f1219; background:linear-gradient(135deg,rgba(var(--mg,251,191,36),1),rgba(168,85,247,.7)); flex-shrink:0; }
    .m-inf{min-width:0;flex:1;} .m-nm{font-size:13px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .m-em{font-size:10px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .m-rl{ font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.3px; padding:3px 8px;border-radius:5px; background:rgba(var(--mg,251,191,36),.1);color:rgba(var(--mg,251,191,36),1); border:1px solid rgba(var(--mg,251,191,36),.2);flex-shrink:0; }
    .m-no{text-align:center;padding:36px 20px;color:var(--muted);font-size:12px;} .m-no i{font-size:28px;display:block;margin-bottom:8px;opacity:.4;}

    /* BOTTOM ACTION BAR */
    .action-bar {
        position: fixed; bottom: 0; left: 0; right: 0;
        background: rgba(10, 13, 20, 0.95); backdrop-filter: blur(10px);
        border-top: 1px solid rgba(255,255,255,0.1);
        padding: 16px 24px; z-index: 100;
        display: flex; align-items: center; justify-content: space-between;
        max-width: 1360px; margin: 0 auto; border-radius: 16px 16px 0 0;
    }
    .ab-info { display: flex; align-items: center; gap: 16px; }
    .ab-count { 
        font-family: 'JetBrains Mono', monospace; font-size: 20px; font-weight: 800; color: #fbbf24; 
        background: rgba(251,191,36,0.1); padding: 4px 12px; border-radius: 8px; border: 1px solid rgba(251,191,36,0.3);
    }
    .ab-text { font-size: 13px; color: #8892a6; }
    .ab-actions { display: flex; gap: 12px; }
    
    .btn-ab {
        padding: 12px 24px; border-radius: 10px; font: 700 12px 'Inter', sans-serif;
        cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        transition: all 0.2s ease; text-decoration: none; border: none;
    }
    .btn-ab-cancel { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); color: #8892a6; }
    .btn-ab-cancel:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .btn-ab-save { 
        background: linear-gradient(135deg, rgba(251, 191, 36, 0.9), rgba(251, 191, 36, 0.7)); 
        color: #0a0d14; box-shadow: 0 4px 16px rgba(251, 191, 36, 0.3); 
    }
    .btn-ab-save:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4); }
    .btn-ab-save:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    /* RESPONSIVE (Базовый адаптив из твоего кода) */
    @media(max-width:1100px){ .dp-stats{grid-template-columns:repeat(2,1fr);} :root{--room-w:270px;--room-h:258px;--sign-w:160px;} .iso-fl{gap:66px 36px;padding:82px 8px 26px;} }
    @media(max-width:992px){ .dp{padding:32px 20px 100px;} .dp-top{margin-bottom:24px;} .dp-top__t{font-size:20px;} .dp-top__ic{width:42px;height:42px;} .dp-top__ic i{font-size:18px;} .dp-stats{margin-bottom:30px;} .dp-st{padding:16px 14px;} .dp-st__v{font-size:22px;} .dp-st__ic{width:34px;height:34px;} .dp-st__ic i{font-size:15px;} :root{--room-w:250px;--room-h:238px;--sign-w:152px;--ppl-w:16px;--ppl-h:34px;} .iso-fl{gap:60px 30px;padding:78px 6px 24px;} .room__sign{top:-60px;padding:9px 10px;border-radius:12px;} .room__sign .ic{width:32px;height:32px;font-size:16px;margin-bottom:5px;} .room__sign .nm{font-size:12px;} .room__sign .lv{font-size:8px;} .room__select-btn{padding:7px 7px;font-size:10px;margin-top:7px;} }
    @media(max-width:768px){ .dp{padding:24px 16px 100px;} .dp-top{flex-direction:column;align-items:stretch;gap:12px;margin-bottom:20px;} .dp-top__l{justify-content:center;} .dp-top__t{font-size:19px;} .dp-top__s{font-size:11px;} .dp-stats{margin-bottom:26px;gap:10px;} .dp-st{padding:14px 12px;gap:12px;border-radius:12px;} .dp-st__v{font-size:20px;} .dp-st__l{font-size:9px;} .dp-st__ic{width:32px;height:32px;border-radius:9px;} .dp-st__ic i{font-size:14px;} :root{--room-w:230px;--room-h:220px;--sign-w:144px;--ppl-w:15px;--ppl-h:32px;} .iso-fl{gap:56px 24px;padding:72px 4px 22px;} .iso-lv{margin-bottom:32px;} .iso-lnk{height:24px;margin-bottom:24px;} .iso-lv__bg{padding:8px 14px;font-size:12px;border-radius:10px;} .iso-lv__bg .cnt{font-size:9px;padding:2px 6px;} .room__sign{top:-56px;padding:8px 9px;border-radius:11px;} .room__sign::after{height:12px;bottom:-12px;} .room__sign .ic{width:30px;height:30px;font-size:15px;margin-bottom:4px;border-radius:8px;} .room__sign .nm{font-size:11px;} .room__sign .lv{font-size:8px;} .room__select-btn{padding:6px 6px;font-size:10px;margin-top:6px;border-radius:7px;} .room__select-btn i{font-size:10px;} .ppl-badge{font-size:9px;padding:2px 6px;} .m-bx{max-width:380px;border-radius:14px;} .m-hd{padding:16px 18px;} .m-hd h4{font-size:14px;} .m-bd{padding:8px;} .m-user-row{padding:8px 9px;gap:9px;} .m-av{width:32px;height:32px;font-size:10px;} .m-nm{font-size:11px;} .m-em{font-size:9px;} .action-bar { padding: 14px 16px; flex-direction: column; gap: 12px; border-radius: 12px 12px 0 0; } .ab-actions { width: 100%; } .btn-ab { flex: 1; } }
    @media(max-width:576px){ .dp{padding:18px 12px 100px;} .dp-top{margin-bottom:18px;gap:10px;} .dp-top__t{font-size:17px;} .dp-top__s{font-size:10px;} .dp-top__ic{width:38px;height:38px;border-radius:10px;} .dp-top__ic i{font-size:16px;} .dp-top__l{gap:11px;} .dp-stats{margin-bottom:22px;gap:8px;} .dp-st{padding:12px 10px;gap:10px;border-radius:11px;} .dp-st__v{font-size:18px;} .dp-st__l{font-size:8px;letter-spacing:.6px;} .dp-st__ic{width:30px;height:30px;border-radius:8px;} .dp-st__ic i{font-size:13px;} :root{--room-w:200px;--room-h:192px;--sign-w:132px;--ppl-w:13px;--ppl-h:28px;} .iso-fl{gap:50px 18px;padding:64px 2px 18px;} .iso-lv{margin-bottom:28px;} .iso-lnk{height:20px;margin-bottom:20px;} .iso-lnk::before,.iso-lnk::after{width:6px;height:6px;} .iso-lv__bg{padding:7px 12px;font-size:11px;border-radius:9px;gap:8px;} .iso-lv__bg .cnt{font-size:8px;padding:1px 5px;border-radius:5px;} .room__sign{top:-50px;padding:7px 8px;border-radius:10px;} .room__sign::after{height:10px;bottom:-10px;} .room__sign .ic{width:28px;height:28px;font-size:14px;margin-bottom:4px;border-radius:7px;} .room__sign .nm{font-size:10px;} .room__sign .lv{font-size:7px;} .room__select-btn{padding:5px 5px;font-size:9px;margin-top:5px;border-radius:6px;gap:4px;} .room__select-btn i{font-size:9px;} .room__select-btn .num{font-size:9px;} .ppl-badge{font-size:8px;padding:1px 5px;border-radius:6px;} .ppl__head{width:8px;height:8px;} .ppl__head::before{height:4px;} .ppl__body{width:12px;height:18px;top:7px;border-radius:5px 5px 3px 3px;} .ppl__body::before,.ppl__body::after{width:3px;height:11px;} .ppl__legs{width:10px;height:6px;} .ppl__legs::before,.ppl__legs::after{width:3px;height:6px;} .ppl__shadow{width:11px;height:3px;} .m-ov{padding:14px;} .m-bx{max-width:340px;border-radius:13px;} .m-hd{padding:14px 16px;} .m-hd h4{font-size:13px;} .m-hd span{font-size:10px;} .m-cl{width:26px;height:26px;font-size:11px;} .m-bd{padding:7px;} .m-user-row{padding:7px 8px;gap:8px;border-radius:8px;} .m-av{width:30px;height:30px;font-size:9px;border-radius:8px;} .m-nm{font-size:11px;} .m-em{font-size:9px;} .m-rl{font-size:7px;padding:2px 5px;} .m-no{padding:28px 16px;font-size:11px;} .m-no i{font-size:24px;} .ab-count { font-size: 18px; padding: 3px 10px; } .ab-text { font-size: 12px; } .btn-ab { padding: 10px 16px; font-size: 11px; } }
</style>

<div class="dp">
    <div class="dp-top">
        <div class="dp-top__l">
            <a href="{{ $returnUrl ?? route('documents.create') }}" class="dp-top__ic" style="text-decoration:none;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="dp-top__t">Выбор получателей по отделам</h1>
                <p class="dp-top__s">Нажмите на комнату отдела, чтобы выбрать сотрудников</p>
            </div>
        </div>
    </div>

    <div class="dp-stats">
        <div class="dp-st">
            <div class="dp-st__ic"><i class="bi bi-buildings-fill"></i></div>
            <div><div class="dp-st__v">{{ $departments->count() }}</div><div class="dp-st__l">Всего отделов</div></div>
        </div>
        <div class="dp-st">
            <div class="dp-st__ic"><i class="bi bi-check-circle-fill"></i></div>
            <div><div class="dp-st__v" id="statSelectedDepts">{{ count($selectedDepartmentIds) }}</div><div class="dp-st__l">Выбрано отделов</div></div>
        </div>
        <div class="dp-st">
            <div class="dp-st__ic"><i class="bi bi-people-fill"></i></div>
            <div><div class="dp-st__v" id="statSelectedUsers">{{ count($selectedUserIds) }}</div><div class="dp-st__l">Выбрано людей</div></div>
        </div>
        <div class="dp-st">
            <div class="dp-st__ic"><i class="bi bi-lightning-charge-fill"></i></div>
            <div><div class="dp-st__v">{{ $totalMembers }}</div><div class="dp-st__l">Всего участников</div></div>
        </div>
    </div>

    <div class="iso-b">
        @foreach($groupedByLevel as $level => $levelDepts)
        @if(!$loop->first) <div class="iso-lnk"></div> @endif

        <div class="iso-lv">
            <div class="iso-lv__hd">
                <div class="iso-lv__bg">
                    <span>{{ $levelNames[$level] ?? 'Уровень ' . $level }}</span>
                    <span class="cnt">{{ $levelDepts->count() }}</span>
                </div>
            </div>

            <div class="iso-fl">
                @foreach($levelDepts as $dept)
                @php
                    $hex = ltrim($dept->color ?? '#4f8cff', '#');
                    $r = hexdec(substr($hex, 0, 2)); $g = hexdec(substr($hex, 2, 2)); $b = hexdec(substr($hex, 4, 2));
                    $rgb = "$r, $g, $b";
                    $isSelectedDept = in_array($dept->id, $selectedDepartmentIds);
                    
                    $membersPayload = $dept->users->map(function ($u) use ($selectedUserIds) {
                        $parts = explode(' ', $u->name ?? '?');
                        $initials = mb_strtoupper(collect($parts)->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode(''));
                        return [
                            'id'       => $u->id,
                            'name'     => $u->name,
                            'email'    => $u->email ?? null,
                            'role'     => $u->role_label,
                            'initials' => $initials ?: '?',
                            'selected' => in_array($u->id, $selectedUserIds)
                        ];
                    })->values();

                    $count = $dept->users->count();
                    $show  = min($count, 10);
                    $extra = $count - $show;
                @endphp

                {{-- ✅ Комната теперь кликабельна и имеет класс selected --}}
                <div class="room {{ $isSelectedDept ? 'selected' : '' }} {{ $count ? '' : 'room--empty' }}" 
                     style="--room-rgb: {{ $rgb }}" 
                     data-dept-id="{{ $dept->id }}"
                     onclick="openDeptModal(this)">

                    <svg class="room__box" viewBox="0 0 220 210" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                        <polygon class="face-left"  points="30,106 110,66 110,110 30,150"/>
                        <polygon class="face-right" points="110,66 190,106 190,150 110,110"/>
                        <polygon class="win" points="46,104 80,87 80,103 46,120"/>
                        <polygon class="face-floor" points="110,110 190,150 110,190 30,150"/>
                        <polygon class="face-thick-l" points="30,150 110,190 110,200 30,160"/>
                        <polygon class="face-thick-r" points="110,190 190,150 190,160 110,200"/>
                        <line class="grid-line" x1="70" y1="130" x2="150" y2="170"/>
                        <line class="grid-line" x1="150" y1="130" x2="70" y2="170"/>
                        <polygon class="pot" points="44,150 60,150 57,162 47,162"/>
                        <ellipse class="leaf" cx="48" cy="146" rx="5" ry="8" transform="rotate(-25 48 146)"/>
                        <ellipse class="leaf" cx="56" cy="144" rx="5" ry="9"/>
                        <ellipse class="leaf" cx="62" cy="147" rx="5" ry="8" transform="rotate(25 62 147)"/>
                        <line class="board-leg" x1="156" y1="118" x2="152" y2="134"/>
                        <line class="board-leg" x1="176" y1="128" x2="172" y2="144"/>
                        <polygon class="board" points="150,96 178,110 178,128 150,114"/>
                        <line class="board-line" x1="155" y1="104" x2="172" y2="112"/>
                        <line class="board-line" x1="155" y1="110" x2="168" y2="116"/>
                        <polygon class="fur-l" points="82,134 110,148 110,156 82,142"/>
                        <polygon class="fur-r" points="110,148 138,134 138,142 110,156"/>
                        <polygon class="fur-top" points="110,120 138,134 110,148 82,134"/>
                        <polygon class="fur-l" points="150,160 162,166 162,174 150,168"/>
                        <polygon class="fur-r" points="162,166 174,160 174,168 162,174"/>
                        <polygon class="fur-top" points="162,154 174,160 162,166 150,160"/>
                    </svg>

                    <div class="room__ppl">
                        @for($i = 0; $i < $show; $i++)
                        <span class="ppl"><span class="ppl__shadow"></span><span class="ppl__body"></span><span class="ppl__head"></span><span class="ppl__legs"></span></span>
                        @endfor
                        @if($extra > 0)<span class="ppl-badge">+{{ $extra }}</span>@endif
                    </div>

                    <div class="room__sign">
                        <div class="ic">{{ $dept->icon }}</div>
                        <h3 class="nm">{{ $dept->name }}</h3>
                        <div class="lv">L{{ $dept->level }}@if($dept->parent) · {{ $dept->parent->name }}@endif</div>

                        {{-- ✅ Кнопка выбора вместо просмотра --}}
                        <button type="button" class="room__select-btn {{ $isSelectedDept ? 'active' : '' }}"
                                data-dept-id="{{ $dept->id }}"
                                data-name="{{ $dept->name }}" 
                                data-glow="{{ $rgb }}"
                                data-users='@json($membersPayload)' 
                                onclick="event.stopPropagation(); openDeptModal(this.closest('.room'))">
                            <i class="bi bi-person-check-fill"></i>
                            <span class="num user-count-display">{{ $count }}</span>
                            <span class="btn-text">Выбрать</span>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        @if($departments->isEmpty())
        <div class="dp-empty" style="padding-top: 100px;">
            <div class="dp-empty__ic"><i class="bi bi-buildings"></i></div>
            <h3 class="dp-empty__t">Отделов пока нет</h3>
            <p class="dp-empty__d">Создайте отделы в структуре организации, чтобы выбрать получателей</p>
            @if($isAdmin)
            <a href="{{ route('departments.create') }}" class="dp-add"><i class="bi bi-plus-lg"></i>Создать первый отдел</a>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- НИЖНЯЯ ПАНЕЛЬ ДЕЙСТВИЙ --}}
<div class="action-bar">
    <div class="ab-info">
        <div class="ab-count" id="abCount">0</div>
        <div class="ab-text">сотрудников выбрано для отправки</div>
    </div>
    <div class="ab-actions">
        <a href="{{ $returnUrl ?? route('documents.create') }}" class="btn-ab btn-ab-cancel">
            <i class="bi bi-x-lg"></i> Отмена
        </a>
        <form action="{{ route('documents.select-by-department.store') }}" method="POST" id="saveForm" style="margin:0;">
            @csrf
            <input type="hidden" name="selected_departments" id="selectedDepartmentsInput" value="">
            <input type="hidden" name="selected_users" id="selectedUsersInput" value="">
            <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('documents.create') }}">
            <button type="submit" class="btn-ab btn-ab-save" id="saveBtn" disabled>
                <i class="bi bi-check-lg"></i> Сохранить выбор
            </button>
        </form>
    </div>
</div>

{{-- МОДАЛЬНОЕ ОКНО СОТРУДНИКОВ --}}
<div class="m-ov" id="mOverlay" onclick="if(event.target===this)closeMembers()">
    <div class="m-bx">
        <div class="m-hd">
            <div><h4 id="mTitle">—</h4><span id="mSub"></span></div>
            <button type="button" class="m-cl" onclick="closeMembers()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="m-bd" id="mBody"></div>
    </div>
</div>

<script>
// Глобальное хранилище выбранных ID
let selectedUserIds = new Set(@json($selectedUserIds));
let selectedDeptIds = new Set(@json($selectedDepartmentIds));

// Данные всех отделов и пользователей для быстрого доступа
const allDeptsData = {};
@foreach($departments as $d)
    allDeptsData[{{ $d->id }}] = @json($d->users->map(fn($u)=>['id'=>$u->id,'name'=>$u->name,'role'=>$u->role_label]));
@endforeach

function toggleUser(userId, deptId) {
    const idStr = String(userId);
    if (selectedUserIds.has(idStr)) {
        selectedUserIds.delete(idStr);
    } else {
        selectedUserIds.add(idStr);
        selectedDeptIds.add(String(deptId));
    }
    updateGlobalUI();
    updateModalUI(deptId); // Обновляем чекбоксы в открытом модале
}

function updateGlobalUI() {
    // Обновляем счетчики
    document.getElementById('statSelectedDepts').textContent = selectedDeptIds.size;
    document.getElementById('statSelectedUsers').textContent = selectedUserIds.size;
    document.getElementById('abCount').textContent = selectedUserIds.size;
    document.getElementById('saveBtn').disabled = selectedUserIds.size === 0;

    // Обновляем скрытые поля формы
    document.getElementById('selectedDepartmentsInput').value = Array.from(selectedDeptIds).join(',');
    document.getElementById('selectedUsersInput').value = Array.from(selectedUserIds).join(',');

    // Обновляем визуальное состояние комнат
    document.querySelectorAll('.room').forEach(room => {
        const deptId = String(room.dataset.deptId);
        const hasUsersInThisDept = [...selectedUserIds].some(uid => {
            return allDeptsData[deptId] && allDeptsData[deptId].some(u => String(u.id) === uid);
        });

        if (hasUsersInThisDept) {
            room.classList.add('selected');
            room.querySelector('.room__select-btn').classList.add('active');
            
            // Считаем сколько выбрано именно в этой комнате
            const countInRoom = allDeptsData[deptId] ? allDeptsData[deptId].filter(u => selectedUserIds.has(String(u.id))).length : 0;
            room.querySelector('.user-count-display').textContent = countInRoom;
            room.querySelector('.btn-text').textContent = 'Выбрано';
        } else {
            room.classList.remove('selected');
            room.querySelector('.room__select-btn').classList.remove('active');
            room.querySelector('.user-count-display').textContent = room.querySelector('.ppl-badge') ? 
                parseInt(room.querySelector('.ppl-badge').textContent.replace('+','')) + 10 : 
                room.querySelectorAll('.ppl').length;
            room.querySelector('.btn-text').textContent = 'Выбрать';
        }
    });
}

// Модальное окно
let currentModalDeptId = null;

function openDeptModal(roomEl) {
    const deptId = roomEl.dataset.deptId;
    currentModalDeptId = deptId;
    const btn = roomEl.querySelector('.room__select-btn');
    const name = btn.dataset.name;
    const glow = btn.dataset.glow;
    let users = []; try { users = JSON.parse(btn.dataset.users || '[]'); } catch(e) {}

    const ov = document.getElementById('mOverlay');
    ov.style.setProperty('--mg', glow);
    document.getElementById('mTitle').textContent = name;
    document.getElementById('mSub').textContent = users.length + ' сотрудников';
    
    renderModalBody(users);
    ov.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function renderModalBody(users) {
    const body = document.getElementById('mBody');
    if (!users.length) {
        body.innerHTML = '<div class="m-no"><i class="bi bi-person-x-fill"></i>В этом отделе пока нет участников</div>';
        return;
    }

    body.innerHTML = users.map(u => {
        const isSelected = selectedUserIds.has(String(u.id));
        return `
            <div class="m-user-row ${isSelected ? 'selected' : ''}" data-uid="${u.id}" onclick="toggleUser(${u.id}, ${currentModalDeptId})">
                <div class="m-chk"></div>
                <div class="m-av">${esc(u.initials || '?')}</div>
                <div class="m-inf">
                    <div class="m-nm">${esc(u.name || '—')}</div>
                    ${u.email ? `<div class="m-em">${esc(u.email)}</div>` : ''}
                </div>
                ${u.role ? `<span class="m-rl">${esc(u.role)}</span>` : ''}
            </div>
        `;
    }).join('');
}

function updateModalUI(deptId) {
    if (String(deptId) !== String(currentModalDeptId)) return;
    document.querySelectorAll('.m-user-row').forEach(row => {
        const uid = row.dataset.uid;
        if (selectedUserIds.has(uid)) row.classList.add('selected');
        else row.classList.remove('selected');
    });
}

function closeMembers() {
    document.getElementById('mOverlay').classList.remove('active');
    document.body.style.overflow = '';
    currentModalDeptId = null;
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMembers(); });

function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// Инициализация при загрузке
updateGlobalUI();
</script>
@endsection