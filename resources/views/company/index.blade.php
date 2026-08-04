@extends('layouts.admin')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@php
    // Безопасно получаем корень (первую компанию без родителя)
    $root = collect($tree)->first(fn($c) => $c->isRoot());
    
    // Считаем статистику через методы коллекции
    $rootsN  = collect($tree)->filter(fn($c) => $c->isRoot())->count();
    $childN  = collect($tree)->filter(fn($c) => !$c->isRoot())->count();
    
    // Уровни считаем по реальному полю level из БД
    $levelsN = collect($tree)->pluck('level')->unique()->count();

    $me        = auth()->user();
    $myCompany = $me->companyRelation;
    $canAdd    = $myCompany && $me->canManageCompany($myCompany);
@endphp

<style>
    :root {
        --line-strong: rgba(var(--glow), 0.5);
        --line-soft: rgba(var(--glow), 0.2);
    }

    .ot-page {
        min-height: 100vh;
        padding: 40px 24px 60px;
        color: var(--text);
        font-family: 'Inter', sans-serif;
        position: relative;
        --card-scale: 1;
    }

    .ot-blob { position: absolute; border-radius: 50%; pointer-events: none; z-index: 0; filter: blur(100px); opacity: .35; }
    .ot-blob-1 { top: -100px; left: -100px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(var(--glow), .3) 0%, transparent 70%); animation: otBlob 20s ease-in-out infinite; }
    .ot-blob-2 { bottom: -100px; right: -100px; width: 600px; height: 600px; background: radial-gradient(circle, rgba(168, 85, 247, .25) 0%, transparent 70%); animation: otBlob 25s ease-in-out infinite reverse; }
    .ot-blob-3 { top: 50%; left: 50%; width: 400px; height: 400px; transform: translate(-50%, -50%); background: radial-gradient(circle, rgba(236, 72, 153, .2) 0%, transparent 70%); animation: otBlob3 30s ease-in-out infinite; }
    @keyframes otBlob { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(30px, -30px); } }
    @keyframes otBlob3 { 0%, 100% { transform: translate(-50%, -50%); } 50% { transform: translate(calc(-50% + 30px), calc(-50% - 30px)); } }

    .ot-topbar { max-width: 1400px; margin: 0 auto 28px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; position: relative; z-index: 1; }
    .ot-topbar-left { display: flex; align-items: center; gap: 16px; min-width: 0; flex: 1; }
    .ot-topbar-icon { width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, rgba(var(--glow), .95), rgba(var(--glow), .4)); display: grid; place-items: center; box-shadow: 0 0 24px rgba(var(--glow), .5), inset 0 0 12px rgba(255, 255, 255, .2); flex-shrink: 0; }
    .ot-topbar-icon svg { width: 26px; height: 26px; color: #0a0d14; }
    .ot-topbar-title { font-size: 24px; font-weight: 800; color: var(--text); margin: 0; line-height: 1.2; }
    .ot-topbar-subtitle { font-size: 13px; color: var(--muted); font-weight: 600; margin-top: 3px; }

    .ot-topbar-right { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

    .ot-btn-add { display: inline-flex; align-items: center; gap: 8px; padding: 12px 22px; background: linear-gradient(180deg, rgba(var(--glow), .95), rgba(var(--glow), .65)); color: #0a0d14; border-radius: 10px; font-size: 12px; font-weight: 700; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; transition: all .25s ease; box-shadow: 0 8px 24px rgba(var(--glow), .35); border: 1px solid transparent; white-space: nowrap; }
    .ot-btn-add:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(var(--glow), .5); filter: brightness(1.08); }
    .ot-btn-add svg { width: 16px; height: 16px; }

    /* ===== ЗУМ-ТУЛБАР ===== */
    .zoom-toolbar {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 6px;
        flex-shrink: 0;
    }
    .zoom-btn {
        width: 34px; height: 34px;
        border-radius: 8px;
        border: 1px solid rgba(var(--glow), 0.35);
        background: rgba(var(--glow), 0.12);
        color: var(--text);
        font-size: 18px;
        font-weight: 800;
        cursor: pointer;
        display: grid;
        place-items: center;
        transition: all 0.15s;
        line-height: 1;
    }
    .zoom-btn:hover { background: rgba(var(--glow), 0.25); }
    .zoom-btn:active { transform: scale(0.92); }
    .zoom-level {
        min-width: 46px;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        color: var(--muted);
    }

    .ot-stats { max-width: 1400px; margin: 0 auto 32px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; position: relative; z-index: 1; }
    @media(min-width: 768px) { .ot-stats { grid-template-columns: repeat(4, 1fr); } }
    .ot-stat { position: relative; background: linear-gradient(180deg, rgba(255, 255, 255, .05), rgba(255, 255, 255, .02)); border: 1px solid var(--line); border-radius: var(--radius); padding: 18px; overflow: hidden; transition: all .3s ease; display: flex; align-items: center; gap: 14px; }
    .ot-stat:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -20px rgba(var(--glow), .3); }
    .ot-stat-icon { width: 36px; height: 36px; border-radius: 10px; display: grid; place-items: center; border: 1px solid; flex-shrink: 0; }
    .ot-stat-icon svg { width: 20px; height: 20px; }
    .ot-stat-icon.s1 { background: rgba(var(--glow), .18); border-color: rgba(var(--glow), .35); color: rgba(var(--glow), 1); }
    .ot-stat-icon.s2 { background: rgba(168, 85, 247, .18); border-color: rgba(168, 85, 247, .35); color: #a855f7; }
    .ot-stat-icon.s3 { background: rgba(76, 217, 130, .18); border-color: rgba(76, 217, 130, .35); color: #4cd982; }
    .ot-stat-icon.s4 { background: rgba(255, 181, 71, .18); border-color: rgba(255, 181, 71, .35); color: #ffb547; }
    .ot-stat-value { font-size: 24px; font-weight: 800; color: var(--text); line-height: 1; }
    .ot-stat-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: .8px; font-weight: 600; margin-top: 4px; }

    .ot-tree-wrap { max-width: 1400px; margin: 0 auto; position: relative; z-index: 1; background: linear-gradient(180deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .02)); border: 1px solid var(--line); border-radius: var(--radius); padding: 40px 24px; overflow-x: auto; }
    .ot-tree-header { text-align: center; margin-bottom: 36px; }
    .ot-tree-header h2 { font-size: 24px; font-weight: 800; color: var(--text); margin: 0 0 8px; }
    .ot-tree-header p { font-size: 13px; color: var(--muted); margin: 0; }

    .org-tree { display: flex; justify-content: center; min-width: fit-content; padding: 10px 0; }
    .org-node { display: flex; flex-direction: column; align-items: center; position: relative; padding-top: calc(24px * var(--card-scale)); }
    .org-node::before { content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 2px; height: calc(24px * var(--card-scale)); background: var(--line-strong); }
    .org-node::after { content: ''; position: absolute; top: 0; height: 2px; background: var(--line-strong); }
    .org-node:first-child::after { left: 50%; width: 50%; }
    .org-node:last-child::after { right: 50%; width: 50%; }
    .org-node:not(:first-child):not(:last-child)::after { left: 0; width: 100%; }
    .org-node:only-child::after { display: none; }
    .org-tree > .org-node::before, .org-tree > .org-node::after { display: none; }
    .org-tree > .org-node { padding-top: 0; }
    .org-children { display: flex; justify-content: center; gap: calc(20px * var(--card-scale)); padding-top: calc(24px * var(--card-scale)); position: relative; }
    .org-children::before { content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 2px; height: calc(24px * var(--card-scale)); background: var(--line-strong); }
    .org-dot { position: absolute; top: -5px; left: 50%; transform: translateX(-50%); width: calc(10px * var(--card-scale)); height: calc(10px * var(--card-scale)); border-radius: 50%; background: #0a0d14; border: 2px solid var(--line-strong); box-shadow: 0 0 10px rgba(var(--glow), .5); z-index: 3; }
    .org-tree > .org-node > .org-card .org-dot { display: none; }

    /* ===== КАРТОЧКА — масштабируется через --card-scale ===== */
    .org-card {
        position: relative;
        width: calc(190px * var(--card-scale));
        background: linear-gradient(180deg, rgba(255, 255, 255, .06), rgba(255, 255, 255, .02));
        border: 1px solid var(--line);
        border-radius: calc(12px * var(--card-scale));
        padding: calc(12px * var(--card-scale));
        transition: all .3s cubic-bezier(.4, 0, .2, 1);
        z-index: 2;
    }
    .org-card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, rgba(var(--glow), .9), transparent); opacity: 0; transition: opacity .3s ease; border-radius: 12px 12px 0 0; }
    .org-card:hover::before { opacity: 1; }
    .org-card:hover { border-color: rgba(var(--glow), .5); box-shadow: 0 16px 36px -12px rgba(var(--glow), .45), 0 0 0 1px rgba(var(--glow), .12); transform: translateY(-3px) scale(1.02); }

    .oc-top { display: flex; align-items: center; gap: calc(8px * var(--card-scale)); margin-bottom: calc(8px * var(--card-scale)); }
    .oc-avatar {
        width: calc(34px * var(--card-scale));
        height: calc(34px * var(--card-scale));
        border-radius: calc(9px * var(--card-scale));
        display: grid; place-items: center;
        font-size: calc(13px * var(--card-scale));
        font-weight: 900; font-style: italic;
        color: rgba(255, 255, 255, .95);
        text-shadow: 0 2px 8px rgba(0, 0, 0, .4);
        background: linear-gradient(135deg, rgba(var(--glow), .5), rgba(168, 85, 247, .4));
        border: 1px solid rgba(255, 255, 255, .15);
        flex-shrink: 0;
    }
    .org-tree > .org-node > .org-card .oc-avatar { background: linear-gradient(135deg, rgba(var(--glow), .8), rgba(var(--glow), .4)); box-shadow: 0 0 14px rgba(var(--glow), .5); }
    .oc-badges { display: flex; flex-direction: column; gap: calc(3px * var(--card-scale)); margin-left: auto; align-items: flex-end; }
    .oc-lvl {
        display: inline-flex; align-items: center; gap: 3px;
        padding: calc(2px * var(--card-scale)) calc(6px * var(--card-scale));
        border-radius: 5px;
        font-size: calc(8px * var(--card-scale));
        font-weight: 800; font-family: 'JetBrains Mono', monospace;
        background: rgba(var(--glow), .9); color: #0a0d14;
        box-shadow: 0 0 10px rgba(var(--glow), .5);
    }
    .oc-status { display: inline-flex; align-items: center; gap: 4px; font-size: calc(8px * var(--card-scale)); font-weight: 700; text-transform: uppercase; letter-spacing: .3px; }
    .oc-status.on { color: #4cd982; }
    .oc-status.off { color: #8892a6; }
    .oc-status-dot { width: calc(5px * var(--card-scale)); height: calc(5px * var(--card-scale)); border-radius: 50%; background: currentColor; }
    .oc-status.on .oc-status-dot { box-shadow: 0 0 8px currentColor; animation: otPulse 2s infinite; }
    @keyframes otPulse { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }

    .oc-name { font-size: calc(12px * var(--card-scale)); font-weight: 800; color: var(--text); text-align: center; margin: 0 0 calc(4px * var(--card-scale)); line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .oc-name a { color: inherit; text-decoration: none; transition: all .2s ease; }
    .oc-name a:hover { color: rgba(var(--glow), 1); text-shadow: 0 0 10px rgba(var(--glow), .5); }
    .oc-type {
        display: block; text-align: center;
        padding: calc(3px * var(--card-scale)) calc(6px * var(--card-scale));
        border-radius: 5px;
        font-size: calc(8px * var(--card-scale));
        font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
        color: rgba(var(--glow), 1);
        background: rgba(var(--glow), .12);
        border: 1px solid rgba(var(--glow), .25);
        margin-bottom: calc(6px * var(--card-scale));
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .oc-city { display: flex; align-items: center; justify-content: center; gap: 4px; font-size: calc(9px * var(--card-scale)); color: var(--muted); font-weight: 500; margin-bottom: calc(6px * var(--card-scale)); }
    .oc-city i { font-size: calc(9px * var(--card-scale)); color: rgba(var(--glow), .7); }
    .oc-city span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: calc(130px * var(--card-scale)); }
    .oc-parent {
        display: flex; align-items: center; gap: 4px;
        padding: calc(4px * var(--card-scale)) calc(6px * var(--card-scale));
        border-radius: 5px;
        background: rgba(255, 255, 255, .04);
        border-left: 2px solid rgba(var(--glow), .6);
        font-size: calc(8px * var(--card-scale));
        color: var(--muted); font-weight: 600;
        margin-bottom: calc(6px * var(--card-scale));
        overflow: hidden;
    }
    .oc-parent i { color: rgba(var(--glow), .8); font-size: calc(9px * var(--card-scale)); flex-shrink: 0; }
    .oc-parent strong { color: var(--text); font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .oc-root-tag {
        display: flex; align-items: center; justify-content: center; gap: 3px;
        padding: calc(3px * var(--card-scale)) calc(6px * var(--card-scale));
        border-radius: 5px;
        font-size: calc(8px * var(--card-scale));
        font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
        background: rgba(255, 181, 71, .15); color: #ffb547;
        border: 1px solid rgba(255, 181, 71, .3);
        margin-bottom: calc(6px * var(--card-scale));
    }
    .oc-children-badge {
        display: flex; align-items: center; justify-content: center; gap: 4px;
        padding: calc(3px * var(--card-scale)) calc(6px * var(--card-scale));
        border-radius: 5px;
        font-size: calc(8px * var(--card-scale));
        font-weight: 700;
        background: rgba(76, 217, 130, .08);
        border: 1px solid rgba(76, 217, 130, .2);
        color: #4cd982;
        margin-bottom: calc(6px * var(--card-scale));
    }
    .oc-children-badge strong { font-size: calc(10px * var(--card-scale)); font-weight: 800; }
    .oc-actions { display: flex; gap: calc(4px * var(--card-scale)); padding-top: calc(8px * var(--card-scale)); border-top: 1px solid var(--line); }
    .oc-act {
        flex: 1; display: grid; place-items: center; width: 100%;
        height: calc(26px * var(--card-scale));
        border-radius: 6px; border: 1px solid var(--line);
        background: rgba(255, 255, 255, .03); color: var(--muted);
        text-decoration: none; transition: all .2s ease; cursor: pointer;
        font-size: calc(11px * var(--card-scale));
    }
    .oc-act.view:hover { background: rgba(var(--glow), .15); border-color: rgba(var(--glow), .5); color: rgba(var(--glow), 1); }
    .oc-act.add:hover { background: rgba(76, 217, 130, .15); border-color: rgba(76, 217, 130, .5); color: #4cd982; }
    .oc-act.edit:hover { background: rgba(255, 181, 71, .15); border-color: rgba(255, 181, 71, .5); color: #ffb547; }
    .oc-act.del:hover { background: rgba(255, 99, 99, .15); border-color: rgba(255, 99, 99, .5); color: #ff6363; }

    .ot-empty { text-align: center; padding: 70px 20px; }
    .ot-empty-icon { width: 90px; height: 90px; border-radius: 24px; background: rgba(255, 255, 255, .05); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; border: 1px solid var(--line); }
    .ot-empty-icon i { font-size: 40px; color: var(--muted); }
    .ot-empty-title { font-size: 22px; font-weight: 800; color: var(--text); margin-bottom: 10px; }
    .ot-empty-desc { font-size: 14px; color: var(--muted); }
    .ot-flash { max-width: 1400px; margin: 0 auto 18px; font-size: 13px; font-weight: 600; padding: 12px 16px; border-radius: 10px; border: 1px solid; position: relative; z-index: 1; }
    .ot-flash.ok { color: #4cd982; background: rgba(76, 217, 130, .08); border-color: rgba(76, 217, 130, .25); }
    .ot-flash.err { color: #ff7a7a; background: rgba(255, 122, 122, .08); border-color: rgba(255, 122, 122, .25); }

    /* ===== АДАПТИВ ===== */
    @media (max-width: 768px) {
        .ot-page { padding: 28px 16px 45px; }
        .ot-topbar { flex-direction: column; align-items: stretch; }
        .ot-topbar-right { justify-content: center; }
        .ot-btn-add { width: 100%; justify-content: center; }
        .ot-stat { padding: 14px; gap: 10px; }
        .ot-stat-value { font-size: 20px; }
        .ot-tree-wrap { padding: 28px 12px; }
        .zoom-btn { width: 30px; height: 30px; font-size: 16px; }
        .zoom-level { min-width: 40px; font-size: 11px; }
    }
    @media (max-width: 480px) {
        .ot-page { padding: 20px 12px 36px; }
        .ot-topbar-title { font-size: 18px; }
        .ot-stat { padding: 12px; }
        .ot-stat-icon { width: 30px; height: 30px; }
        .ot-stat-icon svg { width: 16px; height: 16px; }
        .ot-stat-value { font-size: 18px; }
        .ot-tree-wrap { padding: 20px 8px; }
        .ot-tree-header h2 { font-size: 18px; }
        .zoom-toolbar { padding: 4px; gap: 5px; }
        .zoom-btn { width: 28px; height: 28px; font-size: 14px; border-radius: 6px; }
        .zoom-level { min-width: 36px; font-size: 10px; }
    }
</style>

<div class="ot-page" id="otPage">
    <div class="ot-blob ot-blob-1"></div>
    <div class="ot-blob ot-blob-2"></div>
    <div class="ot-blob ot-blob-3"></div>

    @if(session('success')) <div class="ot-flash ok">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="ot-flash err">{{ session('error') }}</div> @endif

    {{-- TOPBAR --}}
    <div class="ot-topbar">
        <div class="ot-topbar-left">
            <div class="ot-topbar-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
            </div>
            <div>
                @if($root)
                    <div class="ot-topbar-title">{{ $root->name }}</div>
                @else
                    <div class="ot-topbar-title" data-i18n="ot_title">Дерево компаний</div>
                @endif
                <div class="ot-topbar-subtitle" data-i18n="ot_subtitle">Управление иерархической структурой</div>
            </div>
        </div>

        <div class="ot-topbar-right">
            {{-- ЗУМ --}}
            <div class="zoom-toolbar">
                <button class="zoom-btn" id="zoomOutBtn" type="button" title="Уменьшить">−</button>
                <span class="zoom-level" id="zoomLevelLabel">100%</span>
                <button class="zoom-btn" id="zoomInBtn" type="button" title="Увеличить">+</button>
            </div>

            @if($canAdd)
                <a href="{{ route('companies.create', ['parent' => $myCompany->id]) }}" class="ot-btn-add">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span data-i18n="ot_add_company">Добавить компанию</span>
                </a>
            @endif
        </div>
    </div>

    {{-- STATS --}}
    <div class="ot-stats">
        <div class="ot-stat">
            <div class="ot-stat-icon s1"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15"/></svg></div>
            <div><div class="ot-stat-value">{{ count($tree) }}</div><div class="ot-stat-label" data-i18n="ot_stat_total">Всего компаний</div></div>
        </div>
        <div class="ot-stat">
            <div class="ot-stat-icon s2"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg></div>
            <div><div class="ot-stat-value">{{ $rootsN }}</div><div class="ot-stat-label" data-i18n="ot_stat_roots">Корневых</div></div>
        </div>
        <div class="ot-stat">
            <div class="ot-stat-icon s3"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg></div>
            <div><div class="ot-stat-value">{{ $levelsN }}</div><div class="ot-stat-label" data-i18n="ot_stat_levels">Уровней</div></div>
        </div>
        <div class="ot-stat">
            <div class="ot-stat-icon s4"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg></div>
            <div><div class="ot-stat-value">{{ $childN }}</div><div class="ot-stat-label" data-i18n="ot_stat_children">Подразделений</div></div>
        </div>
    </div>

    {{-- TREE --}}
    <div class="ot-tree-wrap">
        <div class="ot-tree-header">
            <h2 data-i18n="ot_tree_title">Иерархическое дерево</h2>
            <p data-i18n="ot_tree_sub">Визуальная структура подчинённости компаний</p>
        </div>

        @if($nestedTree && $nestedTree->count() > 0)
            <div class="org-tree">
                @foreach($nestedTree as $node)
                    @include('company._tree_node', ['node' => $node])
                @endforeach
            </div>
        @else
            <div class="ot-empty">
                <div class="ot-empty-icon"><i class="bi bi-building"></i></div>
                <div class="ot-empty-title" data-i18n="ot_empty_title">Нет компаний</div>
                <div class="ot-empty-desc" data-i18n="ot_empty_desc">Зарегистрируйте первую компанию, чтобы построить дерево</div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ===== ЗУМ =====
    const ZOOM_MIN = 0.4;
    const ZOOM_MAX = 1.5;
    const ZOOM_STEP = 0.1;
    let currentScale = 1;

    function computeAutoScale(count) {
        if (count <= 6)  return 1.0;
        if (count <= 12) return 0.9;
        if (count <= 20) return 0.75;
        if (count <= 35) return 0.6;
        if (count <= 50) return 0.5;
        return 0.4;
    }

    function applyScale(scale) {
        currentScale = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, scale));
        const page = document.getElementById('otPage');
        if (page) page.style.setProperty('--card-scale', currentScale);
        const label = document.getElementById('zoomLevelLabel');
        if (label) label.textContent = Math.round(currentScale * 100) + '%';
    }

    const cardCount = document.querySelectorAll('.org-card').length;
    applyScale(computeAutoScale(cardCount));

    const zoomInBtn  = document.getElementById('zoomInBtn');
    const zoomOutBtn = document.getElementById('zoomOutBtn');

    if (zoomInBtn) {
        zoomInBtn.addEventListener('click', function () {
            applyScale(currentScale + ZOOM_STEP);
        });
    }
    if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', function () {
            applyScale(currentScale - ZOOM_STEP);
        });
    }

    // ===== ПЕРЕВОДЫ =====
    const translations = {
        ru: {
            ot_title: "Дерево компаний", ot_subtitle: "Управление иерархической структурой",
            ot_add_company: "Добавить компанию",
            ot_stat_total: "Всего компаний", ot_stat_roots: "Корневых", ot_stat_levels: "Уровней", ot_stat_children: "Подразделений",
            ot_tree_title: "Иерархическое дерево", ot_tree_sub: "Визуальная структура подчинённости компаний",
            ot_empty_title: "Нет компаний", ot_empty_desc: "Зарегистрируйте первую компанию, чтобы построить дерево",
            oc_active: "Активна", oc_inactive: "Неактивна", oc_root: "★ Корень",
            oc_parent: "Родитель:", oc_children: "Подразделений:", oc_add_child: "Подразделение",
            oc_confirm_delete: "Удалить компанию? Это действие необратимо."
        },
        tj: {
            ot_title: "Дарахти ширкатҳо", ot_subtitle: "Идоракунии сохтори иерархӣ",
            ot_add_company: "Илова кардани ширкат",
            ot_stat_total: "Ҳамагӣ ширкатҳо", ot_stat_roots: "Асосӣ", ot_stat_levels: "Сатҳҳо", ot_stat_children: "Зерсохторҳо",
            ot_tree_title: "Дарахти иерархия", ot_tree_sub: "Сохтори визуалии тобеияти ширкатҳо",
            ot_empty_title: "Ширкатҳо нестанд", ot_empty_desc: "Ширкати аввалинро бақайд гиред, то дарахт созед",
            oc_active: "Фаъол", oc_inactive: "Ғайрифаъол", oc_root: "★ Асосӣ",
            oc_parent: "Волид:", oc_children: "Зерсохторҳо:", oc_add_child: "Зерсохтор",
            oc_confirm_delete: "Ширкатро нест мекунед? Ин амал бозгашт надорад."
        },
        en: {
            ot_title: "Company Tree", ot_subtitle: "Hierarchical structure management",
            ot_add_company: "Add Company",
            ot_stat_total: "Total Companies", ot_stat_roots: "Roots", ot_stat_levels: "Levels", ot_stat_children: "Subdivisions",
            ot_tree_title: "Hierarchy Tree", ot_tree_sub: "Visual structure of company subordination",
            ot_empty_title: "No Companies", ot_empty_desc: "Register your first company to build the tree",
            oc_active: "Active", oc_inactive: "Inactive", oc_root: "★ Root",
            oc_parent: "Parent:", oc_children: "Subdivisions:", oc_add_child: "Subdivision",
            oc_confirm_delete: "Delete company? This action cannot be undone."
        }
    };

    function getCurrentLang() {
        return localStorage.getItem('docsign_lang') || localStorage.getItem('app-lang') || 'ru';
    }

    function applyTranslations() {
        const lang = getCurrentLang();
        const t = translations[lang] || translations['ru'];
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (t[key] !== undefined) el.textContent = t[key];
        });
        document.querySelectorAll('[data-i18n-title]').forEach(el => {
            const key = el.getAttribute('data-i18n-title');
            if (t[key] !== undefined) el.setAttribute('title', t[key]);
        });
        document.querySelectorAll('[data-confirm-i18n]').forEach(el => {
            const key = el.getAttribute('data-confirm-i18n');
            const msg = t[key] || 'Are you sure?';
            const fresh = el.cloneNode(true);
            el.parentNode.replaceChild(fresh, el);
            fresh.onsubmit = (e) => { if (!confirm(msg)) e.preventDefault(); };
        });
    }

    applyTranslations();
    window.addEventListener('docsign:lang-changed', function(e) {
        if (e.detail && e.detail.lang) {
            localStorage.setItem('docsign_lang', e.detail.lang);
            localStorage.setItem('app-lang', e.detail.lang);
        }
        applyTranslations();
    });
    window.addEventListener('storage', function(e) {
        if (e.key === 'docsign_lang' && e.newValue) applyTranslations();
    });

    // ===== БЛОБЫ =====
    const blobs = document.querySelectorAll('.ot-blob');
    document.addEventListener('mousemove', (e) => {
        const x = (e.clientX / window.innerWidth - 0.5) * 30;
        const y = (e.clientY / window.innerHeight - 0.5) * 30;
        blobs.forEach((b, i) => {
            const f = (i + 1) * 0.4;
            b.style.transform = i === 2
                ? `translate(calc(-50% + ${x * f}px), calc(-50% + ${y * f}px))`
                : `translate(${x * f}px, ${y * f}px)`;
        });
    });
});
</script>
@endsection