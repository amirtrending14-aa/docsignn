@extends('layouts.admin')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@php
$authUser = auth()->user();

$levelColors = [];
foreach ($levelNames as $lv => $name) {
    $levelColors[$lv] = \App\Models\Department::levelColor($lv);
}

$curLevel = (int) old('level', $department->level);
$curIcon  = old('icon', $department->icon ?? '');
$curColor = old('color', $department->color ?? ($levelColors[$curLevel] ?? '#4f8cff'));
@endphp

<style>
    *{box-sizing:border-box;margin:0;padding:0;}

    .ep{
        min-height:100vh;
        padding:40px 32px 90px;
        color:var(--text,#e7ecf3);
        font-family:'Inter',sans-serif;
        background:var(--bg,#0a0d14);
        width:100%;
        overflow-x:hidden;
    }

    /* ===== TOPBAR ===== */
    .ep-top{
        max-width:1200px;
        margin:0 auto 32px;
        display:flex;
        align-items:center;
        gap:16px;
    }
    .ep-back{
        width:46px;height:46px;
        border-radius:13px;
        display:grid;place-items:center;
        background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.08);
        color:var(--text);
        text-decoration:none;
        transition:all .3s;
        flex-shrink:0;
    }
    .ep-back:hover{transform:translateX(-3px);border-color:rgba(255,255,255,.2);background:rgba(255,255,255,.06);}
    .ep-back:active{transform:scale(.93);}
    .ep-back i{font-size:18px;}

    .ep-logo{
        width:52px;height:52px;
        border-radius:15px;
        display:grid;place-items:center;
        background:linear-gradient(135deg,rgba(255,255,255,.08),rgba(255,255,255,.02));
        border:1px solid rgba(255,255,255,.1);
        flex-shrink:0;
    }
    .ep-logo i{font-size:20px;color:var(--text);}

    .ep-info{flex:1;min-width:0;}
    .ep-title{font-size:24px;font-weight:800;letter-spacing:-.4px;color:var(--text);line-height:1.2;}
    .ep-sub{font-size:12px;color:var(--muted);font-weight:600;margin-top:4px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
    .ep-badge{
        display:inline-flex;align-items:center;gap:5px;
        padding:2px 9px;border-radius:7px;
        font-size:11px;font-weight:700;color:var(--text);
        background:rgba(255,255,255,.06);
        border:1px solid rgba(255,255,255,.1);
        max-width:200px;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    }
    .ep-badge i{font-size:10px;color:var(--muted);flex-shrink:0;}

    /* ===== GRID ===== */
    .ep-grid{
        max-width:1200px;
        margin:0 auto;
        display:grid;
        grid-template-columns:1fr 370px;
        gap:22px;
        align-items:start;
    }

    /* ===== CARD ===== */
    .ep-card{
        padding:30px;
        border-radius:20px;
        background:rgba(255,255,255,.025);
        border:1px solid rgba(255,255,255,.06);
        position:relative;
        overflow:hidden;
    }
    .ep-card::before{
        content:"";position:absolute;top:0;left:0;right:0;height:1px;
        background:linear-gradient(90deg,transparent,rgba(255,255,255,.1),transparent);
    }

    /* ===== FORM ===== */
    .fg{margin-bottom:24px;}
    .fg-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}

    .fl{
        display:flex;align-items:center;gap:8px;
        font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
        color:var(--muted);margin-bottom:10px;
    }
    .fl i{color:var(--text);font-size:13px;opacity:.7;}
    .fl .rq{color:#ff6363;font-size:14px;}

    .fh{font-size:11px;color:var(--muted);font-style:italic;margin-top:6px;display:flex;align-items:center;gap:6px;line-height:1.4;}
    .fh i{font-size:11px;opacity:.7;flex-shrink:0;}

    .fi,.fs,.ft{
        width:100%;
        padding:14px 16px;
        border-radius:12px;
        background:rgba(0,0,0,.3);
        border:1px solid rgba(255,255,255,.08);
        color:var(--text);
        font-size:14px;font-weight:500;
        font-family:'Inter',sans-serif;
        outline:none;
        transition:all .25s;
        -webkit-tap-highlight-color:transparent;
    }
    .ft{resize:vertical;min-height:100px;line-height:1.6;}
    .fs{
        appearance:none;cursor:pointer;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%238892a6' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat:no-repeat;background-position:right 14px center;padding-right:40px;
    }
    .fs option{background:#0a0d14;color:var(--text);}
    .fi::placeholder,.ft::placeholder{color:var(--muted);opacity:.6;}
    .fi:focus,.fs:focus,.ft:focus{
        border-color:rgba(255,255,255,.25);
        box-shadow:0 0 0 3px rgba(255,255,255,.04);
        background:rgba(0,0,0,.5);
    }
    .fe{display:flex;align-items:center;gap:6px;color:#ff6363;font-size:11px;font-weight:600;margin-top:6px;}

    /* ===== ICON ===== */
    .icon-w{display:flex;align-items:center;gap:12px;}
    .icon-pv{
        width:48px;height:48px;border-radius:12px;
        display:grid;place-items:center;
        font-size:18px;font-weight:800;
        background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);
        flex-shrink:0;color:var(--text);transition:all .3s;
    }
    .icon-w .fi{flex:1;text-align:center;font-size:16px;font-weight:700;min-width:0;}

    /* ===== COLOR ===== */
    .clr-w{display:flex;align-items:center;gap:12px;}
    .clr-sw{
        position:relative;width:48px;height:48px;border-radius:12px;
        overflow:hidden;border:2px solid rgba(255,255,255,.12);
        flex-shrink:0;cursor:pointer;transition:all .3s;
    }
    .clr-sw:hover{border-color:rgba(255,255,255,.3);transform:scale(1.05);}
    .clr-sw input[type=color]{position:absolute;inset:-8px;width:calc(100% + 16px);height:calc(100% + 16px);border:none;padding:0;cursor:pointer;background:none;}
    .clr-hex{
        flex:1;min-width:0;
        padding:14px 16px;border-radius:12px;
        background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);
        color:var(--text);font-size:14px;font-weight:600;
        font-family:'JetBrains Mono',monospace;text-transform:uppercase;
    }
    .clr-rst{
        padding:12px 14px;border-radius:12px;
        background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);
        color:var(--muted);cursor:pointer;font-size:12px;font-weight:600;
        transition:all .2s;white-space:nowrap;-webkit-tap-highlight-color:transparent;
    }
    .clr-rst:hover{color:var(--text);border-color:rgba(255,255,255,.2);}
    .clr-rst:active{transform:scale(.93);}

    /* ===== LEVEL ===== */
    .lv-w{position:relative;}
    .lv-cur{
        display:flex;align-items:center;gap:12px;
        padding:14px 16px;border-radius:12px;
        background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);
        cursor:pointer;transition:all .25s;user-select:none;
        -webkit-tap-highlight-color:transparent;
    }
    .lv-cur:hover{border-color:rgba(255,255,255,.2);background:rgba(0,0,0,.45);}
    .lv-cur.open{border-color:rgba(255,255,255,.3);background:rgba(0,0,0,.5);}
    .lv-n{
        width:36px;height:36px;border-radius:10px;
        display:grid;place-items:center;
        background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
        font-size:16px;font-weight:800;font-family:'JetBrains Mono',monospace;
        color:var(--text);flex-shrink:0;
    }
    .lv-i{flex:1;min-width:0;}
    .lv-nm{font-size:14px;font-weight:700;color:var(--text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .lv-sb{font-size:11px;color:var(--muted);margin-top:2px;}
    .lv-ch{color:var(--muted);font-size:14px;transition:transform .3s;flex-shrink:0;}
    .lv-cur.open .lv-ch{transform:rotate(180deg);}

    .lv-dd{
        position:absolute;top:calc(100% + 6px);left:0;right:0;
        max-height:320px;overflow-y:auto;
        background:#0d1017;border:1px solid rgba(255,255,255,.1);
        border-radius:12px;padding:6px;z-index:1000;
        opacity:0;pointer-events:none;transform:translateY(-8px) scale(.98);
        transition:all .25s;
        box-shadow:0 20px 50px rgba(0,0,0,.7);
    }
    .lv-dd.open{opacity:1;pointer-events:auto;transform:translateY(0) scale(1);}
    .lv-dd::-webkit-scrollbar{width:5px;}
    .lv-dd::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:3px;}

    .lv-o{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;cursor:pointer;transition:all .2s;-webkit-tap-highlight-color:transparent;}
    .lv-o:hover{background:rgba(255,255,255,.05);}
    .lv-o:active{background:rgba(255,255,255,.08);}
    .lv-o.sel{background:rgba(255,255,255,.08);}
    .lv-on{
        width:32px;height:32px;border-radius:8px;
        display:grid;place-items:center;
        background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);
        font-size:13px;font-weight:800;font-family:'JetBrains Mono',monospace;
        color:var(--text);flex-shrink:0;transition:all .2s;
    }
    .lv-o.sel .lv-on{background:var(--text);color:#0a0d14;border-color:transparent;}
    .lv-oi{flex:1;min-width:0;}
    .lv-onm{font-size:13px;font-weight:700;color:var(--text);line-height:1.3;}
    .lv-osb{font-size:10px;color:var(--muted);margin-top:1px;font-family:'JetBrains Mono',monospace;}
    .lv-ck{color:var(--text);opacity:0;font-size:14px;transition:opacity .2s;flex-shrink:0;}
    .lv-o.sel .lv-ck{opacity:1;}

    /* ===== BUTTONS ===== */
    .ep-acts{display:flex;gap:12px;padding-top:26px;margin-top:8px;border-top:1px solid rgba(255,255,255,.06);}
    .btn-s{
        position:relative;overflow:hidden;flex:1;
        display:inline-flex;align-items:center;justify-content:center;gap:10px;
        padding:15px 24px;border-radius:12px;
        border:1px solid rgba(255,255,255,.15);cursor:pointer;
        font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;
        color:var(--text);background:rgba(255,255,255,.06);
        transition:all .3s;-webkit-tap-highlight-color:transparent;
    }
    .btn-s:hover{transform:translateY(-2px);background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.25);box-shadow:0 8px 24px rgba(0,0,0,.3);}
    .btn-s:active{transform:translateY(0) scale(.97);}
    .btn-s::after{content:"";position:absolute;inset:0;transform:translateX(-100%);background:linear-gradient(90deg,transparent,rgba(255,255,255,.15),transparent);transition:transform .6s;}
    .btn-s:hover::after{transform:translateX(100%);}

    .btn-c{
        display:inline-flex;align-items:center;justify-content:center;gap:8px;
        padding:15px 22px;border-radius:12px;text-decoration:none;
        font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;
        color:var(--muted);background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.08);
        transition:all .25s;-webkit-tap-highlight-color:transparent;
    }
    .btn-c:hover{color:#ff6363;border-color:rgba(255,99,99,.3);background:rgba(255,99,99,.06);}
    .btn-c:active{transform:scale(.97);}

    /* ===== PREVIEW ===== */
    .pv-w{position:sticky;top:24px;align-self:start;}
    .pv-card{
        padding:24px;border-radius:20px;
        background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);
        position:relative;overflow:hidden;
    }
    .pv-card::before{content:"";position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.1),transparent);}
    .pv-hd{
        display:flex;align-items:center;gap:8px;
        font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
        color:var(--muted);margin-bottom:18px;padding-bottom:12px;
        border-bottom:1px solid rgba(255,255,255,.06);
    }
    .pv-hd i{opacity:.7;}

    .pv-vis{
        position:relative;padding:22px;border-radius:14px;
        background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.06);
        margin-bottom:14px;overflow:hidden;transition:all .3s;
    }
    .pv-vis::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--pvc,#4f8cff);transition:background .3s;}
    .pv-top{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
    .pv-ic{
        width:44px;height:44px;border-radius:12px;
        display:grid;place-items:center;font-size:16px;font-weight:800;
        background:color-mix(in srgb,var(--pvc,#4f8cff) 22%,transparent);
        border:1px solid color-mix(in srgb,var(--pvc,#4f8cff) 45%,transparent);
        flex-shrink:0;color:var(--text);transition:all .3s;
    }
    .pv-tt{flex:1;min-width:0;}
    .pv-chip{
        display:inline-flex;align-items:center;gap:5px;
        padding:3px 8px;border-radius:7px;
        font-size:9px;font-weight:800;font-family:'JetBrains Mono',monospace;
        color:var(--pvc,#4f8cff);
        background:color-mix(in srgb,var(--pvc,#4f8cff) 14%,transparent);
        border:1px solid color-mix(in srgb,var(--pvc,#4f8cff) 30%,transparent);
        letter-spacing:.5px;transition:all .3s;
    }
    .pv-nm{font-size:16px;font-weight:800;color:var(--text);margin:6px 0 0;letter-spacing:-.3px;line-height:1.3;word-break:break-word;}
    .pv-ds{font-size:12px;color:var(--muted);line-height:1.6;margin:10px 0 0;word-break:break-word;}
    .pv-mt{
        display:flex;justify-content:space-between;align-items:center;
        padding:12px 14px;border-radius:10px;
        background:rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.05);
        gap:8px;flex-wrap:wrap;
    }
    .pv-mi{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--muted);}
    .pv-mi i{font-size:12px;opacity:.7;}

    /* ===== DISABLED ===== */
    .fg.dis{opacity:.35;pointer-events:none;filter:grayscale(.5);}

    /* ============================================================
       RESPONSIVE
       ============================================================ */

    @media(max-width:1100px){
        .ep-grid{grid-template-columns:1fr;gap:18px;}
        .pv-w{position:static;order:-1;}
    }

    @media(max-width:992px){
        .ep{padding:32px 24px 80px;}
        .ep-card{padding:26px;}
        .ep-title{font-size:22px;}
        .ep-logo{width:48px;height:48px;}
        .ep-back{width:42px;height:42px;}
    }

    @media(max-width:768px){
        .ep{padding:24px 18px 70px;}
        .ep-top{margin-bottom:24px;gap:12px;}
        .ep-title{font-size:20px;}
        .ep-sub{font-size:11px;}
        .ep-logo{width:44px;height:44px;border-radius:13px;}
        .ep-logo i{font-size:17px;}
        .ep-back{width:40px;height:40px;border-radius:11px;}
        .ep-back i{font-size:16px;}
        .ep-badge{font-size:10px;padding:2px 8px;max-width:170px;}

        .ep-card{padding:22px;border-radius:16px;}
        .pv-card{padding:20px;border-radius:16px;}

        .fg{margin-bottom:20px;}
        .fg-row{grid-template-columns:1fr;gap:0;}

        .fi,.fs,.ft{padding:13px 15px;font-size:13px;border-radius:11px;}
        .fs{padding-right:36px;}

        .fl{font-size:10px;margin-bottom:9px;}
        .fh{font-size:10px;}

        .icon-pv{width:44px;height:44px;font-size:16px;border-radius:11px;}
        .clr-sw{width:44px;height:44px;border-radius:11px;}
        .clr-hex{padding:13px 14px;font-size:13px;border-radius:11px;}
        .clr-rst{padding:11px 13px;font-size:11px;border-radius:11px;}

        .lv-cur{padding:13px 15px;border-radius:11px;}
        .lv-n{width:34px;height:34px;font-size:15px;border-radius:9px;}
        .lv-nm{font-size:13px;}

        .ep-acts{flex-direction:column;gap:10px;padding-top:22px;}
        .btn-s,.btn-c{width:100%;justify-content:center;padding:14px 20px;}

        .pv-vis{padding:18px;border-radius:12px;}
        .pv-ic{width:40px;height:40px;font-size:14px;border-radius:11px;}
        .pv-nm{font-size:15px;}
        .pv-ds{font-size:11px;}
        .pv-mt{padding:11px 13px;}
        .pv-mi{font-size:10px;}
    }

    @media(max-width:576px){
        .ep{padding:18px 14px 60px;}
        .ep-top{margin-bottom:20px;gap:10px;}
        .ep-title{font-size:18px;}
        .ep-sub{font-size:10px;gap:6px;}
        .ep-logo{width:40px;height:40px;border-radius:11px;}
        .ep-logo i{font-size:15px;}
        .ep-back{width:38px;height:38px;border-radius:10px;}
        .ep-back i{font-size:15px;}
        .ep-badge{font-size:9px;padding:2px 7px;max-width:150px;border-radius:6px;}
        .ep-badge i{font-size:9px;}

        .ep-card{padding:18px;border-radius:14px;}
        .pv-card{padding:16px;border-radius:14px;}

        .fg{margin-bottom:17px;}
        .fl{font-size:10px;letter-spacing:1.2px;margin-bottom:8px;gap:6px;}
        .fl i{font-size:12px;}
        .fl .rq{font-size:12px;}
        .fh{font-size:10px;margin-top:5px;}

        .fi,.fs,.ft{padding:12px 13px;font-size:13px;border-radius:10px;}
        .fs{padding-right:34px;background-position:right 12px center;}
        .ft{min-height:80px;}

        .icon-w{gap:10px;}
        .icon-pv{width:42px;height:42px;font-size:15px;border-radius:10px;}
        .icon-w .fi{font-size:15px;}

        .clr-w{gap:10px;}
        .clr-sw{width:42px;height:42px;border-radius:10px;}
        .clr-hex{padding:12px 13px;font-size:12px;border-radius:10px;}
        .clr-rst{padding:10px 12px;font-size:11px;border-radius:10px;}

        .lv-cur{padding:12px 13px;gap:10px;border-radius:10px;}
        .lv-n{width:32px;height:32px;font-size:14px;border-radius:8px;}
        .lv-nm{font-size:13px;}
        .lv-sb{font-size:10px;}
        .lv-ch{font-size:13px;}

        .lv-dd{max-height:250px;border-radius:10px;padding:5px;}
        .lv-o{padding:9px 10px;gap:10px;border-radius:7px;}
        .lv-on{width:28px;height:28px;font-size:12px;border-radius:7px;}
        .lv-onm{font-size:12px;}
        .lv-osb{font-size:9px;}
        .lv-ck{font-size:13px;}

        .ep-acts{padding-top:18px;gap:9px;}
        .btn-s{padding:13px 18px;font-size:11px;border-radius:10px;}
        .btn-c{padding:13px 18px;font-size:11px;border-radius:10px;}

        .pv-hd{font-size:9px;margin-bottom:14px;padding-bottom:10px;}
        .pv-vis{padding:16px;border-radius:11px;margin-bottom:12px;}
        .pv-vis::before{width:3px;}
        .pv-top{gap:10px;margin-bottom:10px;}
        .pv-ic{width:38px;height:38px;font-size:13px;border-radius:10px;}
        .pv-chip{font-size:8px;padding:2px 7px;border-radius:6px;}
        .pv-nm{font-size:14px;margin-top:5px;}
        .pv-ds{font-size:11px;margin-top:8px;line-height:1.5;}
        .pv-mt{padding:10px 12px;border-radius:9px;gap:6px;}
        .pv-mi{font-size:10px;gap:5px;}
        .pv-mi i{font-size:11px;}

        .fe{font-size:10px;}
    }

    @media(max-width:480px){
        .ep{padding:14px 10px 50px;}
        .ep-top{margin-bottom:16px;gap:9px;}
        .ep-title{font-size:16px;}
        .ep-sub{font-size:9px;gap:5px;margin-top:3px;}
        .ep-logo{width:36px;height:36px;border-radius:10px;}
        .ep-logo i{font-size:14px;}
        .ep-back{width:34px;height:34px;border-radius:9px;}
        .ep-back i{font-size:14px;}
        .ep-badge{font-size:9px;padding:2px 6px;max-width:130px;border-radius:5px;}
        .ep-badge i{font-size:8px;}

        .ep-card{padding:15px;border-radius:12px;}
        .pv-card{padding:13px;border-radius:12px;}

        .fg{margin-bottom:15px;}
        .fl{font-size:9px;letter-spacing:1px;margin-bottom:7px;gap:5px;}
        .fl i{font-size:11px;}
        .fl .rq{font-size:11px;}
        .fh{font-size:9px;margin-top:4px;gap:4px;}
        .fh i{font-size:10px;}

        .fi,.fs,.ft{padding:11px 12px;font-size:12px;border-radius:9px;}
        .fs{padding-right:32px;background-position:right 11px center;}
        .ft{min-height:70px;}

        .icon-w{gap:8px;}
        .icon-pv{width:38px;height:38px;font-size:14px;border-radius:9px;}
        .icon-w .fi{font-size:14px;padding:11px 10px;}

        .clr-w{gap:8px;}
        .clr-sw{width:38px;height:38px;border-radius:9px;}
        .clr-hex{padding:11px 11px;font-size:11px;border-radius:9px;}
        .clr-rst{padding:9px 10px;font-size:10px;border-radius:9px;}

        .lv-cur{padding:11px 12px;gap:9px;border-radius:9px;}
        .lv-n{width:30px;height:30px;font-size:13px;border-radius:8px;}
        .lv-nm{font-size:12px;}
        .lv-sb{font-size:9px;margin-top:1px;}
        .lv-ch{font-size:12px;}

        .lv-dd{max-height:220px;border-radius:9px;padding:4px;top:calc(100% + 4px);}
        .lv-o{padding:8px 9px;gap:9px;border-radius:6px;}
        .lv-on{width:26px;height:26px;font-size:11px;border-radius:6px;}
        .lv-onm{font-size:11px;}
        .lv-osb{font-size:8px;}
        .lv-ck{font-size:12px;}

        .ep-acts{padding-top:16px;gap:8px;}
        .btn-s{padding:12px 16px;font-size:10px;border-radius:9px;gap:8px;}
        .btn-c{padding:12px 16px;font-size:10px;border-radius:9px;gap:6px;}

        .pv-hd{font-size:9px;margin-bottom:12px;padding-bottom:9px;gap:6px;}
        .pv-vis{padding:13px;border-radius:10px;margin-bottom:10px;}
        .pv-vis::before{width:3px;}
        .pv-top{gap:9px;margin-bottom:9px;}
        .pv-ic{width:34px;height:34px;font-size:12px;border-radius:9px;}
        .pv-chip{font-size:8px;padding:2px 6px;border-radius:5px;gap:4px;}
        .pv-chip i{font-size:8px;}
        .pv-nm{font-size:13px;margin-top:4px;}
        .pv-ds{font-size:10px;margin-top:7px;line-height:1.45;}
        .pv-mt{padding:9px 10px;border-radius:8px;gap:5px;}
        .pv-mi{font-size:9px;gap:4px;}
        .pv-mi i{font-size:10px;}

        .fe{font-size:9px;gap:4px;}
        .fe i{font-size:10px;}
    }

    @media(max-width:400px){
        .ep{padding:12px 8px 44px;}
        .ep-top{margin-bottom:14px;gap:8px;}
        .ep-title{font-size:15px;}
        .ep-sub{font-size:9px;}
        .ep-logo{width:33px;height:33px;border-radius:9px;}
        .ep-logo i{font-size:13px;}
        .ep-back{width:32px;height:32px;border-radius:8px;}
        .ep-back i{font-size:13px;}
        .ep-badge{font-size:8px;padding:1px 5px;max-width:110px;border-radius:5px;}

        .ep-card{padding:13px;border-radius:11px;}
        .pv-card{padding:11px;border-radius:11px;}

        .fg{margin-bottom:13px;}
        .fl{font-size:9px;letter-spacing:.8px;margin-bottom:6px;gap:5px;}
        .fl i{font-size:10px;}

        .fi,.fs,.ft{padding:10px 11px;font-size:12px;border-radius:8px;}
        .fs{padding-right:30px;background-position:right 10px center;}
        .ft{min-height:64px;}

        .icon-w{gap:7px;}
        .icon-pv{width:36px;height:36px;font-size:13px;border-radius:8px;}
        .icon-w .fi{font-size:13px;padding:10px 9px;}

        .clr-w{gap:7px;}
        .clr-sw{width:36px;height:36px;border-radius:8px;}
        .clr-hex{padding:10px 10px;font-size:11px;border-radius:8px;}
        .clr-rst{padding:8px 9px;font-size:9px;border-radius:8px;}

        .lv-cur{padding:10px 11px;gap:8px;border-radius:8px;}
        .lv-n{width:28px;height:28px;font-size:12px;border-radius:7px;}
        .lv-nm{font-size:11px;}
        .lv-sb{font-size:9px;}
        .lv-ch{font-size:11px;}

        .lv-dd{max-height:200px;border-radius:8px;padding:4px;}
        .lv-o{padding:7px 8px;gap:8px;border-radius:6px;}
        .lv-on{width:24px;height:24px;font-size:10px;border-radius:6px;}
        .lv-onm{font-size:11px;}
        .lv-osb{font-size:8px;}

        .ep-acts{padding-top:14px;gap:7px;}
        .btn-s{padding:11px 14px;font-size:10px;border-radius:8px;gap:7px;}
        .btn-c{padding:11px 14px;font-size:10px;border-radius:8px;gap:5px;}

        .pv-hd{font-size:8px;margin-bottom:10px;padding-bottom:8px;}
        .pv-vis{padding:11px;border-radius:9px;margin-bottom:9px;}
        .pv-top{gap:8px;margin-bottom:8px;}
        .pv-ic{width:32px;height:32px;font-size:11px;border-radius:8px;}
        .pv-chip{font-size:7px;padding:2px 5px;}
        .pv-nm{font-size:12px;margin-top:4px;}
        .pv-ds{font-size:10px;margin-top:6px;}
        .pv-mt{padding:8px 9px;border-radius:7px;}
        .pv-mi{font-size:9px;}
    }

    @media(max-width:360px){
        .ep{padding:10px 6px 40px;}
        .ep-top{margin-bottom:12px;gap:7px;}
        .ep-title{font-size:14px;}
        .ep-sub{font-size:8px;}
        .ep-logo{width:30px;height:30px;border-radius:8px;}
        .ep-logo i{font-size:12px;}
        .ep-back{width:30px;height:30px;border-radius:8px;}
        .ep-back i{font-size:12px;}
        .ep-badge{font-size:8px;padding:1px 5px;max-width:100px;}

        .ep-card{padding:11px;border-radius:10px;}
        .pv-card{padding:10px;border-radius:10px;}

        .fg{margin-bottom:12px;}
        .fl{font-size:8px;letter-spacing:.7px;margin-bottom:6px;gap:4px;}
        .fl i{font-size:10px;}
        .fl .rq{font-size:10px;}

        .fi,.fs,.ft{padding:9px 10px;font-size:11px;border-radius:8px;}
        .fs{padding-right:28px;background-position:right 9px center;}
        .ft{min-height:58px;}

        .icon-w{gap:6px;}
        .icon-pv{width:33px;height:33px;font-size:12px;border-radius:7px;}
        .icon-w .fi{font-size:12px;padding:9px 8px;}

        .clr-w{gap:6px;}
        .clr-sw{width:33px;height:33px;border-radius:7px;}
        .clr-hex{padding:9px 9px;font-size:10px;border-radius:7px;}
        .clr-rst{padding:8px 8px;font-size:9px;border-radius:7px;}

        .lv-cur{padding:9px 10px;gap:7px;border-radius:8px;}
        .lv-n{width:26px;height:26px;font-size:11px;border-radius:6px;}
        .lv-nm{font-size:11px;}

        .lv-dd{max-height:180px;}
        .lv-o{padding:7px 7px;gap:7px;}
        .lv-on{width:22px;height:22px;font-size:10px;}
        .lv-onm{font-size:10px;}

        .ep-acts{padding-top:12px;gap:6px;}
        .btn-s{padding:10px 12px;font-size:9px;border-radius:8px;}
        .btn-c{padding:10px 12px;font-size:9px;border-radius:8px;}

        .pv-hd{font-size:8px;margin-bottom:9px;padding-bottom:7px;}
        .pv-vis{padding:10px;border-radius:8px;margin-bottom:8px;}
        .pv-top{gap:7px;margin-bottom:7px;}
        .pv-ic{width:28px;height:28px;font-size:10px;border-radius:7px;}
        .pv-chip{font-size:7px;padding:1px 5px;}
        .pv-nm{font-size:11px;}
        .pv-ds{font-size:9px;margin-top:5px;}
        .pv-mt{padding:7px 8px;border-radius:6px;}
        .pv-mi{font-size:8px;}
    }

    @media(max-width:320px){
        .ep{padding:8px 5px 36px;}
        .ep-top{margin-bottom:10px;gap:6px;}
        .ep-title{font-size:13px;}
        .ep-sub{font-size:8px;}
        .ep-logo{width:28px;height:28px;border-radius:7px;}
        .ep-logo i{font-size:11px;}
        .ep-back{width:28px;height:28px;border-radius:7px;}
        .ep-back i{font-size:11px;}
        .ep-badge{font-size:7px;padding:1px 4px;max-width:90px;}

        .ep-card{padding:10px;border-radius:9px;}
        .pv-card{padding:9px;border-radius:9px;}

        .fg{margin-bottom:11px;}
        .fl{font-size:8px;margin-bottom:5px;}

        .fi,.fs,.ft{padding:8px 9px;font-size:11px;border-radius:7px;}
        .fs{padding-right:26px;}
        .ft{min-height:52px;}

        .icon-pv{width:30px;height:30px;font-size:11px;border-radius:7px;}
        .clr-sw{width:30px;height:30px;border-radius:7px;}
        .clr-hex{padding:8px 8px;font-size:10px;border-radius:7px;}
        .clr-rst{padding:7px 7px;font-size:8px;border-radius:7px;}

        .lv-cur{padding:8px 9px;gap:6px;border-radius:7px;}
        .lv-n{width:24px;height:24px;font-size:10px;border-radius:6px;}
        .lv-nm{font-size:10px;}

        .btn-s{padding:10px 10px;font-size:9px;border-radius:7px;}
        .btn-c{padding:10px 10px;font-size:9px;border-radius:7px;}

        .pv-vis{padding:9px;border-radius:7px;}
        .pv-ic{width:26px;height:26px;font-size:10px;border-radius:6px;}
        .pv-nm{font-size:11px;}
        .pv-ds{font-size:9px;}
        .pv-mt{padding:6px 7px;}
        .pv-mi{font-size:8px;}
    }

    @media(max-height:480px) and (orientation:landscape){
        .ep{padding:10px 20px 30px;}
        .ep-grid{grid-template-columns:1fr 1fr;gap:14px;}
        .pv-w{position:static;order:0;}
        .ep-top{margin-bottom:14px;}
        .lv-dd{max-height:160px;}
    }

    @media(min-width:1600px){
        .ep-grid,.ep-top{max-width:1400px;}
    }
</style>

<div class="ep">

    <div class="ep-top">
        <a href="{{ route('departments.show', $department) }}" class="ep-back" aria-label="Back"><i class="bi bi-arrow-left"></i></a>
        <div class="ep-logo"><i class="bi bi-pencil-fill"></i></div>
        <div class="ep-info">
            <h1 class="ep-title" data-i18n="title">Редактировать отдел</h1>
            <div class="ep-sub">
                <span data-i18n="subtitle">Изменение подразделения:</span>
                <span class="ep-badge">
                    <i class="bi bi-diagram-3-fill"></i>
                    {{ $department->name }}
                </span>
            </div>
        </div>
    </div>

    <div class="ep-grid">

        <div class="ep-card">
            <form action="{{ route('departments.update', $department) }}" method="POST">
                @csrf @method('PUT')

                <div class="fg">
                    <label class="fl">
                        <i class="bi bi-layers-fill"></i>
                        <span data-i18n="levelLabel">Уровень иерархии</span>
                        <span class="rq">*</span>
                    </label>
                    <div class="lv-w" id="levelSelectWrap">
                        <div class="lv-cur" id="levelCurrent">
                            <div class="lv-n" id="lcNum">{{ $curLevel }}</div>
                            <div class="lv-i">
                                <div class="lv-nm" id="lcName">—</div>
                                <div class="lv-sb" id="lcSub">L{{ $curLevel }}</div>
                            </div>
                            <i class="bi bi-chevron-down lv-ch"></i>
                        </div>
                        <div class="lv-dd" id="levelDropdown">
                            @foreach($levelNames as $lv => $lvName)
                            <div class="lv-o" data-value="{{ $lv }}" data-name="{{ $lvName }}">
                                <div class="lv-on">{{ $lv }}</div>
                                <div class="lv-oi">
                                    <div class="lv-onm">{{ $lvName }}</div>
                                    <div class="lv-osb">L{{ $lv }}</div>
                                </div>
                                <i class="bi bi-check-lg lv-ck"></i>
                            </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="level" id="levelInput" value="{{ $curLevel }}">
                    </div>
                    @error('level')<span class="fe"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>@enderror
                </div>

                <div class="fg">
                    <label class="fl" for="name">
                        <i class="bi bi-building"></i>
                        <span data-i18n="nameLabel">Название</span>
                        <span class="rq">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $department->name) }}" class="fi" data-i18n-placeholder="namePlaceholder" placeholder="Например: IT-Дивизион" maxlength="255" required>
                    @error('name')<span class="fe"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>@enderror
                </div>

                <div class="fg" id="parentGroup">
                    <label class="fl" for="parent_id">
                        <i class="bi bi-diagram-3"></i>
                        <span data-i18n="parentLabel">Родительский отдел</span>
                    </label>
                    <select id="parent_id" name="parent_id" class="fs">
                        <option value="" data-level="0" data-i18n="parentEmpty">— Верхний уровень (без родителя) —</option>
                        @foreach($tree as $id => $label)
                        @php
                        preg_match('/\[Ур\.(\d+)\]/u', $label, $m);
                        $optLevel = (int)($m[1] ?? 0);
                        @endphp
                        <option value="{{ $id }}" data-level="{{ $optLevel }}" {{ old('parent_id', $department->parent_id) == $id ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="fh" id="parentHint">
                        <i class="bi bi-info-circle"></i>
                        <span data-i18n="parentHint1">Для уровня 1 родитель не нужен</span>
                    </span>
                    @error('parent_id')<span class="fe"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>@enderror
                </div>

                <div class="fg-row">
                    <div class="fg">
                        <label class="fl" for="icon">
                            <i class="bi bi-tag-fill"></i>
                            <span data-i18n="iconLabel">Иконка</span>
                        </label>
                        <div class="icon-w">
                            <div class="icon-pv" id="iconPreview">{{ $curIcon ?: '—' }}</div>
                            <input type="text" id="icon" name="icon" value="{{ $curIcon }}" class="fi" maxlength="4" data-i18n-placeholder="iconPlaceholder" placeholder="Символ">
                        </div>
                    </div>
                    <div class="fg">
                        <label class="fl" for="color">
                            <i class="bi bi-palette-fill"></i>
                            <span data-i18n="colorLabel">Цвет</span>
                        </label>
                        <div class="clr-w">
                            <div class="clr-sw">
                                <input type="color" id="color" name="color" value="{{ $curColor }}">
                            </div>
                            <input type="text" class="clr-hex" id="colorHex" value="{{ $curColor }}" maxlength="7" readonly>
                            <button type="button" class="clr-rst" id="colorReset" data-i18n="colorAuto">Авто</button>
                        </div>
                    </div>
                </div>

                <div class="fg">
                    <label class="fl" for="description">
                        <i class="bi bi-card-text"></i>
                        <span data-i18n="descLabel">Описание</span>
                    </label>
                    <textarea id="description" name="description" rows="3" class="ft" data-i18n-placeholder="descPlaceholder" placeholder="Краткое описание отдела..." maxlength="1000">{{ old('description', $department->description) }}</textarea>
                </div>

                <div class="ep-acts">
                    <button type="submit" class="btn-s">
                        <i class="bi bi-check-lg"></i>
                        <span data-i18n="submitBtn">Сохранить</span>
                    </button>
                    <a href="{{ route('departments.show', $department) }}" class="btn-c">
                        <i class="bi bi-x-lg"></i>
                        <span data-i18n="cancelBtn">Отмена</span>
                    </a>
                </div>
            </form>
        </div>

        <div class="pv-w">
            <div class="pv-card">
                <div class="pv-hd">
                    <i class="bi bi-eye-fill"></i>
                    <span data-i18n="previewTitle">Предпросмотр</span>
                </div>
                <div class="pv-vis" id="pvVisual" style="--pvc: {{ $curColor }}">
                    <div class="pv-top">
                        <div class="pv-ic" id="pvIcon">{{ $curIcon ?: '—' }}</div>
                        <div class="pv-tt">
                            <span class="pv-chip" id="pvLevel"><i class="bi bi-layers-fill"></i> L{{ $curLevel }}</span>
                            <h3 class="pv-nm" id="pvName">{{ $department->name }}</h3>
                        </div>
                    </div>
                    <p class="pv-ds" id="pvDesc">{{ $department->description ?: '—' }}</p>
                </div>
                <div class="pv-mt">
                    <span class="pv-mi">
                        <i class="bi bi-people-fill"></i>
                        <span><strong>{{ $department->users->count() }}</strong> <span data-i18n="members">участников</span></span>
                    </span>
                    <span class="pv-mi" id="pvParentMeta" style="{{ $department->parent ? '' : 'display:none' }}">
                        <i class="bi bi-arrow-up-right"></i>
                        <span id="pvParentText">{{ $department->parent ? $department->parent->name : '—' }}</span>
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded',function(){
    const LEVEL_NAMES=@json($levelNames);
    const LEVEL_COLORS=@json($levelColors);

    const T={
        ru:{title:'Редактировать отдел',subtitle:'Изменение подразделения:',levelLabel:'Уровень иерархии',nameLabel:'Название',namePlaceholder:'Например: IT-Дивизион',parentLabel:'Родительский отдел',parentEmpty:'— Верхний уровень (без родителя) —',parentHint1:'Для уровня 1 родитель не нужен',parentHint2:'Выберите отдел уровня',iconLabel:'Иконка',iconPlaceholder:'Символ',colorLabel:'Цвет',colorAuto:'Авто',descLabel:'Описание',descPlaceholder:'Краткое описание отдела...',submitBtn:'Сохранить',cancelBtn:'Отмена',previewTitle:'Предпросмотр',members:'участников'},
        tj:{title:'Таҳрири шӯъба',subtitle:'Тағйири зерсохтор:',levelLabel:'Сатҳи иерархия',nameLabel:'Ном',namePlaceholder:'Масалан: IT-Дивизион',parentLabel:'Шӯъбаи волидайн',parentEmpty:'— Сатҳи болоӣ (бе волидайн) —',parentHint1:'Барои сатҳи 1 волидайн лозим нест',parentHint2:'Шӯъбаи сатҳро интихоб кунед',iconLabel:'Нишона',iconPlaceholder:'Аломат',colorLabel:'Ранг',colorAuto:'Авто',descLabel:'Тавсиф',descPlaceholder:'Тавсифи мухтасари шӯъба...',submitBtn:'Нигоҳ доштан',cancelBtn:'Бекор',previewTitle:'Пешнамоиш',members:'иштирокчиён'},
        en:{title:'Edit Department',subtitle:'Editing subdivision:',levelLabel:'Hierarchy Level',nameLabel:'Name',namePlaceholder:'e.g. IT Division',parentLabel:'Parent Department',parentEmpty:'— Top level (no parent) —',parentHint1:'Level 1 does not need a parent',parentHint2:'Select department of level',iconLabel:'Icon',iconPlaceholder:'Symbol',colorLabel:'Color',colorAuto:'Auto',descLabel:'Description',descPlaceholder:'Short department description...',submitBtn:'Save',cancelBtn:'Cancel',previewTitle:'Preview',members:'members'}
    };

    let lang=localStorage.getItem('docsign_lang')||'ru';
    const d=()=>T[lang]||T.ru;

    const $=id=>document.getElementById(id);
    const levelCurrent=$('levelCurrent'),levelDropdown=$('levelDropdown'),levelInput=$('levelInput');
    const lcNum=$('lcNum'),lcName=$('lcName'),lcSub=$('lcSub');
    const opts=document.querySelectorAll('.lv-o');
    const nameInput=$('name'),descInput=$('description'),parentSel=$('parent_id');
    const parentGrp=$('parentGroup'),parentHint=document.querySelector('#parentHint span');
    const parentOpts=parentSel.querySelectorAll('option');
    const iconInput=$('icon'),iconPreview=$('iconPreview');
    const colorInput=$('color'),colorHex=$('colorHex'),colorReset=$('colorReset');
    const pvVisual=$('pvVisual'),pvIcon=$('pvIcon'),pvLevel=$('pvLevel');
    const pvName=$('pvName'),pvDesc=$('pvDesc');
    const pvParentMeta=$('pvParentMeta'),pvParentText=$('pvParentText');

    let colorTouched=true;
    const getLv=()=>parseInt(levelInput.value)||1;

    levelCurrent.addEventListener('click',e=>{
        e.stopPropagation();
        const o=levelCurrent.classList.contains('open');
        levelCurrent.classList.toggle('open',!o);
        levelDropdown.classList.toggle('open',!o);
    });

    document.addEventListener('click',e=>{
        if(!$('levelSelectWrap').contains(e.target)){
            levelCurrent.classList.remove('open');
            levelDropdown.classList.remove('open');
        }
    });

    document.addEventListener('keydown',e=>{
        if(e.key==='Escape'){levelCurrent.classList.remove('open');levelDropdown.classList.remove('open');}
    });

    opts.forEach(o=>o.addEventListener('click',()=>{
        levelInput.value=o.dataset.value;
        opts.forEach(x=>x.classList.remove('sel'));
        o.classList.add('sel');
        levelCurrent.classList.remove('open');
        levelDropdown.classList.remove('open');
        if(!colorTouched)autoColor(getLv());
        refresh();
    }));

    iconInput.addEventListener('input',()=>{iconPreview.textContent=iconInput.value||'—';preview();});

    colorInput.addEventListener('input',()=>{colorTouched=true;colorHex.value=colorInput.value.toUpperCase();preview();});
    colorReset.addEventListener('click',()=>{colorTouched=false;autoColor(getLv());preview();});

    function autoColor(lv){const c=LEVEL_COLORS[lv]||'#4f8cff';colorInput.value=c;colorHex.value=c.toUpperCase();}

    function filterParent(){
        const lv=getLv(),need=lv-1;
        parentOpts.forEach(o=>{
            if(o.value===''){o.style.display=lv===1?'':'none';return;}
            o.style.display=parseInt(o.dataset.level)===need?'':'none';
        });
        if(lv===1){
            parentGrp.classList.add('dis');parentSel.value='';parentSel.removeAttribute('required');
            if(parentHint)parentHint.textContent=d().parentHint1;
        }else{
            parentGrp.classList.remove('dis');parentSel.setAttribute('required','required');
            if(parentHint)parentHint.textContent=d().parentHint2+' '+need;
            const s=parentSel.selectedOptions[0];
            if(!s||s.style.display==='none')parentSel.value='';
        }
    }

    function preview(){
        const lv=getLv();
        pvLevel.innerHTML='<i class="bi bi-layers-fill"></i> L'+lv;
        pvVisual.style.setProperty('--pvc',colorInput.value);
        pvIcon.textContent=iconInput.value||'—';
        pvName.textContent=nameInput.value.trim()||'—';
        pvDesc.textContent=descInput.value.trim()||'—';
        if(parentSel.value&&parentSel.selectedIndex>0){
            pvParentMeta.style.display='inline-flex';
            pvParentText.textContent=parentSel.options[parentSel.selectedIndex].text.replace(/\[.*?\]/g,'').replace(/[│├─]/g,'').trim();
        }else{pvParentMeta.style.display='none';}
    }

    function refresh(){
        const lv=getLv();
        lcNum.textContent=lv;
        lcName.textContent=LEVEL_NAMES[lv]||('Уровень '+lv);
        lcSub.textContent='L'+lv;
        filterParent();preview();
    }

    [nameInput,descInput,parentSel].forEach(el=>{el.addEventListener('input',preview);el.addEventListener('change',preview);});

    function applyLang(l){
        lang=l;const t=d();
        document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.getAttribute('data-i18n');if(t[k]!==undefined)el.textContent=t[k];});
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el=>{const k=el.getAttribute('data-i18n-placeholder');if(t[k]!==undefined)el.setAttribute('placeholder',t[k]);});
        refresh();
    }

    (function(){
        const c=levelInput.value;
        opts.forEach(o=>{if(o.dataset.value===c){o.classList.add('sel');lcName.textContent=o.dataset.name;}});
    })();

    colorHex.value=colorInput.value.toUpperCase();
    applyLang(lang);

    window.addEventListener('docsign:lang-changed',e=>applyLang(e.detail?.lang||'ru'));
    window.addEventListener('storage',e=>{if(e.key==='docsign_lang'&&e.newValue)applyLang(e.newValue);});
});
</script>

@endsection