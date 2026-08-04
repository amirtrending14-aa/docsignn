@extends('layouts.admin')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    .cc-page{min-height:100vh;padding:40px 24px 60px;color:var(--text);font-family:'Inter',sans-serif;position:relative;}
    .cc-blob{position:absolute;border-radius:50%;pointer-events:none;z-index:0;filter:blur(100px);opacity:.35;}
    .cc-blob-1{top:-120px;left:-120px;width:500px;height:500px;background:radial-gradient(circle,rgba(var(--glow),.35) 0%,transparent 70%);animation:ccBlob 20s ease-in-out infinite;}
    .cc-blob-2{bottom:-120px;right:-120px;width:600px;height:600px;background:radial-gradient(circle,rgba(168,85,247,.28) 0%,transparent 70%);animation:ccBlob 25s ease-in-out infinite reverse;}
    .cc-blob-3{top:40%;left:60%;width:400px;height:400px;background:radial-gradient(circle,rgba(236,72,153,.22) 0%,transparent 70%);animation:ccBlob3 30s ease-in-out infinite;}
    @keyframes ccBlob{0%,100%{transform:translate(0,0);}50%{transform:translate(30px,-30px);}}
    @keyframes ccBlob3{0%,100%{transform:translate(0,0);}50%{transform:translate(-30px,30px);}}

    .cc-wrap{max-width:760px;margin:0 auto;position:relative;z-index:1;}
    .cc-topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:24px;background:linear-gradient(180deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid var(--line);border-radius:var(--radius);padding:18px 22px;position:relative;}
    .cc-topbar::before{content:"";position:absolute;inset:-1px;border-radius:var(--radius);padding:1px;background:linear-gradient(135deg,rgba(var(--glow),.4),transparent 40%,transparent 60%,rgba(var(--glow),.2));-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;opacity:.6;pointer-events:none;}
    .cc-topbar-left{display:flex;align-items:center;gap:14px;min-width:0;flex:1;}
    .cc-topbar-icon{width:48px;height:48px;border-radius:13px;background:linear-gradient(135deg,rgba(var(--glow),.95),rgba(var(--glow),.4));display:grid;place-items:center;flex-shrink:0;box-shadow:0 0 24px rgba(var(--glow),.5),inset 0 0 12px rgba(255,255,255,.2);}
    .cc-topbar-icon svg{width:24px;height:24px;color:#0a0d14;}
    .cc-topbar-title{font-size:20px;font-weight:800;color:var(--text);letter-spacing:-.3px;margin:0;}
    .cc-topbar-subtitle{font-size:12px;color:var(--muted);font-weight:600;margin-top:3px;}
    .cc-topbar-subtitle strong{color:rgba(var(--glow),1);font-weight:700;}
    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:rgba(255,255,255,.04);color:var(--muted);border:1px solid var(--line);border-radius:10px;font-size:12px;font-weight:700;text-decoration:none;text-transform:uppercase;letter-spacing:.8px;transition:all .25s ease;white-space:nowrap;flex-shrink:0;}
    .btn-back:hover{color:rgba(var(--glow),1);border-color:rgba(var(--glow),.5);background:rgba(var(--glow),.08);box-shadow:0 0 18px rgba(var(--glow),.25);transform:translateX(-2px);}
    .btn-back svg{width:14px;height:14px;}

    .form-card{background:linear-gradient(180deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid var(--line);border-radius:var(--radius);padding:36px 32px;position:relative;}
    .form-card::before{content:"";position:absolute;inset:-1px;border-radius:var(--radius);padding:1px;background:linear-gradient(135deg,rgba(var(--glow),.5),transparent 40%,transparent 60%,rgba(var(--glow),.25));-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;opacity:.7;pointer-events:none;}
    .form-section{margin-bottom:28px;}
    .form-section:last-child{margin-bottom:0;}
    .section-title{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:16px;display:flex;align-items:center;gap:10px;}
    .section-title::before{content:"";width:4px;height:14px;border-radius:2px;background:linear-gradient(180deg,rgba(var(--glow),1),rgba(var(--glow),.4));box-shadow:0 0 10px rgba(var(--glow),.6);flex-shrink:0;}

    .field-group{margin-bottom:18px;}
    .field-group:last-child{margin-bottom:0;}
    .field-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .field-label{display:block;text-transform:uppercase;letter-spacing:1px;font-size:11px;font-weight:800;color:var(--muted);margin-bottom:8px;}
    .field-label .required{color:rgba(var(--glow),1);margin-left:2px;}
    .input-custom{width:100%;background:rgba(255,255,255,.04) !important;border:1px solid var(--line) !important;color:var(--text) !important;font-size:14px !important;font-weight:500;padding:13px 16px !important;border-radius:10px !important;transition:all .25s ease;font-family:'Inter',sans-serif;box-sizing:border-box;}
    .input-custom:focus{border-color:rgba(var(--glow),.6) !important;background:rgba(var(--glow),.06) !important;box-shadow:0 0 0 3px rgba(var(--glow),.15),0 0 20px rgba(var(--glow),.2) !important;outline:none !important;}
    .input-custom::placeholder{color:var(--muted) !important;opacity:.6;}
    .input-custom option,.input-custom optgroup{background:#0d1018;color:var(--text);}
    select.input-custom{appearance:none;cursor:pointer;color-scheme:dark;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%238892a6' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") !important;background-repeat:no-repeat !important;background-position:right 16px center !important;padding-right:40px !important;}
    .note-text{font-size:11px;color:var(--muted);margin-top:8px;font-weight:500;display:flex;align-items:center;gap:6px;}
    .note-text::before{content:"ⓘ";color:rgba(var(--glow),.8);font-size:12px;}
    .err-msg{font-size:11px;color:#ff7a7a;margin-top:6px;font-weight:600;}

    .password-wrap{position:relative;}
    .password-toggle{position:absolute;top:50%;right:12px;transform:translateY(-50%);background:transparent;border:none;color:var(--muted);cursor:pointer;padding:6px;border-radius:6px;display:flex;align-items:center;justify-content:center;transition:all .2s ease;}
    .password-toggle:hover{color:rgba(var(--glow),1);background:rgba(var(--glow),.1);}
    .password-toggle svg{width:18px;height:18px;}

    .info-banner{background:rgba(var(--glow),.08);border:1px solid rgba(var(--glow),.25);border-radius:12px;padding:16px 18px;display:flex;align-items:flex-start;gap:14px;position:relative;overflow:hidden;}
    .info-banner::before{content:"";position:absolute;left:0;top:0;bottom:0;width:3px;background:linear-gradient(180deg,rgba(var(--glow),1),rgba(var(--glow),.4));box-shadow:0 0 12px rgba(var(--glow),.6);}
    .info-banner-icon{width:36px;height:36px;border-radius:10px;background:rgba(var(--glow),.18);border:1px solid rgba(var(--glow),.35);display:grid;place-items:center;flex-shrink:0;}
    .info-banner-icon svg{width:18px;height:18px;color:rgba(var(--glow),1);}
    .info-banner-text{font-size:13px;color:var(--text);font-weight:500;line-height:1.5;}
    .info-banner-text strong{color:rgba(var(--glow),1);font-weight:700;}

    .submit-wrap{padding-top:24px;margin-top:28px;border-top:1px solid var(--line);display:flex;justify-content:center;}
    .btn-submit{display:inline-flex;align-items:center;gap:10px;padding:14px 36px;background:linear-gradient(180deg,rgba(var(--glow),.95),rgba(var(--glow),.65));color:#0a0d14;border-radius:11px;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;transition:all .3s ease;box-shadow:0 8px 28px rgba(var(--glow),.4),inset 0 1px 0 rgba(255,255,255,.3);border:1px solid transparent;cursor:pointer;white-space:nowrap;}
    .btn-submit:hover{transform:translateY(-2px);box-shadow:0 14px 40px rgba(var(--glow),.6);filter:brightness(1.08);}
    .btn-submit svg{width:18px;height:18px;}

    @media(max-width:992px){.cc-page{padding:32px 20px 50px;}.form-card{padding:28px 24px;}.cc-topbar-title{font-size:18px;}.field-row{gap:14px;}}
    @media(max-width:768px){.cc-page{padding:28px 18px 45px;}.form-card{padding:24px 20px;}.cc-topbar-title{font-size:17px;}}
    @media(max-width:640px){.cc-page{padding:24px 16px 40px;}.cc-topbar{flex-direction:column;align-items:stretch;}.btn-back{width:100%;justify-content:center;}.field-row{grid-template-columns:1fr;}.form-card{padding:22px 18px;}.btn-submit{width:100%;max-width:320px;justify-content:center;}}
    @media(max-width:480px){.cc-page{padding:20px 14px 36px;}.form-card{padding:20px 16px;}.cc-topbar-title{font-size:15px;}.btn-submit{padding:12px 24px;font-size:11px;}}
    @media(max-width:380px){.cc-page{padding:18px 12px 32px;}.form-card{padding:18px 14px;}.cc-topbar-title{font-size:14px;}}
</style>

<div class="cc-page">
    <div class="cc-blob cc-blob-1"></div>
    <div class="cc-blob cc-blob-2"></div>
    <div class="cc-blob cc-blob-3"></div>

    <div class="cc-wrap">
        <div class="cc-topbar">
            <div class="cc-topbar-left">
                <div class="cc-topbar-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                </div>
                <div>
                    <div class="cc-topbar-title" data-i18n="cc_title">Новая компания</div>
                    <div class="cc-topbar-subtitle"><span data-i18n="cc_tree">Дерево</span>: <strong>{{ $parent->name ?? '—' }}</strong></div>
                </div>
            </div>
            <a href="{{ route('companies.index') }}" class="btn-back">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span data-i18n="cc_back">Назад к дереву</span>
            </a>
        </div>

        <div class="form-card">
            <form method="POST" action="{{ route('companies.store') }}">
                @csrf

                <div class="form-section">
                    <div class="section-title" data-i18n="cc_company">Данные компании</div>

                    <div class="field-group">
                        <label class="field-label"><span data-i18n="cc_name">Название компании</span><span class="required">*</span></label>
                        <input name="company_name" type="text" required class="input-custom" value="{{ old('company_name') }}" data-i18n-placeholder="cc_name_ph" placeholder="Например: Филиал №2">
                        @error('company_name')<div class="err-msg">{{ $message }}</div>@enderror
                    </div>

                    {{-- ✅ Родительская компания (на всю ширину). Уровень вычисляется автоматически --}}
                    {{-- ✅ Родительская компания --}}
<div class="field-group">
    <label class="field-label"><span data-i18n="cc_parent">Родительская компания</span><span class="required">*</span></label>
    <select name="parent_id" required class="input-custom">
        <option value="" data-i18n="cc_parent_select">-- Выберите родителя --</option>
        @foreach($companies as $co)
            {{-- Используем old(), чтобы сохранить выбор при ошибке валидации --}}
            <option value="{{ $co->id }}" {{ old('parent_id', $parent?->id) == $co->id ? 'selected' : '' }}>
                {{ str_repeat('— ', $co->depth ?? 0) }}{{ $co->name }}@if($co->isRoot()) ★@endif
            </option>
        @endforeach
    </select>
    <div class="note-text" data-i18n="cc_parent_hint">Уровень вложенности определится автоматически</div>
    @error('parent_id')<div class="err-msg">{{ $message }}</div>@enderror
</div>

                    <div class="field-group">
                        <label class="field-label"><span data-i18n="cc_type">Тип организации</span><span class="required">*</span></label>
                        <select name="company_type" required class="input-custom">
                            <option value="" data-i18n="cc_type_select">-- Выберите тип --</option>
                            <optgroup label="1. Органы государственной власти" data-i18n-label="og_1">
                                <option value="ministry" {{ old('company_type')=='ministry'?'selected':'' }} data-i18n="t_ministry">Правительство и министерства</option>
                                <option value="local_government" {{ old('company_type')=='local_government'?'selected':'' }} data-i18n="t_local_government">Местное самоуправление</option>
                                <option value="law_enforcement" {{ old('company_type')=='law_enforcement'?'selected':'' }} data-i18n="t_law_enforcement">Силовые структуры</option>
                                <option value="special_agency" {{ old('company_type')=='special_agency'?'selected':'' }} data-i18n="t_special_agency">Специализированные агентства</option>
                            </optgroup>
                            <optgroup label="2. Социальная сфера" data-i18n-label="og_2">
                                <option value="education" {{ old('company_type')=='education'?'selected':'' }} data-i18n="t_education">Образование</option>
                                <option value="healthcare" {{ old('company_type')=='healthcare'?'selected':'' }} data-i18n="t_healthcare">Здравоохранение</option>
                                <option value="social_protection" {{ old('company_type')=='social_protection'?'selected':'' }} data-i18n="t_social_protection">Социальная защита</option>
                            </optgroup>
                            <optgroup label="3. Финансы и бизнес" data-i18n-label="og_3">
                                <option value="bank" {{ old('company_type')=='bank'?'selected':'' }} data-i18n="t_bank">Финансы</option>
                                <option value="business_services" {{ old('company_type')=='business_services'?'selected':'' }} data-i18n="t_business_services">Деловые услуги</option>
                                <option value="it_development" {{ old('company_type')=='it_development'?'selected':'' }} data-i18n="t_it_development">IT и разработка</option>
                            </optgroup>
                            <optgroup label="4. Торговля и общепит" data-i18n-label="og_4">
                                <option value="retail" {{ old('company_type')=='retail'?'selected':'' }} data-i18n="t_retail">Торговля</option>
                                <option value="catering" {{ old('company_type')=='catering'?'selected':'' }} data-i18n="t_catering">Общепит</option>
                            </optgroup>
                            <optgroup label="5. Производство и строительство" data-i18n-label="og_5">
                                <option value="manufacturing" {{ old('company_type')=='manufacturing'?'selected':'' }} data-i18n="t_manufacturing">Промышленность</option>
                                <option value="construction" {{ old('company_type')=='construction'?'selected':'' }} data-i18n="t_construction">Строительство</option>
                            </optgroup>
                            <optgroup label="6. Сфера услуг" data-i18n-label="og_6">
                                <option value="household_services" {{ old('company_type')=='household_services'?'selected':'' }} data-i18n="t_household_services">Бытовые услуги</option>
                                <option value="hospitality" {{ old('company_type')=='hospitality'?'selected':'' }} data-i18n="t_hospitality">Гостиничный бизнес</option>
                                <option value="sport_leisure" {{ old('company_type')=='sport_leisure'?'selected':'' }} data-i18n="t_sport_leisure">Спорт и досуг</option>
                            </optgroup>
                            <optgroup label="7. Инфраструктура" data-i18n-label="og_7">
                                <option value="utilities" {{ old('company_type')=='utilities'?'selected':'' }} data-i18n="t_utilities">ЖКХ</option>
                                <option value="transport" {{ old('company_type')=='transport'?'selected':'' }} data-i18n="t_transport">Транспорт</option>
                                <option value="communication" {{ old('company_type')=='communication'?'selected':'' }} data-i18n="t_communication">Связь</option>
                            </optgroup>
                        </select>
                        @error('company_type')<div class="err-msg">{{ $message }}</div>@enderror
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label"><span data-i18n="cc_region">Область</span><span class="required">*</span></label>
                            <select name="region_id" id="ccRegion" required class="input-custom">
                                <option value="" data-i18n="cc_region_select">-- Выберите область --</option>
                                <option value="1" {{ old('region_id')=='1'?'selected':'' }}>Согдийская область (Вилояти Суғд)</option>
                                <option value="2" {{ old('region_id')=='2'?'selected':'' }}>Хатлонская область (Вилояти Хатлон)</option>
                                <option value="3" {{ old('region_id')=='3'?'selected':'' }}>Районы республиканского подчинения (РРП)</option>
                                <option value="4" {{ old('region_id')=='4'?'selected':'' }}>Горно-Бадахшанская АО (ГБАО/ВМКБ)</option>
                                <option value="5" {{ old('region_id')=='5'?'selected':'' }}>Город Душанбе (Шаҳри Душанбе)</option>
                            </select>
                            @error('region_id')<div class="err-msg">{{ $message }}</div>@enderror
                        </div>
                        <div class="field-group">
                            <label class="field-label"><span data-i18n="cc_city">Город / Район</span><span class="required">*</span></label>
                            <select name="city_id" id="ccCity" required class="input-custom">
                                <option value="" data-i18n="cc_city_select">-- Сначала выберите область --</option>
                            </select>
                            @error('city_id')<div class="err-msg">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-title" data-i18n="cc_admin">Администратор компании</div>

                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label"><span data-i18n="cc_aname">Имя</span><span class="required">*</span></label>
                            <input name="name" type="text" required class="input-custom" value="{{ old('name') }}" data-i18n-placeholder="cc_aname_ph" placeholder="Иван Иванов">
                            @error('name')<div class="err-msg">{{ $message }}</div>@enderror
                        </div>
                        <div class="field-group">
                            <label class="field-label"><span data-i18n="cc_aemail">Email</span><span class="required">*</span></label>
                            <input name="email" type="email" required class="input-custom" value="{{ old('email') }}" data-i18n-placeholder="cc_aemail_ph" placeholder="admin@company.tj">
                            @error('email')<div class="err-msg">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label"><span data-i18n="cc_arole">Роль</span><span class="required">*</span></label>
                        <select name="admin_role" required class="input-custom">
                            <option value="admin" {{ old('admin_role','admin')=='admin'?'selected':'' }} data-i18n="cc_role_admin">Администратор компании</option>
                            <option value="employee" {{ old('admin_role')=='employee'?'selected':'' }} data-i18n="cc_role_emp">Сотрудник</option>
                        </select>
                        @error('admin_role')<div class="err-msg">{{ $message }}</div>@enderror
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label"><span data-i18n="cc_apass">Пароль</span><span class="required">*</span></label>
                            <div class="password-wrap">
                                <input name="password" type="password" id="ccPass" required class="input-custom" style="padding-right:48px !important;" data-i18n-placeholder="cc_apass_ph" placeholder="Минимум 8 символов">
                                <button type="button" onclick="ccTogglePass()" class="password-toggle" aria-label="Toggle">
                                    <svg id="ccEye" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                            @error('password')<div class="err-msg">{{ $message }}</div>@enderror
                        </div>
                        <div class="field-group">
                            <label class="field-label"><span data-i18n="cc_apass2">Подтверждение пароля</span><span class="required">*</span></label>
                            <input name="password_confirmation" type="password" required class="input-custom" data-i18n-placeholder="cc_apass2_ph" placeholder="Повторите пароль">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="info-banner">
                        <div class="info-banner-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="info-banner-text" data-i18n="cc_note">Компания войдёт в ваше дерево как <strong>подразделение</strong>. Администратор получит отдельный вход.</div>
                    </div>
                </div>

                <div class="submit-wrap">
                    <button type="submit" class="btn-submit">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span data-i18n="cc_create">Создать компанию</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ✅ Глобальная функция переключения видимости пароля (для onclick)
function ccTogglePass(){
    var input = document.getElementById('ccPass');
    var icon  = document.getElementById('ccEye');
    if(input.type === 'password'){
        input.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
    }
}

document.addEventListener('DOMContentLoaded', function(){
    'use strict';

    // =====================================================================
    // 1. ПЕРЕВОДЫ (RU / TJ / EN)
    // =====================================================================
    var translations = {
        ru: {
            cc_title:'Новая компания', cc_tree:'Дерево', cc_back:'Назад к дереву',
            cc_company:'Данные компании', cc_name:'Название компании', cc_name_ph:'Например: Филиал №2',
            cc_parent:'Родительская компания', cc_parent_select:'-- Выберите родителя --',
            cc_parent_hint:'Уровень вложенности определится автоматически',
            cc_type:'Тип организации', cc_type_select:'-- Выберите тип --',
            og_1:'1. Органы государственной власти', og_2:'2. Социальная сфера', og_3:'3. Финансы и бизнес',
            og_4:'4. Торговля и общепит', og_5:'5. Производство и строительство', og_6:'6. Сфера услуг', og_7:'7. Инфраструктура',
            t_ministry:'Правительство и министерства', t_local_government:'Местное самоуправление', t_law_enforcement:'Силовые структуры', t_special_agency:'Специализированные агентства',
            t_education:'Образование', t_healthcare:'Здравоохранение', t_social_protection:'Социальная защита',
            t_bank:'Финансы', t_business_services:'Деловые услуги', t_it_development:'IT и разработка',
            t_retail:'Торговля', t_catering:'Общепит',
            t_manufacturing:'Промышленность', t_construction:'Строительство',
            t_household_services:'Бытовые услуги', t_hospitality:'Гостиничный бизнес', t_sport_leisure:'Спорт и досуг',
            t_utilities:'ЖКХ', t_transport:'Транспорт', t_communication:'Связь',
            cc_region:'Область', cc_region_select:'-- Выберите область --',
            cc_city:'Город / Район', cc_city_select:'-- Сначала выберите область --', cc_city_select2:'-- Выберите город / район --',
            cc_admin:'Администратор компании', cc_aname:'Имя', cc_aname_ph:'Иван Иванов',
            cc_aemail:'Email', cc_aemail_ph:'admin@company.tj',
            cc_arole:'Роль', cc_role_admin:'Администратор компании', cc_role_emp:'Сотрудник',
            cc_apass:'Пароль', cc_apass_ph:'Минимум 8 символов', cc_apass2:'Подтверждение пароля', cc_apass2_ph:'Повторите пароль',
            cc_note:'Компания войдёт в ваше дерево как <strong>подразделение</strong>. Администратор получит отдельный вход.',
            cc_create:'Создать компанию'
        },
        tj: {
            cc_title:'Ширкати нав', cc_tree:'Дарахт', cc_back:'Бозгашт ба дарахт',
            cc_company:'Маълумоти ширкат', cc_name:'Номи ширкат', cc_name_ph:'Масалан: Филиал №2',
            cc_parent:'Ширкати волид', cc_parent_select:'-- Волидро интихоб кунед --',
            cc_parent_hint:'Сатҳи зертобеият ба таври худкор муайян мешавад',
            cc_type:'Намуди ташкилот', cc_type_select:'-- Намудро интихоб кунед --',
            og_1:'1. Мақомоти ҳокимияти давлатӣ', og_2:'2. Соҳаи иҷтимоӣ', og_3:'3. Молия ва бизнес',
            og_4:'4. Савдо ва ғизои умумӣ', og_5:'5. Истеҳсол ва сохтмон', og_6:'6. Соҳаи хидматрасонӣ', og_7:'7. Инфрасохтор',
            t_ministry:'Ҳукумат ва вазоратҳо', t_local_government:'Ҳудудияти маҳаллӣ', t_law_enforcement:'Сохторҳои қудратӣ', t_special_agency:'Агентии махсус',
            t_education:'Маориф', t_healthcare:'Тандурустӣ', t_social_protection:'Ҳифзи иҷтимоӣ',
            t_bank:'Молия', t_business_services:'Хидматҳои тиҷоратӣ', t_it_development:'IT ва таҳия',
            t_retail:'Савдо', t_catering:'Ғизои умумӣ',
            t_manufacturing:'Саноат', t_construction:'Сохтмон',
            t_household_services:'Хидматҳои маишӣ', t_hospitality:'Меҳмонхонадорӣ', t_sport_leisure:'Варзиш ва фароғат',
            t_utilities:'ЖКХ', t_transport:'Нақлиёт', t_communication:'Алоқа',
            cc_region:'Вилоят', cc_region_select:'-- Вилоятро интихоб кунед --',
            cc_city:'Шаҳр / Ноҳия', cc_city_select:'-- Аввал вилоятро интихоб кунед --', cc_city_select2:'-- Шаҳр / ноҳияро интихоб кунед --',
            cc_admin:'Админи ширкат', cc_aname:'Ном', cc_aname_ph:'Иван Иванов',
            cc_aemail:'Email', cc_aemail_ph:'admin@company.tj',
            cc_arole:'Нақш', cc_role_admin:'Администратори ширкат', cc_role_emp:'Корманд',
            cc_apass:'Рамз', cc_apass_ph:'Ҳадди ақал 8 аломат', cc_apass2:'Тасдиқи рамз', cc_apass2_ph:'Рамзро такрор кунед',
            cc_note:'Ширкат ба дарахти шумо ҳамчун <strong>зерсохтор</strong> медарояд. Админ вуруди алоҳида мегирад.',
            cc_create:'Ширкат сохтан'
        },
        en: {
            cc_title:'New Company', cc_tree:'Tree', cc_back:'Back to tree',
            cc_company:'Company Data', cc_name:'Company Name', cc_name_ph:'E.g.: Branch №2',
            cc_parent:'Parent Company', cc_parent_select:'-- Select parent --',
            cc_parent_hint:'Nesting level is determined automatically',
            cc_type:'Organization Type', cc_type_select:'-- Select type --',
            og_1:'1. Government Authorities', og_2:'2. Social Sphere', og_3:'3. Finance & Business',
            og_4:'4. Trade & Catering', og_5:'5. Manufacturing & Construction', og_6:'6. Services', og_7:'7. Infrastructure',
            t_ministry:'Government & Ministries', t_local_government:'Local Government', t_law_enforcement:'Law Enforcement', t_special_agency:'Special Agencies',
            t_education:'Education', t_healthcare:'Healthcare', t_social_protection:'Social Protection',
            t_bank:'Finance', t_business_services:'Business Services', t_it_development:'IT & Development',
            t_retail:'Retail', t_catering:'Catering',
            t_manufacturing:'Manufacturing', t_construction:'Construction',
            t_household_services:'Household Services', t_hospitality:'Hospitality', t_sport_leisure:'Sport & Leisure',
            t_utilities:'Utilities', t_transport:'Transport', t_communication:'Communication',
            cc_region:'Region', cc_region_select:'-- Select region --',
            cc_city:'City / District', cc_city_select:'-- Select region first --', cc_city_select2:'-- Select city / district --',
            cc_admin:'Company Admin', cc_aname:'Name', cc_aname_ph:'Ivan Ivanov',
            cc_aemail:'Email', cc_aemail_ph:'admin@company.tj',
            cc_arole:'Role', cc_role_admin:'Company Administrator', cc_role_emp:'Employee',
            cc_apass:'Password', cc_apass_ph:'Minimum 8 characters', cc_apass2:'Password Confirmation', cc_apass2_ph:'Repeat password',
            cc_note:'The company joins your tree as a <strong>subdivision</strong>. The admin gets a separate login.',
            cc_create:'Create Company'
        }
    };

    function getCurrentLang(){
        return localStorage.getItem('docsign_lang') || localStorage.getItem('app-lang') || 'ru';
    }

    var currentT = translations[getCurrentLang()] || translations.ru;

    function applyTranslations(){
        var lang = getCurrentLang();
        currentT = translations[lang] || translations.ru;

        // Текст элементов
        document.querySelectorAll('[data-i18n]').forEach(function(el){
            var k = el.getAttribute('data-i18n');
            if(currentT[k] !== undefined) el.innerHTML = currentT[k];
        });
        // Placeholder'ы
        document.querySelectorAll('[data-i18n-placeholder]').forEach(function(el){
            var k = el.getAttribute('data-i18n-placeholder');
            if(currentT[k] !== undefined) el.setAttribute('placeholder', currentT[k]);
        });
        // Label'ы optgroup
        document.querySelectorAll('[data-i18n-label]').forEach(function(el){
            var k = el.getAttribute('data-i18n-label');
            if(currentT[k] !== undefined) el.setAttribute('label', currentT[k]);
        });
    }

    applyTranslations();

    window.addEventListener('docsign:lang-changed', function(e){
        if(e.detail && e.detail.lang){
            localStorage.setItem('docsign_lang', e.detail.lang);
            localStorage.setItem('app-lang', e.detail.lang);
        }
        applyTranslations();
        // Перезаполняем список городов с новым языком
        if(region.value) loadCities(region.value, null);
    });
    window.addEventListener('storage', function(e){
        if(e.key === 'docsign_lang' && e.newValue){
            applyTranslations();
            if(region.value) loadCities(region.value, null);
        }
    });

    // =====================================================================
    // 2. ГОРОДА (данные из БД, переданные контроллером)
    // =====================================================================
    var UC_CITIES = @json($citiesByRegion);
    var region = document.getElementById('ccRegion');
    var city   = document.getElementById('ccCity');

    var currentRegion = '{{ old("region_id", "") }}';
    var currentCity   = '{{ old("city_id", "") }}';

    function loadCities(rid, selected){
        city.innerHTML = '';
        if(!rid || !UC_CITIES[rid]){
            city.innerHTML = '<option value="">' + currentT.cc_city_select + '</option>';
            return;
        }
        city.innerHTML = '<option value="">' + currentT.cc_city_select2 + '</option>';
        UC_CITIES[rid].forEach(function(c){
            var o = document.createElement('option');
            o.value = c.id;
            o.textContent = c.name;
            if(selected && String(selected) === String(c.id)) o.selected = true;
            city.appendChild(o);
        });
    }

    region.addEventListener('change', function(){
        loadCities(this.value, null);
    });

    if(currentRegion){
        loadCities(currentRegion, currentCity);
    }

    // =====================================================================
    // 3. BLOB ANIMATION
    // =====================================================================
    var blobs = document.querySelectorAll('.cc-blob');
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