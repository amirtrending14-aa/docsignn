@extends('layouts.admin')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@php
    // Маппинг машинных ключей типа → русские названия
    $typeLabels = [
        'ministry'           => 'Правительство и министерства',
        'local_government'   => 'Местное самоуправление',
        'law_enforcement'    => 'Силовые структуры',
        'special_agency'     => 'Специализированные агентства',
        'education'          => 'Образование',
        'healthcare'         => 'Здравоохранение',
        'social_protection'  => 'Социальная защита',
        'bank'               => 'Финансы',
        'business_services'  => 'Деловые услуги',
        'it_development'     => 'IT и разработка',
        'retail'             => 'Торговля',
        'catering'           => 'Общепит',
        'manufacturing'      => 'Промышленность',
        'construction'       => 'Строительство',
        'household_services' => 'Бытовые услуги',
        'hospitality'        => 'Гостиничный бизнес',
        'sport_leisure'      => 'Спорт и досуг',
        'utilities'          => 'ЖКХ',
        'transport'          => 'Транспорт',
        'communication'      => 'Связь',
    ];
    $typeLabel = $typeLabels[$company->type] ?? ($company->type ?? '—');
    $statusActive = strtolower($company->status ?? '') === 'active';
    $canManage = auth()->user()->canManageCompany($company);
@endphp

