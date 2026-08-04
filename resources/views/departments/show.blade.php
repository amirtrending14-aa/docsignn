@extends('layouts.admin')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@php
$authUser  = auth()->user();
$isAdmin   = $authUser && $authUser->isAdmin();
$ancestors = $ancestors ?? collect();

$hex = ltrim($department->color ?? '#4f8cff', '#');
$r = hexdec(substr($hex, 0, 2));
$g = hexdec(substr($hex, 2, 2));
$b = hexdec(substr($hex, 4, 2));
$rgb = "$r, $g, $b";

$memberCount = $department->users->count();
$showPeople  = min($memberCount, \App\Models\Department::maxUsers());
$extraPeople = $memberCount - $showPeople;
$maxUsers    = \App\Models\Department::maxUsers();
$fillPercent = round(($memberCount / $maxUsers) * 100);
@endphp

<style>
    *{box-sizing:border-box;margin:0;padding:0;}

    :root{
        --rm-w:440px;
        --rm-h:420px;
        --sign-w:230px;
        --ppl-w:24px;
        --ppl-h:50px;
    }

    .ds{
        min-height:100vh;
        padding:40px 24px 90px;
        color:var(--text,#e7ecf3);
        font-family:'Inter',sans-serif;
        background:var(--bg,#0a0d14);
        width:100%;overflow-x:hidden;
    }
    .ds-w{max-width:1200px;margin:0 auto;}

    /* ===== BREADCRUMB ===== */
    .bc{
        display:flex;align-items:center;gap:6px;flex-wrap:wrap;
        margin-bottom:20px;padding:10px 16px;border-radius:10px;
        background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);
    }
    .bc a{color:var(--muted);text-decoration:none;font-size:11px;font-weight:600;transition:color .2s;display:flex;align-items:center;gap:5px;}
    .bc a:hover{color:var(--text);}
    .bc .sp{color:var(--muted);font-size:10px;opacity:.5;}
    .bc .cr{color:var(--text);font-size:11px;font-weight:700;display:flex;align-items:center;gap:5px;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

    /* ===== TOPBAR ===== */
    .ds-top{display:flex;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap;}
    .ds-back{
        width:46px;height:46px;border-radius:13px;
        display:grid;place-items:center;
        background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);
        color:var(--text);text-decoration:none;transition:all .3s;flex-shrink:0;
    }
    .ds-back:hover{transform:translateX(-3px);border-color:rgba(255,255,255,.2);background:rgba(255,255,255,.06);}
    .ds-back:active{transform:scale(.93);}
    .ds-back i{font-size:18px;}
    .ds-info{flex:1;min-width:0;}
    .ds-title{font-size:24px;font-weight:800;letter-spacing:-.5px;color:var(--text);line-height:1.2;word-break:break-word;}
    .ds-sub{font-size:12px;color:var(--muted);font-weight:600;margin-top:4px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
    .lv-chip{
        display:inline-flex;align-items:center;gap:6px;
        padding:3px 10px;border-radius:7px;
        font-size:10px;font-weight:800;font-family:'JetBrains Mono',monospace;
        color:rgba(var(--rc),1);background:rgba(var(--rc),.12);
        border:1px solid rgba(var(--rc),.28);letter-spacing:.5px;
    }
    .lv-chip i{font-size:10px;opacity:.8;}
    .ds-acts{display:flex;gap:8px;flex-shrink:0;}
    .btn-a{
        position:relative;overflow:hidden;
        display:inline-flex;align-items:center;gap:8px;
        padding:12px 20px;border-radius:12px;text-decoration:none;
        font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;
        color:var(--text);background:rgba(255,255,255,.06);
        border:1px solid rgba(255,255,255,.1);
        transition:all .25s;cursor:pointer;
    }
    .btn-a:hover{transform:translateY(-2px);background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2);}
    .btn-a:active{transform:translateY(0) scale(.97);}
    .btn-a.dng{color:#ff6363;}
    .btn-a.dng:hover{border-color:rgba(255,99,99,.3);background:rgba(255,99,99,.06);}

    /* ===== ALERT ===== */
    .al{
        display:flex;align-items:center;gap:12px;
        margin-bottom:22px;padding:14px 18px;border-radius:12px;
        background:rgba(76,217,130,.06);border:1px solid rgba(76,217,130,.2);
        color:#4cd982;font-size:13px;font-weight:500;
    }
    .al .ic{display:grid;place-items:center;width:28px;height:28px;border-radius:9px;background:rgba(76,217,130,.15);border:1px solid rgba(76,217,130,.3);flex-shrink:0;}

    /* ===== ROOM HERO ===== */
    .rh{
        position:relative;padding:96px 20px 30px;border-radius:20px;
        background:radial-gradient(ellipse at 50% 30%,rgba(var(--rc),.10),rgba(255,255,255,.015) 60%);
        border:1px solid rgba(var(--rc),.18);margin-bottom:24px;overflow:hidden;
    }
    .rh::before{
        content:"";position:absolute;inset:0;
        background-image:linear-gradient(rgba(var(--rc),.04) 1px,transparent 1px),linear-gradient(90deg,rgba(var(--rc),.04) 1px,transparent 1px);
        background-size:34px 34px;
        mask-image:radial-gradient(ellipse at center,#000 30%,transparent 75%);
        pointer-events:none;
    }

    .room{
        --rc:{{ $rgb }};
        position:relative;width:var(--rm-w);height:var(--rm-h);
        margin:0 auto;animation:rmIn .6s cubic-bezier(.16,1,.3,1) both;
    }
    @keyframes rmIn{from{opacity:0;transform:translateY(20px) scale(.94);}to{opacity:1;transform:none;}}
    .room::before{
        content:"";position:absolute;left:50%;top:74%;width:80%;height:30%;
        transform:translate(-50%,0);
        background:radial-gradient(ellipse at center,rgba(var(--rc),.35),rgba(var(--rc),.12) 45%,transparent 72%);
        filter:blur(10px);z-index:0;
    }

    .room__box{position:absolute;inset:0;width:100%;height:100%;display:block;z-index:1;overflow:visible;}
    .room__box polygon,.room__box line{vector-effect:non-scaling-stroke;stroke-linejoin:round;stroke-linecap:round;}
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

    /* ===== PEOPLE ===== */
    .room__ppl{position:absolute;inset:0;z-index:3;pointer-events:none;}
    .ppl{
        position:absolute;width:var(--ppl-w);height:var(--ppl-h);
        transform:translate(-50%,-100%);
        animation:pbr 4.2s ease-in-out infinite;
    }
    @keyframes pbr{0%,100%{transform:translate(-50%,-100%);}50%{transform:translate(-50%,calc(-100% - 1.5px));}}

    .ppl:nth-child(1){left:50%;top:58%;animation-delay:0s;}
    .ppl:nth-child(2){left:41%;top:62%;animation-delay:.42s;}
    .ppl:nth-child(3){left:59%;top:62%;animation-delay:.84s;}
    .ppl:nth-child(4){left:33%;top:68%;animation-delay:1.26s;}
    .ppl:nth-child(5){left:50%;top:67%;animation-delay:1.68s;}
    .ppl:nth-child(6){left:67%;top:68%;animation-delay:2.1s;}
    .ppl:nth-child(7){left:42%;top:75%;animation-delay:2.52s;}
    .ppl:nth-child(8){left:58%;top:75%;animation-delay:2.94s;}
    .ppl:nth-child(9){left:37%;top:81%;animation-delay:3.36s;}
    .ppl:nth-child(10){left:63%;top:81%;animation-delay:3.78s;}

    .ppl__shadow{position:absolute;left:50%;bottom:-1px;transform:translateX(-50%);width:18px;height:5px;border-radius:50%;background:rgba(0,0,0,.32);filter:blur(1.5px);}
    .ppl__head{position:absolute;left:50%;top:0;transform:translateX(-50%);width:13px;height:13px;border-radius:50%;background:#d4a574;box-shadow:0 1px 2px rgba(0,0,0,.35);z-index:2;}
    .ppl__head::before{content:'';position:absolute;top:-1px;left:1px;right:1px;height:6px;border-radius:6px 6px 0 0;background:#3a3a3a;}
    .ppl__body{position:absolute;left:50%;top:12px;transform:translateX(-50%);width:18px;height:28px;border-radius:7px 7px 5px 5px;background:#5a5f6b;box-shadow:0 2px 4px rgba(0,0,0,.3),inset 0 -4px 6px rgba(0,0,0,.15);z-index:1;}
    .ppl__body::before,.ppl__body::after{content:'';position:absolute;top:3px;width:5px;height:18px;border-radius:3px;background:inherit;box-shadow:inset 0 -2px 3px rgba(0,0,0,.15);}
    .ppl__body::before{left:-4px;transform:rotate(5deg);transform-origin:top center;}
    .ppl__body::after{right:-4px;transform:rotate(-5deg);transform-origin:top center;}
    .ppl__legs{position:absolute;left:50%;bottom:0;transform:translateX(-50%);width:14px;height:10px;display:flex;gap:2px;justify-content:center;}
    .ppl__legs::before,.ppl__legs::after{content:'';width:5px;height:10px;border-radius:2px 2px 1px 1px;background:#3d4149;}

    .ppl:nth-child(1) .ppl__body{background:#5a5f6b;} .ppl:nth-child(1) .ppl__head{background:#d4a574;}
    .ppl:nth-child(2) .ppl__body{background:#4e535e;} .ppl:nth-child(2) .ppl__head{background:#c9956a;}
    .ppl:nth-child(3) .ppl__body{background:#636873;} .ppl:nth-child(3) .ppl__head{background:#e0b48e;}
    .ppl:nth-child(4) .ppl__body{background:#52575f;} .ppl:nth-child(4) .ppl__head{background:#b8845c;}
    .ppl:nth-child(5) .ppl__body{background:#5e636e;} .ppl:nth-child(5) .ppl__head{background:#d4a574;}
    .ppl:nth-child(6) .ppl__body{background:#4a4f58;} .ppl:nth-child(6) .ppl__head{background:#c9956a;}
    .ppl:nth-child(7) .ppl__body{background:#666b76;} .ppl:nth-child(7) .ppl__head{background:#e0b48e;}
    .ppl:nth-child(8) .ppl__body{background:#555a64;} .ppl:nth-child(8) .ppl__head{background:#b8845c;}
    .ppl:nth-child(9) .ppl__body{background:#5c616c;} .ppl:nth-child(9) .ppl__head{background:#d4a574;}
    .ppl:nth-child(10) .ppl__body{background:#505560;} .ppl:nth-child(10) .ppl__head{background:#c9956a;}

    .ppl:nth-child(1) .ppl__head::before{background:#2c2c2c;}
    .ppl:nth-child(2) .ppl__head::before{background:#4a3728;}
    .ppl:nth-child(3) .ppl__head::before{background:#1a1a1a;}
    .ppl:nth-child(4) .ppl__head::before{background:#3d2b1f;}
    .ppl:nth-child(5) .ppl__head::before{background:#2c2c2c;}
    .ppl:nth-child(6) .ppl__head::before{background:#4a3728;}
    .ppl:nth-child(7) .ppl__head::before{background:#1a1a1a;}
    .ppl:nth-child(8) .ppl__head::before{background:#3d2b1f;}
    .ppl:nth-child(9) .ppl__head::before{background:#2c2c2c;}
    .ppl:nth-child(10) .ppl__head::before{background:#4a3728;}

    .ppl:nth-child(3n) .ppl__body::after{transform:rotate(-9deg);}
    .ppl:nth-child(3n) .ppl__body::before{transform:rotate(9deg);}
    @keyframes psw{0%,100%{transform:translate(-50%,-100%) rotate(0deg);}50%{transform:translate(-50%,calc(-100% - 1px)) rotate(.4deg);}}
    .ppl:nth-child(4n){animation-name:psw;}

    .ppl-badge{position:absolute;left:50%;top:88%;transform:translate(-50%,-50%);font:700 12px 'JetBrains Mono',monospace;color:#0a0d14;background:rgba(var(--rc),.95);padding:3px 10px;border-radius:9px;box-shadow:0 2px 6px rgba(0,0,0,.4);z-index:4;}
    .room--empty .room__ppl::after{content:'';position:absolute;left:50%;top:64%;transform:translate(-50%,-50%);width:26px;height:26px;border:2px dashed rgba(var(--rc),.3);border-radius:50%;opacity:.5;}

    /* ===== ROOM SIGN ===== */
    .room__sign{
        position:absolute;left:50%;top:-72px;transform:translateX(-50%);
        width:var(--sign-w);z-index:5;text-align:center;
        background:linear-gradient(180deg,rgba(20,24,36,.97),rgba(12,14,22,.98));
        border:1px solid rgba(var(--rc),.45);border-radius:16px;padding:14px;
        box-shadow:0 18px 40px -14px rgba(0,0,0,.8);
    }
    .room__sign::after{content:"";position:absolute;left:50%;bottom:-16px;transform:translateX(-50%);width:2px;height:16px;background:linear-gradient(180deg,rgba(var(--rc),.6),rgba(var(--rc),.1));}
    .room__sign .ic{width:46px;height:46px;margin:0 auto 8px;border-radius:13px;display:grid;place-items:center;font-size:24px;background:linear-gradient(135deg,rgba(var(--rc),.32),rgba(var(--rc),.08));border:1px solid rgba(var(--rc),.4);}
    .room__sign .nm{font-size:16px;font-weight:800;color:var(--text);line-height:1.2;margin:0 0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .room__sign .lv{font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:700;color:rgba(var(--rc),1);letter-spacing:.4px;}

    .rh-desc{max-width:640px;margin:26px auto 0;text-align:center;font-size:14px;color:var(--muted);line-height:1.7;}
    .rh-desc.emp{font-style:italic;opacity:.6;}

    /* ===== STATS ===== */
    .st-g{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:28px;}
    .st-t{
        position:relative;padding:22px;border-radius:18px;
        background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);
        transition:all .3s;overflow:hidden;
    }
    .st-t:hover{transform:translateY(-3px);border-color:rgba(var(--rc),.25);}
    .st-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
    .st-ch{
        display:inline-flex;align-items:center;gap:6px;
        padding:4px 10px;border-radius:8px;
        font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
        background:rgba(255,255,255,.04);color:var(--muted);
        border:1px solid rgba(255,255,255,.06);
    }
    .st-ch i{font-size:11px;}
    .st-n{font-size:36px;font-weight:800;letter-spacing:-1.2px;line-height:1;margin:0 0 6px;color:var(--text);font-family:'JetBrains Mono',monospace;}
    .st-n small{font-size:16px;color:var(--muted);font-weight:700;}
    .st-l{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;}
    .st-bar{height:5px;border-radius:3px;background:rgba(255,255,255,.06);margin-top:12px;overflow:hidden;}
    .st-bar span{display:block;height:100%;border-radius:3px;background:linear-gradient(90deg,rgba(var(--rc),.7),rgba(var(--rc),1));transition:width .6s;}

    /* ===== SECTIONS ===== */
    .sec{margin-bottom:32px;}
    .sec-t{display:flex;align-items:center;gap:12px;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,.06);}
    .sec-ic{width:38px;height:38px;border-radius:12px;display:grid;place-items:center;font-size:17px;background:rgba(var(--rc),.1);border:1px solid rgba(var(--rc),.22);color:rgba(var(--rc),1);flex-shrink:0;}
    .sec-t h2{margin:0;font-size:16px;font-weight:800;letter-spacing:-.3px;color:var(--text);}
    .sec-c{margin-left:auto;display:inline-flex;align-items:center;justify-content:center;min-width:24px;padding:2px 9px;border-radius:7px;font-size:11px;font-weight:700;color:var(--text);background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);font-family:'JetBrains Mono',monospace;}

    /* ===== ASSIGN ===== */
    .asg{padding:18px;border-radius:14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);margin-bottom:18px;}
    .asg-l{font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);display:flex;align-items:center;gap:8px;margin-bottom:12px;}
    .asg-l i{opacity:.7;}
    .asg-r{display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
    .asg-s{
        padding:11px 14px;border-radius:10px;
        background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);
        color:var(--text);font-size:13px;font-weight:500;font-family:'Inter',sans-serif;
        outline:none;transition:all .2s;appearance:none;cursor:pointer;
        flex:1;min-width:240px;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%238892a6' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat:no-repeat;background-position:right 14px center;padding-right:36px;
    }
    .asg-s option{background:#0a0d14;color:var(--text);}
    .asg-s:focus{border-color:rgba(var(--rc),.5);box-shadow:0 0 0 3px rgba(var(--rc),.1);background-color:rgba(0,0,0,.5);}
    .btn-ad{
        display:inline-flex;align-items:center;gap:8px;
        padding:11px 22px;border:1px solid rgba(var(--rc),.3);border-radius:10px;
        cursor:pointer;font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;
        color:rgba(var(--rc),1);background:rgba(var(--rc),.1);
        transition:all .25s;white-space:nowrap;
    }
    .btn-ad:hover{transform:translateY(-2px);background:rgba(var(--rc),.2);border-color:rgba(var(--rc),.5);}
    .btn-ad:active{transform:translateY(0) scale(.97);}
    .asg-n{display:flex;align-items:center;gap:6px;margin-top:10px;font-size:11px;color:var(--muted);flex-wrap:wrap;}
    .asg-n i{font-size:12px;color:rgba(var(--rc),1);flex-shrink:0;}
    .asg-f{
        display:flex;align-items:center;gap:10px;
        padding:14px 16px;border-radius:12px;
        background:rgba(251,191,36,.06);border:1px solid rgba(251,191,36,.2);
        color:#fbbf24;font-size:12px;font-weight:600;
    }
    .asg-f i{font-size:16px;flex-shrink:0;}

    /* ===== TEAM LIST ===== */
    .tm-l{display:flex;flex-direction:column;gap:8px;}
    .mb{
        display:flex;align-items:center;gap:14px;
        padding:14px 18px;border-radius:14px;
        background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);
        transition:all .25s;
    }
    .mb:hover{transform:translateX(4px);border-color:rgba(var(--rc),.25);background:rgba(255,255,255,.04);}
    .mb-av{
        width:46px;height:46px;border-radius:12px;
        display:grid;place-items:center;color:#0a0d14;
        font-weight:800;font-size:17px;
        background:linear-gradient(135deg,rgba(var(--rc),1),rgba(var(--rc),.6));
        flex-shrink:0;
    }
    .mb-inf{flex:1;min-width:0;}
    .mb-nm{display:block;font-size:14px;font-weight:700;color:var(--text);line-height:1.3;}
    .mb-em{display:block;font-size:12px;color:var(--muted);margin-top:2px;word-break:break-all;}
    .mb-rl{
        display:inline-flex;align-items:center;gap:5px;margin-top:6px;
        font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;
        color:rgba(var(--rc),1);background:rgba(var(--rc),.1);
        border:1px solid rgba(var(--rc),.25);padding:3px 9px;border-radius:7px;
    }
    .mb-rl i{font-size:10px;}
    .btn-rm{
        display:inline-flex;align-items:center;justify-content:center;
        width:34px;height:34px;border-radius:10px;
        border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);
        color:var(--muted);cursor:pointer;font-size:14px;
        transition:all .25s;flex-shrink:0;
    }
    .btn-rm:hover{color:#ff6363;border-color:rgba(255,99,99,.3);background:rgba(255,99,99,.06);}
    .btn-rm:active{transform:scale(.9);}

    /* ===== EMPTY ===== */
    .emp-st{text-align:center;padding:40px 20px;border:1px dashed rgba(255,255,255,.08);border-radius:14px;background:rgba(255,255,255,.015);}
    .emp-st .ic{font-size:32px;margin-bottom:10px;opacity:.4;display:block;}
    .emp-st p{margin:0;font-size:12px;font-style:italic;color:var(--muted);}

    /* ===== CHILDREN ===== */
    .ch-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;}
    .ch-c{
        display:flex;align-items:center;gap:12px;
        padding:16px;border-radius:14px;text-decoration:none;
        background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);
        transition:all .25s;
    }
    .ch-c:hover{transform:translateY(-3px);border-color:rgba(var(--rc),.3);background:rgba(255,255,255,.04);}
    .ch-ic{width:40px;height:40px;border-radius:11px;display:grid;place-items:center;font-size:18px;background:rgba(var(--rc),.1);border:1px solid rgba(var(--rc),.22);flex-shrink:0;}
    .ch-bd{flex:1;min-width:0;}
    .ch-nm{font-size:13px;font-weight:700;color:var(--text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:0;}
    .ch-mt{font-size:10px;font-weight:600;color:var(--muted);margin-top:3px;display:flex;align-items:center;gap:8px;}
    .ch-mt i{font-size:10px;opacity:.7;}
    .ch-ar{color:var(--muted);font-size:13px;transition:transform .25s;flex-shrink:0;}
    .ch-c:hover .ch-ar{transform:translateX(3px);color:rgba(var(--rc),1);}

    /* ============================================================
       RESPONSIVE
       ============================================================ */

    @media(max-width:1100px){
        .st-g{grid-template-columns:repeat(2,1fr);}
        :root{--rm-w:380px;--rm-h:362px;--sign-w:210px;--ppl-w:22px;--ppl-h:46px;}
    }

    @media(max-width:992px){
        .ds{padding:32px 20px 80px;}
        .ds-title{font-size:22px;}
        .ds-back{width:42px;height:42px;}
        .ds-back i{font-size:16px;}
        :root{--rm-w:350px;--rm-h:334px;--sign-w:200px;--ppl-w:20px;--ppl-h:42px;}
        .rh{padding:88px 16px 26px;}
        .room__sign{top:-66px;padding:12px;}
        .room__sign .ic{width:42px;height:42px;font-size:22px;}
        .room__sign .nm{font-size:15px;}
        .st-t{padding:20px;}
        .st-n{font-size:32px;}
    }

    @media(max-width:768px){
        .ds{padding:24px 16px 70px;}
        .ds-top{gap:12px;margin-bottom:20px;}
        .ds-title{font-size:19px;}
        .ds-sub{font-size:11px;}
        .ds-back{width:40px;height:40px;border-radius:11px;}
        .ds-back i{font-size:15px;}
        .lv-chip{font-size:9px;padding:3px 8px;}

        .ds-acts{width:100%;}
        .btn-a{flex:1;justify-content:center;padding:11px 16px;font-size:10px;}

        .bc{padding:9px 14px;margin-bottom:16px;}
        .bc a{font-size:10px;}
        .bc .cr{font-size:10px;max-width:130px;}

        .al{padding:12px 14px;font-size:12px;margin-bottom:18px;}
        .al .ic{width:24px;height:24px;border-radius:7px;}

        :root{--rm-w:300px;--rm-h:286px;--sign-w:180px;--ppl-w:18px;--ppl-h:38px;}
        .rh{padding:78px 12px 22px;border-radius:16px;margin-bottom:20px;}
        .room__sign{top:-60px;padding:11px;border-radius:13px;}
        .room__sign::after{height:13px;bottom:-13px;}
        .room__sign .ic{width:38px;height:38px;font-size:19px;border-radius:11px;margin-bottom:6px;}
        .room__sign .nm{font-size:13px;}
        .room__sign .lv{font-size:9px;}
        .rh-desc{font-size:13px;margin-top:20px;}

        .ppl__head{width:11px;height:11px;}
        .ppl__head::before{height:5px;}
        .ppl__body{width:15px;height:24px;top:10px;border-radius:6px 6px 4px 4px;}
        .ppl__body::before,.ppl__body::after{width:4px;height:15px;}
        .ppl__legs{width:12px;height:8px;}
        .ppl__legs::before,.ppl__legs::after{width:4px;height:8px;}
        .ppl__shadow{width:15px;height:4px;}
        .ppl-badge{font-size:10px;padding:2px 8px;}

        .st-g{gap:10px;margin-bottom:24px;}
        .st-t{padding:18px;border-radius:14px;}
        .st-n{font-size:28px;}
        .st-n small{font-size:14px;}
        .st-l{font-size:10px;}
        .st-ch{font-size:9px;padding:3px 8px;}
        .st-bar{margin-top:10px;}

        .sec{margin-bottom:26px;}
        .sec-t{gap:10px;margin-bottom:14px;padding-bottom:10px;}
        .sec-ic{width:34px;height:34px;font-size:15px;border-radius:10px;}
        .sec-t h2{font-size:15px;}
        .sec-c{font-size:10px;padding:2px 7px;}

        .asg{padding:16px;border-radius:12px;}
        .asg-l{font-size:9px;margin-bottom:10px;}
        .asg-s{padding:10px 13px;font-size:12px;border-radius:9px;min-width:200px;}
        .btn-ad{padding:10px 18px;font-size:10px;border-radius:9px;}
        .asg-n{font-size:10px;}
        .asg-f{padding:12px 14px;font-size:11px;border-radius:10px;}

        .mb{padding:12px 14px;gap:12px;border-radius:12px;}
        .mb-av{width:40px;height:40px;font-size:15px;border-radius:10px;}
        .mb-nm{font-size:13px;}
        .mb-em{font-size:11px;}
        .mb-rl{font-size:9px;padding:2px 7px;margin-top:5px;}
        .btn-rm{width:32px;height:32px;border-radius:9px;font-size:13px;}

        .emp-st{padding:32px 16px;}
        .emp-st .ic{font-size:28px;}
        .emp-st p{font-size:11px;}

        .ch-g{grid-template-columns:1fr 1fr;gap:10px;}
        .ch-c{padding:14px;border-radius:12px;gap:10px;}
        .ch-ic{width:36px;height:36px;font-size:16px;border-radius:9px;}
        .ch-nm{font-size:12px;}
        .ch-mt{font-size:9px;}
        .ch-ar{font-size:12px;}
    }

    @media(max-width:576px){
        .ds{padding:18px 12px 60px;}
        .ds-top{gap:10px;margin-bottom:18px;}
        .ds-title{font-size:17px;}
        .ds-sub{font-size:10px;gap:6px;}
        .ds-back{width:38px;height:38px;border-radius:10px;}
        .ds-back i{font-size:14px;}
        .lv-chip{font-size:8px;padding:2px 7px;gap:4px;}
        .lv-chip i{font-size:8px;}
        .btn-a{padding:10px 14px;font-size:9px;gap:6px;border-radius:10px;}
        .btn-a i{font-size:11px;}

        .bc{padding:8px 12px;margin-bottom:14px;gap:5px;border-radius:9px;}
        .bc a{font-size:9px;gap:4px;}
        .bc a i{font-size:9px;}
        .bc .sp{font-size:8px;}
        .bc .cr{font-size:9px;max-width:110px;}

        .al{padding:11px 12px;font-size:11px;margin-bottom:16px;gap:10px;border-radius:10px;}
        .al .ic{width:22px;height:22px;border-radius:6px;font-size:11px;}

        :root{--rm-w:260px;--rm-h:248px;--sign-w:160px;--ppl-w:15px;--ppl-h:32px;}
        .rh{padding:68px 10px 18px;border-radius:14px;margin-bottom:18px;}
        .room__sign{top:-52px;padding:9px;border-radius:11px;}
        .room__sign::after{height:11px;bottom:-11px;}
        .room__sign .ic{width:34px;height:34px;font-size:17px;border-radius:9px;margin-bottom:5px;}
        .room__sign .nm{font-size:12px;}
        .room__sign .lv{font-size:8px;}
        .rh-desc{font-size:12px;margin-top:16px;line-height:1.6;}

        .ppl__head{width:9px;height:9px;}
        .ppl__head::before{height:4px;left:1px;right:1px;}
        .ppl__body{width:13px;height:20px;top:8px;border-radius:5px 5px 4px 4px;}
        .ppl__body::before,.ppl__body::after{width:4px;height:13px;top:2px;}
        .ppl__body::before{left:-3px;} .ppl__body::after{right:-3px;}
        .ppl__legs{width:11px;height:7px;gap:1px;}
        .ppl__legs::before,.ppl__legs::after{width:4px;height:7px;}
        .ppl__shadow{width:13px;height:4px;}
        .ppl-badge{font-size:9px;padding:2px 6px;border-radius:7px;}
        .room--empty .room__ppl::after{width:20px;height:20px;}

        .st-g{gap:8px;margin-bottom:20px;}
        .st-t{padding:16px;border-radius:12px;}
        .st-hd{margin-bottom:10px;}
        .st-n{font-size:24px;letter-spacing:-.8px;}
        .st-n small{font-size:12px;}
        .st-l{font-size:9px;letter-spacing:.8px;}
        .st-ch{font-size:8px;padding:3px 7px;gap:4px;}
        .st-ch i{font-size:9px;}
        .st-bar{height:4px;margin-top:8px;}

        .sec{margin-bottom:22px;}
        .sec-t{gap:9px;margin-bottom:12px;padding-bottom:9px;}
        .sec-ic{width:32px;height:32px;font-size:14px;border-radius:9px;}
        .sec-t h2{font-size:14px;}
        .sec-c{font-size:9px;padding:2px 6px;min-width:20px;}

        .asg{padding:14px;border-radius:11px;margin-bottom:14px;}
        .asg-l{font-size:9px;margin-bottom:9px;gap:6px;}
        .asg-r{gap:8px;}
        .asg-s{padding:10px 12px;font-size:12px;border-radius:9px;min-width:0;width:100%;padding-right:32px;}
        .btn-ad{padding:10px 16px;font-size:10px;border-radius:9px;width:100%;justify-content:center;}
        .asg-n{font-size:9px;margin-top:8px;}
        .asg-f{padding:11px 12px;font-size:10px;border-radius:9px;gap:8px;}
        .asg-f i{font-size:14px;}

        .tm-l{gap:7px;}
        .mb{padding:11px 12px;gap:11px;border-radius:11px;}
        .mb-av{width:38px;height:38px;font-size:14px;border-radius:9px;}
        .mb-nm{font-size:12px;}
        .mb-em{font-size:10px;}
        .mb-rl{font-size:8px;padding:2px 6px;margin-top:4px;gap:4px;border-radius:6px;}
        .mb-rl i{font-size:8px;}
        .btn-rm{width:30px;height:30px;border-radius:8px;font-size:12px;}

        .emp-st{padding:28px 14px;border-radius:12px;}
        .emp-st .ic{font-size:26px;margin-bottom:8px;}
        .emp-st p{font-size:11px;}

        .ch-g{grid-template-columns:1fr;gap:8px;}
        .ch-c{padding:13px;border-radius:11px;gap:10px;}
        .ch-ic{width:34px;height:34px;font-size:15px;border-radius:9px;}
        .ch-nm{font-size:12px;}
        .ch-mt{font-size:9px;gap:6px;}
        .ch-ar{font-size:11px;}
    }

    @media(max-width:480px){
        .ds{padding:14px 10px 50px;}
        .ds-top{gap:9px;margin-bottom:16px;}
        .ds-title{font-size:16px;}
        .ds-sub{font-size:9px;gap:5px;margin-top:3px;}
        .ds-back{width:34px;height:34px;border-radius:9px;}
        .ds-back i{font-size:13px;}
        .lv-chip{font-size:8px;padding:2px 6px;gap:3px;border-radius:6px;}
        .btn-a{padding:9px 12px;font-size:9px;gap:5px;border-radius:9px;}
        .btn-a i{font-size:10px;}

        .bc{padding:7px 10px;margin-bottom:12px;gap:4px;border-radius:8px;}
        .bc a{font-size:9px;}
        .bc .cr{font-size:9px;max-width:100px;}

        .al{padding:10px 11px;font-size:10px;margin-bottom:14px;gap:8px;border-radius:9px;}
        .al .ic{width:20px;height:20px;border-radius:6px;}
        .al .ic i{font-size:10px;}

        :root{--rm-w:220px;--rm-h:210px;--sign-w:140px;--ppl-w:13px;--ppl-h:28px;}
        .rh{padding:58px 8px 16px;border-radius:12px;margin-bottom:16px;}
        .rh::before{background-size:26px 26px;}
        .room__sign{top:-46px;padding:8px;border-radius:10px;}
        .room__sign::after{height:9px;bottom:-9px;}
        .room__sign .ic{width:30px;height:30px;font-size:15px;border-radius:8px;margin-bottom:4px;}
        .room__sign .nm{font-size:11px;}
        .room__sign .lv{font-size:7px;}
        .rh-desc{font-size:11px;margin-top:14px;}

        .ppl__head{width:8px;height:8px;}
        .ppl__head::before{height:4px;}
        .ppl__body{width:11px;height:17px;top:7px;border-radius:5px 5px 3px 3px;}
        .ppl__body::before,.ppl__body::after{width:3px;height:11px;top:2px;}
        .ppl__body::before{left:-2px;} .ppl__body::after{right:-2px;}
        .ppl__legs{width:9px;height:6px;gap:1px;}
        .ppl__legs::before,.ppl__legs::after{width:3px;height:6px;}
        .ppl__shadow{width:11px;height:3px;}
        .ppl-badge{font-size:8px;padding:1px 5px;border-radius:6px;}
        .room--empty .room__ppl::after{width:18px;height:18px;}

        .st-g{gap:7px;margin-bottom:18px;}
        .st-t{padding:14px;border-radius:11px;}
        .st-hd{margin-bottom:8px;}
        .st-n{font-size:22px;letter-spacing:-.6px;margin-bottom:4px;}
        .st-n small{font-size:11px;}
        .st-l{font-size:8px;letter-spacing:.6px;}
        .st-ch{font-size:8px;padding:2px 6px;gap:3px;border-radius:6px;}
        .st-ch i{font-size:8px;}
        .st-bar{height:3px;margin-top:7px;border-radius:2px;}

        .sec{margin-bottom:20px;}
        .sec-t{gap:8px;margin-bottom:11px;padding-bottom:8px;}
        .sec-ic{width:30px;height:30px;font-size:13px;border-radius:8px;}
        .sec-t h2{font-size:13px;}
        .sec-c{font-size:9px;padding:1px 6px;min-width:18px;border-radius:6px;}

        .asg{padding:12px;border-radius:10px;margin-bottom:12px;}
        .asg-l{font-size:8px;margin-bottom:8px;letter-spacing:1px;}
        .asg-s{padding:9px 11px;font-size:11px;border-radius:8px;padding-right:30px;}
        .btn-ad{padding:9px 14px;font-size:9px;border-radius:8px;}
        .asg-n{font-size:9px;margin-top:7px;}
        .asg-f{padding:10px 11px;font-size:10px;border-radius:8px;}

        .tm-l{gap:6px;}
        .mb{padding:10px 11px;gap:10px;border-radius:10px;}
        .mb-av{width:34px;height:34px;font-size:13px;border-radius:8px;}
        .mb-nm{font-size:12px;}
        .mb-em{font-size:10px;}
        .mb-rl{font-size:8px;padding:2px 5px;margin-top:3px;border-radius:5px;}
        .btn-rm{width:28px;height:28px;border-radius:7px;font-size:11px;}

        .emp-st{padding:24px 12px;border-radius:10px;}
        .emp-st .ic{font-size:24px;margin-bottom:7px;}
        .emp-st p{font-size:10px;}

        .ch-g{gap:7px;}
        .ch-c{padding:12px;border-radius:10px;gap:9px;}
        .ch-ic{width:32px;height:32px;font-size:14px;border-radius:8px;}
        .ch-nm{font-size:11px;}
        .ch-mt{font-size:8px;gap:5px;}
        .ch-mt i{font-size:8px;}
        .ch-ar{font-size:10px;}
    }

    @media(max-width:400px){
        .ds{padding:12px 8px 44px;}
        .ds-top{gap:8px;margin-bottom:14px;}
        .ds-title{font-size:15px;}
        .ds-sub{font-size:9px;}
        .ds-back{width:32px;height:32px;border-radius:8px;}
        .ds-back i{font-size:12px;}
        .btn-a{padding:8px 10px;font-size:8px;border-radius:8px;}

        .bc{padding:6px 9px;margin-bottom:10px;border-radius:7px;}
        .bc a{font-size:8px;}
        .bc .cr{font-size:8px;max-width:85px;}

        .al{padding:9px 10px;font-size:10px;margin-bottom:12px;border-radius:8px;}

        :root{--rm-w:190px;--rm-h:182px;--sign-w:124px;--ppl-w:11px;--ppl-h:24px;}
        .rh{padding:50px 6px 14px;border-radius:11px;margin-bottom:14px;}
        .room__sign{top:-40px;padding:7px;border-radius:9px;}
        .room__sign::after{height:8px;bottom:-8px;}
        .room__sign .ic{width:26px;height:26px;font-size:13px;border-radius:7px;margin-bottom:3px;}
        .room__sign .nm{font-size:10px;}
        .room__sign .lv{font-size:7px;}
        .rh-desc{font-size:10px;margin-top:12px;}

        .ppl__head{width:7px;height:7px;}
        .ppl__head::before{height:3px;}
        .ppl__body{width:10px;height:14px;top:6px;border-radius:4px 4px 3px 3px;}
        .ppl__body::before,.ppl__body::after{width:3px;height:9px;top:2px;}
        .ppl__body::before{left:-2px;} .ppl__body::after{right:-2px;}
        .ppl__legs{width:8px;height:5px;}
        .ppl__legs::before,.ppl__legs::after{width:3px;height:5px;}
        .ppl__shadow{width:9px;height:3px;}
        .ppl-badge{font-size:7px;padding:1px 4px;}

        .st-g{gap:6px;margin-bottom:16px;}
        .st-t{padding:12px;border-radius:10px;}
        .st-n{font-size:20px;margin-bottom:3px;}
        .st-n small{font-size:10px;}
        .st-l{font-size:8px;}
        .st-ch{font-size:7px;padding:2px 5px;}
        .st-bar{height:3px;margin-top:6px;}

        .sec{margin-bottom:18px;}
        .sec-t{gap:7px;margin-bottom:10px;padding-bottom:7px;}
        .sec-ic{width:28px;height:28px;font-size:12px;border-radius:7px;}
        .sec-t h2{font-size:12px;}
        .sec-c{font-size:8px;padding:1px 5px;}

        .asg{padding:11px;border-radius:9px;margin-bottom:11px;}
        .asg-s{padding:9px 10px;font-size:11px;border-radius:8px;}
        .btn-ad{padding:9px 12px;font-size:9px;border-radius:8px;}
        .asg-n{font-size:8px;}
        .asg-f{padding:9px 10px;font-size:9px;}

        .mb{padding:9px 10px;gap:9px;border-radius:9px;}
        .mb-av{width:32px;height:32px;font-size:12px;border-radius:7px;}
        .mb-nm{font-size:11px;}
        .mb-em{font-size:9px;}
        .mb-rl{font-size:7px;padding:2px 5px;margin-top:3px;}
        .btn-rm{width:26px;height:26px;border-radius:6px;font-size:10px;}

        .emp-st{padding:22px 10px;}
        .emp-st .ic{font-size:22px;}
        .emp-st p{font-size:10px;}

        .ch-c{padding:11px;border-radius:9px;gap:8px;}
        .ch-ic{width:30px;height:30px;font-size:13px;border-radius:7px;}
        .ch-nm{font-size:11px;}
        .ch-mt{font-size:8px;}
    }

    @media(max-width:360px){
        .ds{padding:10px 6px 40px;}
        .ds-top{gap:7px;margin-bottom:12px;}
        .ds-title{font-size:14px;}
        .ds-sub{font-size:8px;}
        .ds-back{width:30px;height:30px;border-radius:8px;}
        .ds-back i{font-size:11px;}
        .btn-a{padding:8px 9px;font-size:8px;}

        .bc{padding:6px 8px;margin-bottom:9px;}
        .bc a{font-size:8px;}
        .bc .cr{font-size:8px;max-width:75px;}

        :root{--rm-w:168px;--rm-h:162px;--sign-w:112px;--ppl-w:10px;--ppl-h:22px;}
        .rh{padding:44px 5px 12px;border-radius:10px;margin-bottom:12px;}
        .room__sign{top:-36px;padding:6px;border-radius:8px;}
        .room__sign::after{height:7px;bottom:-7px;}
        .room__sign .ic{width:24px;height:24px;font-size:12px;border-radius:6px;margin-bottom:3px;}
        .room__sign .nm{font-size:9px;}
        .room__sign .lv{font-size:6px;}
        .rh-desc{font-size:10px;margin-top:10px;}

        .ppl__head{width:6px;height:6px;}
        .ppl__body{width:9px;height:13px;top:5px;}
        .ppl__body::before,.ppl__body::after{width:2px;height:8px;}
        .ppl__legs{width:7px;height:4px;}
        .ppl__legs::before,.ppl__legs::after{width:2px;height:4px;}
        .ppl__shadow{width:8px;height:2px;}

        .st-g{gap:5px;margin-bottom:14px;}
        .st-t{padding:11px;border-radius:9px;}
        .st-n{font-size:18px;}
        .st-n small{font-size:9px;}
        .st-l{font-size:7px;}
        .st-ch{font-size:7px;padding:2px 4px;}

        .sec-t h2{font-size:11px;}
        .sec-ic{width:26px;height:26px;font-size:11px;}

        .asg{padding:10px;}
        .asg-s{padding:8px 9px;font-size:10px;}
        .btn-ad{padding:8px 10px;font-size:8px;}

        .mb{padding:8px 9px;gap:8px;}
        .mb-av{width:30px;height:30px;font-size:11px;}
        .mb-nm{font-size:10px;}
        .mb-em{font-size:9px;}
        .btn-rm{width:24px;height:24px;font-size:10px;}

        .ch-c{padding:10px;gap:7px;}
        .ch-ic{width:28px;height:28px;font-size:12px;}
        .ch-nm{font-size:10px;}
    }

    @media(max-width:320px){
        .ds{padding:8px 5px 36px;}
        .ds-top{gap:6px;margin-bottom:10px;}
        .ds-title{font-size:13px;}
        .ds-back{width:28px;height:28px;border-radius:7px;}
        .ds-back i{font-size:11px;}
        .btn-a{padding:7px 8px;font-size:7px;border-radius:7px;}

        .bc{padding:5px 7px;margin-bottom:8px;}
        .bc .cr{max-width:65px;}

        :root{--rm-w:148px;--rm-h:142px;--sign-w:100px;--ppl-w:9px;--ppl-h:20px;}
        .rh{padding:40px 4px 10px;border-radius:9px;margin-bottom:10px;}
        .room__sign{top:-32px;padding:5px;border-radius:7px;}
        .room__sign::after{height:6px;bottom:-6px;}
        .room__sign .ic{width:20px;height:20px;font-size:10px;border-radius:5px;margin-bottom:2px;}
        .room__sign .nm{font-size:9px;}
        .room__sign .lv{font-size:6px;}
        .rh-desc{font-size:9px;margin-top:8px;}

        .ppl__head{width:6px;height:6px;}
        .ppl__body{width:8px;height:11px;top:5px;}
        .ppl__body::before,.ppl__body::after{width:2px;height:7px;}
        .ppl__legs{width:6px;height:4px;}
        .ppl__legs::before,.ppl__legs::after{width:2px;height:4px;}
        .ppl__shadow{width:7px;height:2px;}

        .st-g{gap:4px;margin-bottom:12px;}
        .st-t{padding:10px;border-radius:8px;}
        .st-n{font-size:16px;}
        .st-n small{font-size:9px;}
        .st-l{font-size:7px;}
        .st-ch{font-size:6px;padding:1px 4px;}
        .st-bar{height:2px;margin-top:5px;}

        .sec{margin-bottom:14px;}
        .sec-t{gap:6px;margin-bottom:8px;padding-bottom:6px;}
        .sec-ic{width:24px;height:24px;font-size:10px;border-radius:6px;}
        .sec-t h2{font-size:11px;}
        .sec-c{font-size:7px;}

        .asg{padding:9px;border-radius:8px;}
        .asg-s{padding:8px 8px;font-size:10px;border-radius:7px;}
        .btn-ad{padding:8px 9px;font-size:8px;border-radius:7px;}
        .asg-n{font-size:8px;}

        .mb{padding:8px;gap:7px;border-radius:8px;}
        .mb-av{width:28px;height:28px;font-size:10px;border-radius:6px;}
        .mb-nm{font-size:10px;}
        .mb-em{font-size:8px;}
        .mb-rl{font-size:7px;padding:1px 4px;}
        .btn-rm{width:22px;height:22px;font-size:9px;border-radius:5px;}

        .emp-st{padding:18px 8px;}
        .emp-st .ic{font-size:20px;}
        .emp-st p{font-size:9px;}

        .ch-c{padding:9px;border-radius:8px;gap:6px;}
        .ch-ic{width:26px;height:26px;font-size:11px;border-radius:6px;}
        .ch-nm{font-size:10px;}
        .ch-mt{font-size:7px;}
        .ch-ar{font-size:9px;}
    }

    @media(max-height:480px) and (orientation:landscape){
        .ds{padding:10px 20px 30px;}
        .st-g{grid-template-columns:repeat(4,1fr);}
        .ch-g{grid-template-columns:repeat(3,1fr);}
        :root{--rm-w:260px;--rm-h:248px;--sign-w:160px;--ppl-w:15px;--ppl-h:32px;}
        .rh{padding:60px 14px 18px;}
    }

    @media(min-width:1600px){
        .ds-w{max-width:1400px;}
        :root{--rm-w:480px;--rm-h:458px;--sign-w:250px;--ppl-w:26px;--ppl-h:54px;}
    }
</style>

<div class="ds" style="--rc: {{ $rgb }}">
    <div class="ds-w">

        <div class="bc">
            <a href="{{ route('departments.index') }}"><i class="bi bi-house-fill"></i> <span data-i18n="navHome">Отделы</span></a>
            @foreach($ancestors as $anc)
            <span class="sp"><i class="bi bi-chevron-right"></i></span>
            <a href="{{ route('departments.show', $anc) }}">{{ $anc->name }}</a>
            @endforeach
            <span class="sp"><i class="bi bi-chevron-right"></i></span>
            <span class="cr">{{ $department->name }}</span>
        </div>

        <div class="ds-top">
            <a href="{{ route('departments.index') }}" class="ds-back" aria-label="Back"><i class="bi bi-arrow-left"></i></a>
            <div class="ds-info">
                <h1 class="ds-title">{{ $department->icon }} {{ $department->name }}</h1>
                <div class="ds-sub">
                    <span class="lv-chip"><i class="bi bi-layers-fill"></i> L{{ $department->level }} · {{ $levelNames[$department->level] ?? 'Уровень' }}</span>
                </div>
            </div>
            @if($isAdmin)
            <div class="ds-acts">
                <a href="{{ route('departments.edit', $department) }}" class="btn-a"><i class="bi bi-pencil-fill"></i> <span data-i18n="editBtn">Редактировать</span></a>
                <form action="{{ route('departments.destroy', $department) }}" method="POST" style="display:inline" onsubmit="return confirm('Удалить отдел?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-a dng"><i class="bi bi-trash-fill"></i> <span data-i18n="deleteBtn">Удалить</span></button>
                </form>
            </div>
            @endif
        </div>

        @if(session('success'))
        <div class="al"><span class="ic"><i class="bi bi-check-lg"></i></span><span>{{ session('success') }}</span></div>
        @endif

        <div class="rh">
            <div class="room {{ $memberCount ? '' : 'room--empty' }}">
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
                    @for($i = 0; $i < $showPeople; $i++)
                    <span class="ppl"><span class="ppl__shadow"></span><span class="ppl__body"></span><span class="ppl__head"></span><span class="ppl__legs"></span></span>
                    @endfor
                    @if($extraPeople > 0)<span class="ppl-badge">+{{ $extraPeople }}</span>@endif
                </div>

                <div class="room__sign">
                    <div class="ic">{{ $department->icon }}</div>
                    <h3 class="nm">{{ $department->name }}</h3>
                    <div class="lv">L{{ $department->level }} · {{ $memberCount }}/{{ $maxUsers }}</div>
                </div>
            </div>

            <p class="rh-desc {{ $department->description ? '' : 'emp' }}">
                {{ $department->description ?: 'Описание отдела пока не добавлено.' }}
            </p>
        </div>

        <div class="st-g">
            <div class="st-t">
                <div class="st-hd"><span class="st-ch"><i class="bi bi-people-fill"></i> <span data-i18n="statTeam">Команда</span></span></div>
                <div class="st-n">{{ $memberCount }}<small>/{{ $maxUsers }}</small></div>
                <div class="st-l" data-i18n="statMembers">Участников</div>
                <div class="st-bar"><span style="width: {{ $fillPercent }}%"></span></div>
            </div>
            <div class="st-t">
                <div class="st-hd"><span class="st-ch"><i class="bi bi-hourglass-split"></i> <span data-i18n="statFree">Свободно</span></span></div>
                <div class="st-n">{{ $remainingSlots }}</div>
                <div class="st-l" data-i18n="statSlots">Мест</div>
            </div>
            <div class="st-t">
                <div class="st-hd"><span class="st-ch"><i class="bi bi-folder2-open"></i> <span data-i18n="statChildren">Подотделы</span></span></div>
                <div class="st-n">{{ $department->children->count() }}</div>
                <div class="st-l" data-i18n="statSubdivisions">Подразделений</div>
            </div>
            <div class="st-t">
                <div class="st-hd"><span class="st-ch"><i class="bi bi-layers-fill"></i> <span data-i18n="statHierarchy">Иерархия</span></span></div>
                <div class="st-n">{{ $department->level }}</div>
                <div class="st-l" data-i18n="statLevel">Уровень</div>
            </div>
        </div>

        <div class="sec">
            <div class="sec-t">
                <div class="sec-ic"><i class="bi bi-people-fill"></i></div>
                <h2 data-i18n="sectionTeam">Команда</h2>
                <span class="sec-c">{{ $memberCount }}/{{ $maxUsers }}</span>
            </div>

            @if($isAdmin)
            @if($remainingSlots > 0)
            <div class="asg">
                <div class="asg-l"><i class="bi bi-person-plus-fill"></i> <span data-i18n="assignLabel">Добавить участника</span></div>
                <form action="{{ route('departments.assign-user', $department) }}" method="POST" class="asg-r">
                    @csrf
                    <select name="user_id" class="asg-s" required>
                        <option value="" data-i18n="selectUser">— Выберите пользователя —</option>
                        @foreach($companyUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-ad"><i class="bi bi-plus-lg"></i> <span data-i18n="addBtn">Добавить</span></button>
                </form>
                <div class="asg-n">
                    <i class="bi bi-info-circle"></i>
                    <span data-i18n="assignNote">Роль «employee» назначается автоматически. Свободно мест:</span>
                    <strong>{{ $remainingSlots }}</strong>
                </div>
            </div>
            @else
            <div class="asg-f">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span data-i18n="limitReached">Достигнут лимит — максимум 10 сотрудников в отделе.</span>
            </div>
            @endif
            @endif

            <div class="tm-l">
                @forelse($department->users as $user)
                <div class="mb">
                    <div class="mb-av">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</div>
                    <div class="mb-inf">
                        <strong class="mb-nm">{{ $user->name }}</strong>
                        <small class="mb-em">{{ $user->email }}</small>
                        <span class="mb-rl">
                            <i class="bi bi-shield-fill-check"></i>
                            {{ $user->role_label }}
                        </span>
                    </div>
                    @if($isAdmin)
                    <form action="{{ route('departments.remove-user', [$department, $user->id]) }}" method="POST" style="display:inline" onsubmit="return confirm('Убрать сотрудника из отдела?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-rm" title="Убрать"><i class="bi bi-person-dash-fill"></i></button>
                    </form>
                    @endif
                </div>
                @empty
                <div class="emp-st">
                    <i class="bi bi-person-x ic"></i>
                    <p data-i18n="emptyTeam">В отделе пока никого нет.</p>
                </div>
                @endforelse
            </div>
        </div>

        @if($department->children->count())
        <div class="sec">
            <div class="sec-t">
                <div class="sec-ic"><i class="bi bi-folder2-open"></i></div>
                <h2><span data-i18n="sectionChildren">Подотделы</span> <span style="opacity:.5;font-weight:500;font-size:12px;margin-left:6px;">(L{{ $department->level + 1 }})</span></h2>
                <span class="sec-c">{{ $department->children->count() }}</span>
            </div>

            <div class="ch-g">
                @foreach($department->children as $child)
                <a href="{{ route('departments.show', $child) }}" class="ch-c">
                    <div class="ch-ic">{{ $child->icon }}</div>
                    <div class="ch-bd">
                        <h4 class="ch-nm">{{ $child->name }}</h4>
                        <div class="ch-mt">
                            <span><i class="bi bi-people-fill"></i> {{ $child->users->count() }} <span data-i18n="membersShort">чел.</span></span>
                            @if($child->children->count())
                            <span><i class="bi bi-folder2-open"></i> {{ $child->children->count() }}</span>
                            @endif
                        </div>
                    </div>
                    <i class="bi bi-arrow-right ch-ar"></i>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded',function(){
    const T={
        ru:{navHome:'Отделы',editBtn:'Редактировать',deleteBtn:'Удалить',statTeam:'Команда',statMembers:'Участников',statFree:'Свободно',statSlots:'Мест',statChildren:'Подотделы',statSubdivisions:'Подразделений',statHierarchy:'Иерархия',statLevel:'Уровень',sectionTeam:'Команда',sectionChildren:'Подотделы',assignLabel:'Добавить участника',selectUser:'— Выберите пользователя —',addBtn:'Добавить',assignNote:'Роль «employee» назначается автоматически. Свободно мест:',limitReached:'Достигнут лимит — максимум 10 сотрудников в отделе.',emptyTeam:'В отделе пока никого нет.',membersShort:'чел.'},
        tj:{navHome:'Шӯъбаҳо',editBtn:'Таҳрир',deleteBtn:'Нест кардан',statTeam:'Даста',statMembers:'Иштирокчиён',statFree:'Озод',statSlots:'Ҷойҳо',statChildren:'Зершӯъбаҳо',statSubdivisions:'Зерсохторҳо',statHierarchy:'Иерархия',statLevel:'Сатҳ',sectionTeam:'Даста',sectionChildren:'Зершӯъбаҳо',assignLabel:'Илова кардани иштирокчӣ',selectUser:'— Корбарро интихоб кунед —',addBtn:'Илова кардан',assignNote:'Нақши «employee» ба таври худкор таъин мешавад. Ҷойҳои озод:',limitReached:'Ҳадди аксар — 10 корманд дар шӯъба.',emptyTeam:'Дар шӯъба ҳоло касе нест.',membersShort:'нафар'},
        en:{navHome:'Departments',editBtn:'Edit',deleteBtn:'Delete',statTeam:'Team',statMembers:'Members',statFree:'Available',statSlots:'Slots',statChildren:'Sub-depts',statSubdivisions:'Subdivisions',statHierarchy:'Hierarchy',statLevel:'Level',sectionTeam:'Team',sectionChildren:'Sub-departments',assignLabel:'Add member',selectUser:'— Select user —',addBtn:'Add',assignNote:'Role «employee» is assigned automatically. Available slots:',limitReached:'Limit reached — maximum 10 employees per department.',emptyTeam:'No one in this department yet.',membersShort:'people'}
    };
    function applyLang(l){const d=T[l]||T.ru;document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.getAttribute('data-i18n');if(d[k]!==undefined)el.textContent=d[k];});}
    applyLang(localStorage.getItem('docsign_lang')||'ru');
    window.addEventListener('docsign:lang-changed',e=>applyLang(e.detail?.lang||'ru'));
    window.addEventListener('storage',e=>{if(e.key==='docsign_lang'&&e.newValue)applyLang(e.newValue);});
});
</script>

@endsection