<style>
    .cp-page{min-height:100vh;padding:40px 24px 60px;color:var(--text,#e7ecf3);font-family:'Inter',sans-serif;position:relative;}

    .cp-blob{position:absolute;border-radius:50%;pointer-events:none;z-index:0;filter:blur(100px);opacity:.35;}
    .cp-blob-1{top:-120px;left:-120px;width:500px;height:500px;background:radial-gradient(circle,rgba(var(--glow),.35) 0%,transparent 70%);animation:cpBlob 20s ease-in-out infinite;}
    .cp-blob-2{bottom:-120px;right:-120px;width:600px;height:600px;background:radial-gradient(circle,rgba(168,85,247,.28) 0%,transparent 70%);animation:cpBlob 25s ease-in-out infinite reverse;}
    .cp-blob-3{top:40%;left:60%;width:400px;height:400px;background:radial-gradient(circle,rgba(236,72,153,.22) 0%,transparent 70%);animation:cpBlob3 30s ease-in-out infinite;}
    @keyframes cpBlob{0%,100%{transform:translate(0,0);}50%{transform:translate(30px,-30px);}}
    @keyframes cpBlob3{0%,100%{transform:translate(0,0);}50%{transform:translate(-30px,30px);}}

    .cp-wrap{max-width:1100px;margin:0 auto;position:relative;z-index:1;}

    .cp-flash{margin-bottom:18px;font-size:13px;font-weight:600;padding:12px 16px;border-radius:10px;border:1px solid;}
    .cp-flash.ok{color:#4cd982;background:rgba(76,217,130,.08);border-color:rgba(76,217,130,.25);}
    .cp-flash.err{color:#ff7a7a;background:rgba(255,122,122,.08);border-color:rgba(255,122,122,.25);}

    .page-head-custom{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px;background:linear-gradient(180deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid var(--line);border-radius:var(--radius);padding:18px 22px;position:relative;}
    .page-head-custom::before{content:"";position:absolute;inset:-1px;border-radius:var(--radius);padding:1px;background:linear-gradient(135deg,rgba(var(--glow),.4),transparent 40%,transparent 60%,rgba(var(--glow),.2));-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;opacity:.6;pointer-events:none;}
    .cp-head-left{display:flex;align-items:center;gap:14px;min-width:0;flex:1;}
    .cp-head-icon{width:48px;height:48px;border-radius:13px;background:linear-gradient(135deg,rgba(var(--glow),.95),rgba(var(--glow),.4));display:grid;place-items:center;flex-shrink:0;box-shadow:0 0 24px rgba(var(--glow),.5),inset 0 0 12px rgba(255,255,255,.2);font-size:18px;font-weight:900;font-style:italic;color:#0a0d14;}
    .cp-page h1{margin:0;font-size:20px;font-weight:800;letter-spacing:-.3px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .cp-page .head-sub{font-size:12px;color:var(--muted);margin-top:4px;font-weight:500;}
    .cp-btns{display:flex;gap:8px;flex-wrap:wrap;}
    .cp-btn{appearance:none;border:1px solid var(--line);background:rgba(255,255,255,.04);color:var(--text);font:700 11px 'Inter',sans-serif;padding:10px 16px;border-radius:9px;cursor:pointer;display:inline-flex;align-items:center;gap:7px;text-decoration:none;transition:all .2s;text-transform:uppercase;letter-spacing:.6px;white-space:nowrap;}
    .cp-btn i{font-size:13px;}
    .cp-btn:hover{border-color:rgba(var(--glow),.4);background:rgba(var(--glow),.1);color:rgba(var(--glow),1);box-shadow:0 0 12px rgba(var(--glow),.2);transform:translateY(-1px);}
    .cp-btn.add{color:#4cd982;border-color:rgba(76,217,130,.3);}
    .cp-btn.add:hover{border-color:rgba(76,217,130,.5);background:rgba(76,217,130,.1);box-shadow:0 0 12px rgba(76,217,130,.25);color:#4cd982;}
    .cp-btn.edit{color:#ffb547;border-color:rgba(255,181,71,.3);}
    .cp-btn.edit:hover{border-color:rgba(255,181,71,.5);background:rgba(255,181,71,.1);box-shadow:0 0 12px rgba(255,181,71,.25);color:#ffb547;}

    .tag{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:6px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;border:1px solid;}
    .tag-root{color:#ffb547;background:rgba(255,181,71,.12);border-color:rgba(255,181,71,.3);}

    .cp-grid{display:grid;grid-template-columns:2fr 1fr;gap:20px;}
    @media(max-width:900px){.cp-grid{grid-template-columns:1fr;}}
    .cp-card{background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.015));border:1px solid var(--line);border-radius:14px;padding:22px;position:relative;overflow:hidden;margin-bottom:20px;}
    .cp-card::before{content:"";position:absolute;inset:-1px;border-radius:14px;padding:1px;background:linear-gradient(135deg,rgba(var(--glow),.4),transparent 50%);-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none;opacity:.6;}
    .cp-card h3{font-size:12px;font-weight:800;color:var(--text);text-transform:uppercase;letter-spacing:1px;margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .cp-card h3 i{color:rgba(var(--glow),1);}
    .cp-card h3 .count{margin-left:auto;font-size:11px;font-weight:800;color:rgba(var(--glow),1);background:rgba(var(--glow),.12);border:1px solid rgba(var(--glow),.3);padding:2px 9px;border-radius:6px;}

    .cp-info{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
    @media(max-width:520px){.cp-info{grid-template-columns:1fr;}}
    .cp-info .cell{background:rgba(255,255,255,.03);border:1px solid var(--line);border-radius:9px;padding:12px 14px;transition:all .2s;}
    .cp-info .cell:hover{border-color:rgba(var(--glow),.3);background:rgba(var(--glow),.04);}
    .cp-info .cell .k{font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;margin-bottom:5px;font-weight:700;}
    .cp-info .cell .v{font-size:13px;color:var(--text);font-weight:600;word-break:break-word;}

    .status-pill{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;border:1px solid;}
    .status-pill::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor;box-shadow:0 0 8px currentColor;}
    .status-active{color:#4cd982;background:rgba(76,217,130,.08);border-color:rgba(76,217,130,.25);}
    .status-inactive{color:#8892a6;background:rgba(136,146,166,.08);border-color:rgba(136,146,166,.25);}

    .cp-list{display:flex;flex-direction:column;gap:8px;}
    .cp-list a{display:flex;align-items:center;justify-content:space-between;gap:10px;background:rgba(255,255,255,.03);border:1px solid var(--line);border-radius:9px;padding:12px 14px;text-decoration:none;color:var(--text);font-size:13px;font-weight:600;transition:all .2s;}
    .cp-list a:hover{border-color:rgba(var(--glow),.4);background:rgba(var(--glow),.07);box-shadow:inset 3px 0 0 0 rgba(var(--glow),1);transform:translateX(2px);}
    .cp-list .muted{color:var(--muted);font-size:11px;font-weight:500;}
    .cp-empty{font-size:12px;color:var(--muted);padding:8px 2px;font-weight:500;}

    .cp-add-user-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:11px;margin-bottom:16px;background:rgba(76,217,130,.08);border:1px dashed rgba(76,217,130,.4);border-radius:9px;color:#4cd982;font-size:12px;font-weight:700;text-decoration:none;transition:all .2s ease;text-transform:uppercase;letter-spacing:.5px;}
    .cp-add-user-btn:hover{background:rgba(76,217,130,.15);border-color:rgba(76,217,130,.6);transform:translateY(-1px);box-shadow:0 0 16px rgba(76,217,130,.2);}

    .cp-member{display:flex;align-items:center;gap:11px;padding:10px 0;border-bottom:1px solid var(--line);}
    .cp-member:last-child{border-bottom:0;}
    .cp-ava{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;box-shadow:0 2px 10px rgba(0,0,0,.3);}
    .cp-member .nm{font-size:13px;color:var(--text);font-weight:600;}
    .cp-member .em{font-size:11px;color:var(--muted);}
    .cp-member .badge{margin-left:auto;font-size:8px;font-weight:800;letter-spacing:.6px;color:rgba(var(--glow),1);background:rgba(var(--glow),.1);border:1px solid rgba(var(--glow),.3);padding:3px 8px;border-radius:5px;text-transform:uppercase;}

    @media(max-width:768px){.cp-page{padding:28px 18px 45px;}.page-head-custom{flex-direction:column;align-items:stretch;}.cp-btns{width:100%;}.cp-btn{flex:1;justify-content:center;}.cp-page h1{font-size:18px;}}
    @media(max-width:480px){.cp-page{padding:20px 14px 36px;}.cp-card{padding:18px;}.cp-page h1{font-size:16px;}.cp-head-icon{width:42px;height:42px;font-size:16px;}}
</style>

<div class="cp-page">
    <div class="cp-blob cp-blob-1"></div>
    <div class="cp-blob cp-blob-2"></div>
    <div class="cp-blob cp-blob-3"></div>

    <div class="cp-wrap">
        @if(session('success'))<div class="cp-flash ok">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="cp-flash err">{{ session('error') }}</div>@endif

        {{-- TOPBAR --}}
        <div class="page-head-custom">
            <div class="cp-head-left">
                <div class="cp-head-icon">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($company->name, 0, 2)) }}</div>
                <div>
                    <h1>
                        {{ $company->name }}
                        @if($company->isRoot())<span class="tag tag-root" data-i18n="cp_root">★ Корень</span>@endif
                    </h1>
                    <div class="head-sub">{{ $typeLabel }}@if($company->city) · {{ $company->city->name_ru }}@endif</div>
                </div>
            </div>
            @if($canManage)
            <div class="cp-btns">
                <a href="{{ route('companies.create', ['parent' => $company->id]) }}" class="cp-btn add"><i class="bi bi-plus-lg"></i><span data-i18n="cp_add">Подразделение</span></a>
                <a href="{{ route('companies.edit', $company) }}" class="cp-btn edit"><i class="bi bi-pencil"></i><span data-i18n="cp_edit">Редактировать</span></a>
            </div>
            @endif
        </div>

        <div class="cp-grid">
            <div>
                {{-- ДЕТАЛИ --}}
                <div class="cp-card">
                    <h3><i class="bi bi-info-circle"></i><span data-i18n="cp_details">Детали компании</span></h3>
                    <div class="cp-info">
                        <div class="cell"><div class="k" data-i18n="cp_type">Тип</div><div class="v">{{ $typeLabel }}</div></div>
                        <div class="cell">
                            <div class="k" data-i18n="cp_status">Статус</div>
                            <div class="v">
                                <span class="status-pill {{ $statusActive ? 'status-active' : 'status-inactive' }}" data-i18n="{{ $statusActive ? 'cp_status_active' : 'cp_status_inactive' }}">{{ $statusActive ? 'Активна' : 'Неактивна' }}</span>
                            </div>
                        </div>
                        <div class="cell"><div class="k" data-i18n="cp_region">Область</div><div class="v">{{ $company->region->name_ru ?? '—' }}</div></div>
                        <div class="cell"><div class="k" data-i18n="cp_city">Город</div><div class="v">{{ $company->city->name_ru ?? '—' }}</div></div>
                        <div class="cell"><div class="k" data-i18n="cp_email">Email</div><div class="v">{{ $company->email ?? '—' }}</div></div>
                        <div class="cell">
                            <div class="k" data-i18n="cp_parent">Родитель</div>
                            <div class="v">
                                @if($company->parent)
                                    {{ $company->parent->name }}
                                @else
                                    <span data-i18n="cp_no_parent">Корневая (без родителя)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ПОДРАЗДЕЛЕНИЯ --}}
                <div class="cp-card">
                    <h3><i class="bi bi-diagram-3"></i><span data-i18n="cp_children">Подразделения</span><span class="count">{{ $company->children->count() }}</span></h3>
                    @if($company->children->count())
                        <div class="cp-list">
                            @foreach($company->children as $ch)
                            <a href="{{ route('companies.show', $ch) }}">
                                <span>{{ $ch->name }}</span>
                                <span class="muted">{{ $typeLabels[$ch->type] ?? ($ch->type ?? '') }}</span>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="cp-empty" data-i18n="cp_no_children">Нет подразделений.</div>
                    @endif
                </div>
            </div>

            <div>
                {{-- КОМАНДА --}}
                <div class="cp-card">
                    <h3><i class="bi bi-people"></i><span data-i18n="cp_team">Команда</span><span class="count">{{ $team->count() }}</span></h3>

                    @if($canManage)
                        <a href="{{ route('users.create', ['company_id' => $company->id]) }}" class="cp-add-user-btn">
                            <i class="bi bi-person-plus-fill"></i>
                            <span data-i18n="cp_add_user">Добавить пользователя</span>
                        </a>
                    @endif

                    @if($team->count())
                        @foreach($team as $u)
                        <div class="cp-member">
                            <div class="cp-ava" style="background:{{ $u->avatar_color ?? '#4f46e5' }}">{{ $u->initials ?? mb_substr($u->name, 0, 1) }}</div>
                            <div style="min-width:0;">
                                <div class="nm">{{ $u->name }}</div>
                                <div class="em">{{ $u->email }}</div>
                            </div>
                            @if($u->is_admin)<span class="badge" data-i18n="cp_admin">Админ</span>@endif
                        </div>
                        @endforeach
                    @else
                        <div class="cp-empty" data-i18n="cp_no_team">Нет участников.</div>
                    @endif

                    <a href="{{ route('companies.index') }}" class="cp-btn" style="width:100%;justify-content:center;margin-top:14px;"><i class="bi bi-arrow-left"></i><span data-i18n="cp_back">К дереву</span></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var translations = {
        ru: {
            cp_root:'★ Корень', cp_add:'Подразделение', cp_edit:'Редактировать',
            cp_details:'Детали компании', cp_type:'Тип', cp_status:'Статус',
            cp_status_active:'Активна', cp_status_inactive:'Неактивна',
            cp_region:'Область', cp_city:'Город', cp_email:'Email',
            cp_parent:'Родитель', cp_no_parent:'Корневая (без родителя)',
            cp_children:'Подразделения', cp_no_children:'Нет подразделений.',
            cp_team:'Команда', cp_no_team:'Нет участников.', cp_admin:'Админ',
            cp_add_user:'Добавить пользователя', cp_back:'К дереву'
        },
        tj: {
            cp_root:'★ Асосӣ', cp_add:'Зерсохтор', cp_edit:'Таҳрир',
            cp_details:'Тафсилоти ширкат', cp_type:'Намуд', cp_status:'Ҳолат',
            cp_status_active:'Фаъол', cp_status_inactive:'Ғайрифаъол',
            cp_region:'Вилоят', cp_city:'Шаҳр', cp_email:'Email',
            cp_parent:'Волид', cp_no_parent:'Асосӣ (бе волид)',
            cp_children:'Зерсохторҳо', cp_no_children:'Зерсохтор нест.',
            cp_team:'Даста', cp_no_team:'Иштирокчӣ нест.', cp_admin:'Админ',
            cp_add_user:'Илова кардани корбар', cp_back:'Ба дарахт'
        },
        en: {
            cp_root:'★ Root', cp_add:'Subdivision', cp_edit:'Edit',
            cp_details:'Company Details', cp_type:'Type', cp_status:'Status',
            cp_status_active:'Active', cp_status_inactive:'Inactive',
            cp_region:'Region', cp_city:'City', cp_email:'Email',
            cp_parent:'Parent', cp_no_parent:'Root (no parent)',
            cp_children:'Subdivisions', cp_no_children:'No subdivisions.',
            cp_team:'Team', cp_no_team:'No members.', cp_admin:'Admin',
            cp_add_user:'Add User', cp_back:'To tree'
        }
    };

    function getCurrentLang(){
        return localStorage.getItem('docsign_lang') || localStorage.getItem('app-lang') || 'ru';
    }

    function applyTranslations(){
        var lang = getCurrentLang();
        var t = translations[lang] || translations.ru;
        document.querySelectorAll('[data-i18n]').forEach(function(el){
            var k = el.getAttribute('data-i18n');
            if(t[k] !== undefined) el.textContent = t[k];
        });
    }

    applyTranslations();
    window.addEventListener('docsign:lang-changed', function(e){ applyTranslations(); });
    window.addEventListener('storage', function(e){ if(e.key === 'docsign_lang' && e.newValue) applyTranslations(); });

    // Blob animation
    var blobs = document.querySelectorAll('.cp-blob');
    document.addEventListener('mousemove', function(e){
        var x = (e.clientX / window.innerWidth - 0.5) * 30;
        var y = (e.clientY / window.innerHeight - 0.5) * 30;
        blobs.forEach(function(b, i){
            var f = (i + 1) * 0.4;
            b.style.transform = 'translate(' + (x*f) + 'px,' + (y*f) + 'px)';
        });
    });
});
</script>
@endsection