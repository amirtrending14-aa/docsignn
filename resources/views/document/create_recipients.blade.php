@extends('layouts.admin')

@section('content')
<style>
    .recipients-page { color: #e7ecf3; padding: 24px 16px; min-height: calc(100vh - 64px); }
    .glass-card {
        background: linear-gradient(180deg, rgba(22, 26, 38, 0.95), rgba(16, 19, 28, 0.95));
        border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 20px;
        position: relative; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.4); transition: all 0.3s ease;
    }
    .glass-card::before {
        content: ""; position: absolute; inset: -1px; border-radius: 16px; padding: 1px;
        background: linear-gradient(135deg, rgba(79,140,255,0.4), transparent 40%, transparent 60%, rgba(79,140,255,0.2));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none; opacity: 0.7;
    }
    .glass-card:hover { border-color: rgba(79,140,255, 0.3); box-shadow: 0 20px 40px rgba(0,0,0,0.5), 0 0 20px rgba(79,140,255,0.1); }
    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.06); }
    .page-header-left { display: flex; align-items: center; gap: 12px; }
    .back-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #8892a6; text-decoration: none; font-size: 13px; transition: all 0.25s ease; }
    .back-btn:hover { color: #fff; border-color: rgba(79,140,255, 0.5); background: rgba(79,140,255, 0.08); }
    .back-label { font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #8892a6; }
    .content-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
    @media (max-width: 1024px) { .content-grid { grid-template-columns: 1fr; } }
    .step-label { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
    .step-label.blue { color: #4f8cff; } .step-label.purple { color: #a855f7; } .step-label.cyan { color: #06b6d4; } .step-label.amber { color: #f59e0b; } .step-label.green { color: #22c55e; }
    .custom-select, .custom-input { width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px 14px; color: #fff; font: 500 12px 'Inter', sans-serif; outline: none; transition: all 0.2s ease; appearance: none; cursor: pointer; }
    .custom-input { cursor: text; }
    .custom-select:focus, .custom-input:focus { border-color: rgba(79,140,255, 0.6); box-shadow: 0 0 0 2px rgba(79,140,255, 0.15); background: rgba(255,255,255,0.05); }
    .custom-select option { background: #161a26; color: #fff; }
    .custom-select optgroup { background: #0f111a; color: #f59e0b; font-weight: 700; }

    .org-list { max-height: 500px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; margin-top: 12px; padding-right: 4px; }
    .org-list::-webkit-scrollbar { width: 4px; }
    .org-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

    .org-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px; cursor: pointer; transition: all 0.2s ease; }
    .org-item:hover { background: rgba(245,158,11,0.06); border-color: rgba(245,158,11,0.3); }
    .org-item.active { background: rgba(245,158,11,0.12); border-color: rgba(245,158,11,0.6); border-bottom-left-radius: 0; border-bottom-right-radius: 0; }
    .org-item-name { font-size: 12px; font-weight: 600; color: #fff; flex: 1; }
    .org-item-icon { font-size: 14px; color: #f59e0b; transition: transform 0.2s; }
    .org-item.active .org-item-icon { transform: rotate(180deg); }

    .org-users-container {
        background: rgba(0,0,0,0.2);
        border: 1px solid rgba(245,158,11,0.3);
        border-top: none;
        border-bottom-left-radius: 10px;
        border-bottom-right-radius: 10px;
        padding: 12px;
        display: none;
        animation: slideDown 0.2s ease-out;
    }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

    .user-item { display: flex; align-items: center; gap: 10px; padding: 10px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px; cursor: pointer; transition: all 0.2s ease; margin-bottom: 6px; }
    .user-item:last-child { margin-bottom: 0; }
    .user-item:hover { background: rgba(79,140,255,0.05); border-color: rgba(79,140,255,0.3); }
    .user-checkbox { width: 16px; height: 16px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.3); accent-color: #4f8cff; cursor: pointer; flex-shrink: 0; }
    .user-info { flex: 1; min-width: 0; }
    .user-name { font-size: 12px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .user-role { font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #8892a6; margin-top: 2px; display: flex; flex-wrap: wrap; gap: 4px; }

    .action-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .btn-sm { padding: 6px 12px; border-radius: 8px; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; cursor: pointer; transition: all 0.2s ease; border: 1px solid; background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.1); color: #8892a6; }
    .btn-sm:hover { background: rgba(255,255,255,0.08); color: #fff; }
    .btn-sm.primary { background: rgba(79,140,255, 0.15); border-color: rgba(79,140,255, 0.4); color: #4f8cff; }
    .btn-sm.primary:hover { background: rgba(79,140,255, 0.25); border-color: rgba(79,140,255, 0.7); }

    .sidebar-card { background: linear-gradient(180deg, rgba(22, 26, 38, 0.95), rgba(16, 19, 28, 0.95)); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; padding: 20px; position: sticky; top: 24px; }
    .sidebar-card::before { content: ""; position: absolute; inset: -1px; border-radius: 14px; padding: 1px; background: linear-gradient(135deg, rgba(34,197,94,0.4), transparent 40%, transparent 60%, rgba(34,197,94,0.2)); -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none; opacity: 0.6; }
    .sidebar-title { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #8892a6; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; gap: 8px; }
    .badge-count { background: rgba(79,140,255, 0.2); border: 1px solid rgba(79,140,255, 0.4); color: #4f8cff; padding: 2px 8px; border-radius: 10px; font-size: 10px; }

    .selected-list { min-height: 120px; max-height: 420px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px; }
    .org-group { background: rgba(34, 197, 94, 0.05); border: 1px solid rgba(34, 197, 94, 0.2); border-radius: 10px; padding: 10px 12px; }
    .org-group-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px dashed rgba(34,197,94,0.25); }
    .org-group-name { font-size: 12px; font-weight: 700; color: #22c55e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .org-group-users { display: flex; flex-direction: column; gap: 6px; }
    .selected-item { display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.03); border-radius: 8px; padding: 6px 8px; }
    .selected-item-info { flex: 1; min-width: 0; }
    .selected-item-name { font-size: 11px; font-weight: 500; color: #e7ecf3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .org-group-total { margin-top: 8px; padding-top: 6px; border-top: 1px dashed rgba(34,197,94,0.25); font-size: 10px; color: #8892a6; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .btn-remove { background: none; border: none; color: #ff6b6b; cursor: pointer; font-size: 14px; padding: 4px; transition: all 0.2s; flex-shrink: 0; }
    .btn-remove:hover { color: #ff4444; transform: scale(1.1); }
    .btn-remove-group { background: none; border: none; color: #ff6b6b; cursor: pointer; font-size: 12px; flex-shrink: 0; }

    .empty-state { text-align: center; padding: 20px; border: 1px dashed rgba(255,255,255,0.1); border-radius: 10px; color: #8892a6; font-size: 11px; font-style: italic; }
    .btn-save { width: 100%; padding: 12px; border-radius: 10px; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: 1px solid rgba(34, 197, 94, 0.4); background: rgba(34, 197, 94, 0.15); color: #22c55e; cursor: pointer; transition: all 0.2s ease; }
    .btn-save:hover:not(:disabled) { background: rgba(34, 197, 94, 0.25); border-color: rgba(34, 197, 94, 0.6); box-shadow: 0 0 15px rgba(34, 197, 94, 0.2); color: #fff; }
    .btn-save:disabled { opacity: 0.5; cursor: not-allowed; border-color: rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: #8892a6; }

    @media (max-width: 768px) { .recipients-page { padding: 18px 12px; } .glass-card { padding: 16px; border-radius: 12px; } .page-header { margin-bottom: 18px; padding-bottom: 12px; } .content-grid { gap: 16px; } .sidebar-card { position: static; padding: 16px; border-radius: 12px; } }
    @media (max-width: 480px) { .recipients-page { padding: 14px 8px; } .glass-card { padding: 14px; border-radius: 10px; } .step-label { font-size: 9px; gap: 6px; } .custom-select, .custom-input { padding: 10px 12px; font-size: 11px; } .user-item { padding: 8px; gap: 8px; } .user-name { font-size: 11px; } }
</style>

<div class="recipients-page">
    <div class="max-w-6xl mx-auto">
        <div class="page-header">
            <div class="page-header-left">
                <a href="{{ url()->previous() }}" class="back-btn"><i class="bi bi-arrow-left"></i></a>
                <span class="back-label" data-i18n="back">Бозгашт</span>
            </div>
            <div>
               <p class="text-gray-400 text-xs" data-i18n="page_subtitle">Вилоят → Шаҳр/Ноҳия → Соҳа → Ташкилот → Корбарон</p>
            </div>
        </div>

        <form action="{{ route('documents.recipients.store') }}" method="POST" id="recipientsForm">
            @csrf
            <input type="hidden" name="document_id" value="{{ request('document_id', $documentId ?? '') }}">
            <input type="hidden" name="return_url"  value="{{ request('return_url') }}">
            <div id="hiddenInputs"></div>

            <div class="content-grid">
                <div style="display:flex; flex-direction:column; gap:16px;">
                    <div class="glass-card" id="step1">
                        <label class="step-label blue"><i class="bi bi-building"></i> <span data-i18n="step1">1. Вилоятро интихоб кунед</span> <span class="text-red-500">*</span></label>
                        <select id="regionSelect" class="custom-select" data-i18n-placeholder="ph_region"><option value="">-- Вилоятро интихоб кунед --</option></select>
                    </div>
                    <div class="glass-card hidden" id="step2">
                        <label class="step-label purple"><i class="bi bi-geo-alt"></i> <span data-i18n="step2">2. Шаҳр / Ноҳия</span> <span class="text-red-500">*</span></label>
                        <select id="citySelect" class="custom-select" data-i18n-placeholder="ph_city"><option value="">-- Аввал вилоятро интихоб кунед --</option></select>
                    </div>
                    <div class="glass-card hidden" id="step3">
                        <label class="step-label cyan"><i class="bi bi-briefcase"></i> <span data-i18n="step3">3. Соҳаи фаъолият</span> <span class="text-red-500">*</span></label>
                        <select id="typeSelect" class="custom-select" data-i18n-placeholder="ph_type"></select>
                    </div>
                    <div class="glass-card hidden" id="step4">
                        <label class="step-label amber"><i class="bi bi-search"></i> <span data-i18n="step4">4. Ташкилот ва корбарон</span> <span class="text-red-500">*</span></label>
                        <input type="text" id="orgSearchInput" class="custom-input" data-i18n-placeholder="ph_org_search" placeholder="🔍 -- Ном оварда ҷустуҷӯ кунед --">

                        <div class="action-row" style="margin-top: 12px;">
                            <span style="font-size: 10px; color: #8892a6;" data-i18n="hint_click">Барои дидани корбарон, ташкилотро пахш кунед</span>
                            <div style="display:flex; gap:8px;">
                                <button type="button" id="selectAllBtn" class="btn-sm primary" data-i18n="btn_all_visible">✅ Ҳамаи намоён</button>
                                <button type="button" id="deselectAllBtn" class="btn-sm" data-i18n="btn_clear">❌ Бекор</button>
                            </div>
                        </div>

                        <div id="orgList" class="org-list">
                            <div class="empty-state" data-i18n="waiting_for_filters">Аввал вилоят, шаҳр ва соҳаро интихоб кунед...</div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="sidebar-card">
                        <div class="sidebar-title">
                            <i class="bi bi-cart-check-fill" style="color:#22c55e;"></i>
                            <span data-i18n="cart_title">Гирандагон</span>
                            <span class="badge-count" id="badgeCount">0</span>
                        </div>
                        <div id="selectedSummary" class="selected-list">
                            <div class="empty-state" data-i18n="cart_empty">Ҳоло ҳеҷ кас интихоб нашудааст...</div>
                        </div>
                        <button type="submit" id="saveBtn" disabled class="btn-save">
                            <span data-i18n="btn_save">Захира кардан</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // ✅ ПРАВИЛЬНЫЙ СПИСОК ОБЛАСТЕЙ ТАДЖИКИСТАНА
    const REGIONS_LIST = [
        { id: 1, name_tj: 'Вилояти Суғд', name_ru: 'Согдийская область', name_en: 'Sughd Region' },
        { id: 2, name_tj: 'Вилояти Хатлон', name_ru: 'Хатлонская область', name_en: 'Khatlon Region' },
        { id: 3, name_tj: 'Ноҳияҳои тобеи ҷумҳурӣ', name_ru: 'Районы республиканского подчинения (РРП)', name_en: 'Districts of Republican Subordination' },
        { id: 4, name_tj: 'Вилояти Мухтори Кӯҳистони Бадахшон (ВМКБ)', name_ru: 'Горно-Бадахшанская автономная область (ГБАО)', name_en: 'Gorno-Badakhshan Autonomous Region (GBAO)' },
        { id: 5, name_tj: 'Шаҳри Душанбе', name_ru: 'Город Душанбе (столица)', name_en: 'City of Dushanbe (Capital)' }
    ];

    // ✅ ПОЛНЫЙ ПРАВИЛЬНЫЙ СПИСОК ГОРОДОВ И РАЙОНОВ
    const citiesByRegion = {
        '1': [ // Согдийская область — центр: Худжанд
            { id: 101, name_tj: 'Шаҳри Хуҷанд', name_ru: 'г. Худжанд (центр)', name_en: 'Khujand (capital)' },
            { id: 102, name_tj: 'Шаҳри Бӯстон', name_ru: 'г. Бустон', name_en: 'Buston' },
            { id: 103, name_tj: 'Шаҳри Гулистон', name_ru: 'г. Гулистон', name_en: 'Guliston' },
            { id: 104, name_tj: 'Шаҳри Истаравшан', name_ru: 'г. Истаравшан', name_en: 'Istaravshan' },
            { id: 105, name_tj: 'Шаҳри Истиқлол', name_ru: 'г. Истиклол', name_en: 'Istiklol' },
            { id: 106, name_tj: 'Шаҳри Исфара', name_ru: 'г. Исфара', name_en: 'Isfara' },
            { id: 107, name_tj: 'Шаҳри Конибодом', name_ru: 'г. Канибадам', name_en: 'Kanibadam' },
            { id: 108, name_tj: 'Шаҳри Панҷакент', name_ru: 'г. Пенджикент', name_en: 'Panjakent' },
            { id: 109, name_tj: 'Ноҳияи Айнӣ', name_ru: 'Айнинский район', name_en: 'Ayni District' },
            { id: 110, name_tj: 'Ноҳияи Ашт', name_ru: 'Аштский район', name_en: 'Asht District' },
            { id: 111, name_tj: 'Ноҳияи Бобоҷон Ғафуров', name_ru: 'Бободжон-Гафуровский район', name_en: 'Bobojon Ghafurov District' },
            { id: 112, name_tj: 'Ноҳияи Деваштич', name_ru: 'Деваштичский район', name_en: 'Devashtich District' },
            { id: 113, name_tj: 'Ноҳияи Кӯҳистони Мастчоҳ', name_ru: 'Кухистони-Мастчохский (Горно-Матчинский) район', name_en: 'Kuhistoni Mastchoh District' },
            { id: 114, name_tj: 'Ноҳияи Мастчоҳ', name_ru: 'Матчинский район', name_en: 'Mastchoh District' },
            { id: 115, name_tj: 'Ноҳияи Ҷаббор Расулов', name_ru: 'Джаббор-Расуловский район', name_en: 'Jabbor Rasulov District' },
            { id: 116, name_tj: 'Ноҳияи Зафаробод', name_ru: 'Зафарободский район', name_en: 'Zafarobod District' },
            { id: 117, name_tj: 'Ноҳияи Спитамен', name_ru: 'Спитаменский район', name_en: 'Spitamen District' },
            { id: 118, name_tj: 'Ноҳияи Шаҳристон', name_ru: 'Шахристанский район', name_en: 'Shahriston District' }
        ],
        '2': [ // Хатлонская область — центр: Бохтар
            { id: 201, name_tj: 'Шаҳри Бохтар', name_ru: 'г. Бохтар (бывш. Курган-Тюбе, центр)', name_en: 'Bokhtar (capital)' },
            { id: 202, name_tj: 'Шаҳри Кӯлоб', name_ru: 'г. Куляб', name_en: 'Kulob' },
            { id: 203, name_tj: 'Шаҳри Норак', name_ru: 'г. Нурек', name_en: 'Nurek' },
            { id: 204, name_tj: 'Шаҳри Левакант', name_ru: 'г. Левакант', name_en: 'Levakant' },
            { id: 205, name_tj: 'Ноҳияи Балҷувон', name_ru: 'Бальджуванский район', name_en: 'Baljuvon District' },
            { id: 206, name_tj: 'Ноҳияи Бохтар', name_ru: 'Бохтарский район', name_en: 'Bokhtar District' },
            { id: 207, name_tj: 'Ноҳияи Вахш', name_ru: 'Вахшский район', name_en: 'Vakhsh District' },
            { id: 208, name_tj: 'Ноҳияи Восеъ', name_ru: 'Восейский район', name_en: 'Vose District' },
            { id: 209, name_tj: 'Ноҳияи Данғара', name_ru: 'Дангаринский район', name_en: 'Danghara District' },
            { id: 210, name_tj: 'Ноҳияи Абдураҳмони Ҷомӣ', name_ru: 'Район Абдурахмона Джами', name_en: 'Abdurahmoni Jomi District' },
            { id: 211, name_tj: 'Ноҳияи Ҷайҳун', name_ru: 'Джайхунский район', name_en: 'Jayhun District' },
            { id: 212, name_tj: 'Ноҳияи Қубодиён', name_ru: 'Кубодиёнский район', name_en: 'Qubodiyon District' },
            { id: 213, name_tj: 'Ноҳияи Мӯъминобод', name_ru: 'Муминабадский район', name_en: 'Muminobod District' },
            { id: 214, name_tj: 'Ноҳияи Панҷ', name_ru: 'Пянджский район', name_en: 'Panj District' },
            { id: 215, name_tj: 'Ноҳияи Темурмалик', name_ru: 'Темурмаликский район', name_en: 'Temurmalik District' },
            { id: 216, name_tj: 'Ноҳияи Фархор', name_ru: 'Фархорский район', name_en: 'Farxor District' },
            { id: 217, name_tj: 'Ноҳияи Мир Сайид Алии Ҳамадонӣ', name_ru: 'Район Мир Сайид Алии Хамадони', name_en: 'Mir Sayid Alii Hamadoni District' },
            { id: 218, name_tj: 'Ноҳияи Носири Хусрав', name_ru: 'Район Носири Хусрав', name_en: 'Nosiri Khusrav District' },
            { id: 219, name_tj: 'Ноҳияи Ховалинг', name_ru: 'Ховалингский район', name_en: 'Khovaling District' },
            { id: 220, name_tj: 'Ноҳияи Хуросон', name_ru: 'Хуросонский район', name_en: 'Khuroson District' },
            { id: 221, name_tj: 'Ноҳияи Шаҳритӯс', name_ru: 'Шахритусский район', name_en: 'Shahritus District' },
            { id: 222, name_tj: 'Ноҳияи Шамсиддин Шоҳин', name_ru: 'Район Шамсиддин Шохин', name_en: 'Shamsiddin Shohin District' },
            { id: 223, name_tj: 'Ноҳияи Ёвон', name_ru: 'Яванский район', name_en: 'Yovon District' }
        ],
        '3': [ // Районы республиканского подчинения (РРП)
            { id: 301, name_tj: 'Ноҳияи Варзоб', name_ru: 'Варзобский район', name_en: 'Varzob District' },
            { id: 302, name_tj: 'Ноҳияи Вахдат', name_ru: 'Вахдатский район', name_en: 'Vahdat District' },
            { id: 303, name_tj: 'Ноҳияи Ҳисор', name_ru: 'Гиссарский район', name_en: 'Hissor District' },
            { id: 304, name_tj: 'Ноҳияи Лахш', name_ru: 'Лахшский район', name_en: 'Lakhsh District' },
            { id: 305, name_tj: 'Ноҳияи Нуробод', name_ru: 'Нурабадский район', name_en: 'Nurobod District' },
            { id: 306, name_tj: 'Ноҳияи Рашт', name_ru: 'Раштский район', name_en: 'Rasht District' },
            { id: 307, name_tj: 'Ноҳияи Рӯдакӣ', name_ru: 'Рудакинский район', name_en: 'Rudaki District' },
            { id: 308, name_tj: 'Ноҳияи Сангвор', name_ru: 'Сангворский район', name_en: 'Sangvor District' },
            { id: 309, name_tj: 'Ноҳияи Тоҷикобод', name_ru: 'Таджикабадский район', name_en: 'Tojikobod District' },
            { id: 310, name_tj: 'Ноҳияи Турсунзода', name_ru: 'Турсунзадевский район', name_en: 'Tursunzoda District' },
            { id: 311, name_tj: 'Ноҳияи Файзобод', name_ru: 'Файзабадский район', name_en: 'Fayzobod District' },
            { id: 312, name_tj: 'Ноҳияи Шаҳринав', name_ru: 'Шахринавский район', name_en: 'Shahrinov District' },
            { id: 313, name_tj: 'Ноҳияи Роғун', name_ru: 'Рогунский район', name_en: 'Roghun District' }
        ],
        '4': [ // ГБАО — центр: Хорог
            { id: 401, name_tj: 'Шаҳри Хоруғ', name_ru: 'г. Хорог (центр)', name_en: 'Khorog (capital)' },
            { id: 402, name_tj: 'Ноҳияи Дарвоз', name_ru: 'Дарвазский район', name_en: 'Darvoz District' },
            { id: 403, name_tj: 'Ноҳияи Ванҷ', name_ru: 'Ванчский район', name_en: 'Vanj District' },
            { id: 404, name_tj: 'Ноҳияи Рӯшон', name_ru: 'Рушанский район', name_en: 'Rushon District' },
            { id: 405, name_tj: 'Ноҳияи Шуғнон', name_ru: 'Шугнанский район', name_en: 'Shughnon District' },
            { id: 406, name_tj: 'Ноҳияи Роштқалъа', name_ru: 'Рошткалинский район', name_en: 'Roshtqala District' },
            { id: 407, name_tj: 'Ноҳияи Ишкошим', name_ru: 'Ишкашимский район', name_en: 'Ishkoshim District' },
            { id: 408, name_tj: 'Ноҳияи Мурғоб', name_ru: 'Мургабский район', name_en: 'Murghob District' }
        ],
        '5': [ // Город Душанбе
            { id: 501, name_tj: 'Шаҳри Душанбе', name_ru: 'г. Душанбе (столица)', name_en: 'Dushanbe (Capital)' }
        ]
    };

    const COMPANY_TYPES_STRUCTURE = [
        {
            group: { tj: '1. Мақомоти давлатӣ ва идоракунӣ', ru: '1. Органы власти и управления', en: '1. Government & Administration' },
            options: [
                { value: 'ministry', tj: 'Ҳукумат ва вазоратҳо', ru: 'Правительство и министерства', en: 'Government and ministries' },
                { value: 'local_government', tj: 'Ҳокимият ва мақомоти маҳаллӣ', ru: 'Местное самоуправление', en: 'Local government' },
                { value: 'law_enforcement', tj: 'Сохторҳои қудратӣ ва назоратӣ', ru: 'Силовые и надзорные структуры', en: 'Law enforcement' },
                { value: 'special_agency', tj: 'Агентҳои ихтисосӣ (Кадастр, экология, МХЦ)', ru: 'Специализированные агентства', en: 'Specialized agencies' }
            ]
        },
        {
            group: { tj: '2. Соҳаи иҷтимоӣ', ru: '2. Социальная сфера', en: '2. Social Sphere' },
            options: [
                { value: 'education', tj: 'Маориф (Боғчаҳо, мактабҳо, донишгоҳҳо)', ru: 'Образование', en: 'Education' },
                { value: 'healthcare', tj: 'Тандурустӣ (Беморхонаҳо, клиникаҳо, дорухонаҳо)', ru: 'Здравоохранение', en: 'Healthcare' },
                { value: 'social_protection', tj: 'Ҳимояи иҷтимоӣ (Пенсионӣ, паноҳгоҳҳо)', ru: 'Социальная защита', en: 'Social protection' }
            ]
        },
        {
            group: { tj: '3. Молия ва бизнес', ru: '3. Финансы и бизнес', en: '3. Finance & Business' },
            options: [
                { value: 'bank', tj: 'Бонкҳо ва муассисаҳои молиявӣ', ru: 'Банки и финансы', en: 'Banks and finance' },
                { value: 'business_services', tj: 'Хизматрасонии тиҷоратӣ (Ҳуқуқӣ, нотариалӣ, аудит)', ru: 'Деловые услуги', en: 'Business services' },
                { value: 'it_development', tj: 'IT ва рушди барномавӣ', ru: 'IT и разработка', en: 'IT & Development' }
            ]
        },
        {
            group: { tj: '4. Савдо ва хӯрокворӣ', ru: '4. Торговля и общепит', en: '4. Trade & Catering' },
            options: [
                { value: 'retail', tj: 'Савдо (Марказҳои савдо, супермаркетҳо, бозорҳо)', ru: 'Торговля', en: 'Retail' },
                { value: 'catering', tj: 'Хӯрокворӣ (Ресторанҳо, қаҳвахонаҳо, фастфуд)', ru: 'Общественное питание', en: 'Catering' }
            ]
        },
        {
            group: { tj: '5. Истеҳсолот ва сохтмон', ru: '5. Производство и строительство', en: '5. Manufacturing & Construction' },
            options: [
                { value: 'manufacturing', tj: 'Саноат (Заводҳо, фабрикаҳо, комбинатҳо)', ru: 'Промышленность', en: 'Manufacturing' },
                { value: 'construction', tj: 'Сохтмон (Ширкатҳои девелоперӣ, таъмир)', ru: 'Строительство', en: 'Construction' }
            ]
        },
        {
            group: { tj: '6. Хизматрасонӣ ва рӯзгор', ru: '6. Сфера услуг', en: '6. Services' },
            options: [
                { value: 'household_services', tj: 'Хизматрасонии рӯзгор (Салонҳои зебоӣ, ателье, химчистка)', ru: 'Бытовые услуги', en: 'Household services' },
                { value: 'hospitality', tj: 'Меҳмонхонаҳо ва хобгоҳҳо', ru: 'Гостиничный бизнес', en: 'Hospitality' },
                { value: 'sport_leisure', tj: 'Варзиш ва фароғат (Клубҳои фитнес, кинотеатрҳо, осорхонаҳо)', ru: 'Спорт и досуг', en: 'Sport & Leisure' }
            ]
        },
        {
            group: { tj: '7. Инфрасохтор ва логистика', ru: '7. Инфраструктура', en: '7. Infrastructure' },
            options: [
                { value: 'utilities', tj: 'ЖКХ (Ширкатҳои идоракунанда, обтаъминкунӣ, барқ)', ru: 'ЖКХ', en: 'Utilities' },
                { value: 'transport', tj: 'Нақлиёт (Автовокзалҳо, аэропортҳо, АЗС, таксопаркҳо)', ru: 'Транспорт', en: 'Transport' },
                { value: 'communication', tj: 'Алоқа (Почта, операторони алоқаи мобилӣ)', ru: 'Связь', en: 'Communication' }
            ]
        }
    ];

    const RECIPIENT_TRANSLATIONS = {
        tj: {
            back: "Бозгашт", page_title: "Интихоби гирандагон аз рӯи сохтор", page_subtitle: "Вилоят → Шаҳр/Ноҳия → Соҳа → Ташкилот → Корбарон",
            step1: "1. Вилоятро интихоб кунед", ph_region: "-- Вилоятро интихоб кунед --",
            step2: "2. Шаҳр / Ноҳия", ph_city: "-- Аввал вилоятро интихоб кунед --",
            step3: "3. Соҳаи фаъолият", ph_type: "-- Соҳаро интихоб кунед --",
            step4: "4. Ташкилот ва корбарон", ph_org_search: "🔍 -- Ном оварда ҷустуҷӯ кунед --",
            hint_click: "Барои дидани корбарон, ташкилотро пахш кунед",
            btn_all_visible: "✅ Ҳамаи намоён", btn_clear: "❌ Бекор",
            cart_title: "Гирандагон", cart_empty: "Ҳоло ҳеҷ кас интихоб нашудааст...", btn_save: "Захира кардан",
            no_data: "Дар ин минтақа маълумот ёфт нашуд", no_users: "Дар ин ташкилот корбарон нестанд", total: "Ҳамагӣ",
            waiting_for_filters: "Аввал вилоят, шаҳр ва соҳаро интихоб кунед..."
        },
        ru: {
            back: "Назад", page_title: "Выбор получателей по структуре", page_subtitle: "Область → Город/Район → Сфера → Организация → Сотрудники",
            step1: "1. Выберите область", ph_region: "-- Выберите область --",
            step2: "2. Город / Район", ph_city: "-- Сначала выберите область --",
            step3: "3. Сфера деятельности", ph_type: "-- Выберите сферу --",
            step4: "4. Организация и сотрудники", ph_org_search: "🔍 -- Введите название для поиска --",
            hint_click: "Нажмите на организацию, чтобы увидеть сотрудников",
            btn_all_visible: "✅ Все видимые", btn_clear: "❌ Сбросить",
            cart_title: "Получатели", cart_empty: "Пока никто не выбран...", btn_save: "Сохранить",
            no_data: "В этом регионе данные не найдены", no_users: "В этой организации нет пользователей", total: "Всего",
            waiting_for_filters: "Сначала выберите область, город и сферу..."
        },
        en: {
            back: "Back", page_title: "Select Recipients by Structure", page_subtitle: "Region → City/District → Sector → Organization → Users",
            step1: "1. Select Region", ph_region: "-- Select Region --",
            step2: "2. City / District", ph_city: "-- Select Region first --",
            step3: "3. Sector", ph_type: "-- Select Sector --",
            step4: "4. Organization & Users", ph_org_search: "🔍 -- Type a name to search --",
            hint_click: "Click on organization to see users",
            btn_all_visible: "✅ All visible", btn_clear: "❌ Clear",
            cart_title: "Recipients", cart_empty: "No one selected yet...", btn_save: "Save",
            no_data: "No data found in this region", no_users: "No users in this organization", total: "Total",
            waiting_for_filters: "First select region, city and sector..."
        }
    };

    function applyTranslations(lang) {
        const dict = RECIPIENT_TRANSLATIONS[lang] || RECIPIENT_TRANSLATIONS.tj;
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (dict[key] !== undefined) el.textContent = dict[key];
        });
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            if (dict[key] !== undefined) el.setAttribute('placeholder', dict[key]);
        });
        populateRegions();
        if (currentRegionId) populateCities(currentRegionId);
        if (currentCityId) populateCategories();
        renderCart();
    }

    let currentRegionId = null;
    let currentCityId = null;
    let currentCategory = null;
    let orgsCache = [];
    let cart = new Map();
    let expandedOrgId = null;

    function getTrans(key) {
        const lang = localStorage.getItem('docsign_lang') || 'tj';
        return RECIPIENT_TRANSLATIONS[lang][key] || key;
    }

    function resetDownstream(target) {
        if (target === 'region') {
            currentCityId = null; currentCategory = null; orgsCache = []; expandedOrgId = null;
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step3').classList.add('hidden');
            document.getElementById('step4').classList.add('hidden');
            document.getElementById('citySelect').innerHTML = `<option value="">${getTrans('ph_city')}</option>`;
            document.getElementById('orgSearchInput').value = '';
            document.getElementById('orgList').innerHTML = '';
        } else if (target === 'city') {
            currentCategory = null; orgsCache = []; expandedOrgId = null;
            document.getElementById('step3').classList.add('hidden');
            document.getElementById('step4').classList.add('hidden');
            document.getElementById('orgSearchInput').value = '';
            document.getElementById('orgList').innerHTML = '';
        } else if (target === 'category') {
            orgsCache = []; expandedOrgId = null;
            document.getElementById('step4').classList.add('hidden');
            document.getElementById('orgSearchInput').value = '';
            document.getElementById('orgList').innerHTML = '';
        }
    }

    function populateRegions() {
        const sel = document.getElementById('regionSelect');
        const prev = currentRegionId;
        const lang = localStorage.getItem('docsign_lang') || 'tj';
        sel.innerHTML = `<option value="">${getTrans('ph_region')}</option>`;
        REGIONS_LIST.forEach(region => {
            const name = lang === 'en' ? (region.name_en || region.name_ru) : (lang === 'ru' ? region.name_ru : region.name_tj);
            sel.innerHTML += `<option value="${region.id}" ${prev == region.id ? 'selected' : ''}>${name}</option>`;
        });
    }

    function populateCities(regionId) {
        const sel = document.getElementById('citySelect');
        const lang = localStorage.getItem('docsign_lang') || 'tj';
        sel.innerHTML = `<option value="">${getTrans('ph_city')}</option>`;
        if (!regionId) { document.getElementById('step2').classList.add('hidden'); return; }
        const cities = citiesByRegion[regionId] || [];
        if (cities.length === 0) {
            sel.innerHTML += `<option value="">${getTrans('no_data')}</option>`;
        } else {
            cities.forEach(city => {
                const name = lang === 'en' ? (city.name_en || city.name_ru) : (lang === 'ru' ? city.name_ru : city.name_tj);
                sel.innerHTML += `<option value="${city.id}" ${currentCityId == city.id ? 'selected' : ''}>${name}</option>`;
            });
        }
        document.getElementById('step2').classList.remove('hidden');
    }

    function populateCategories() {
        const sel = document.getElementById('typeSelect');
        const lang = localStorage.getItem('docsign_lang') || 'tj';
        sel.innerHTML = `<option value="">${getTrans('ph_type')}</option>`;
        COMPANY_TYPES_STRUCTURE.forEach(group => {
            const optgroup = document.createElement('optgroup');
            optgroup.label = group.group[lang] || group.group.ru;
            group.options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt[lang] || opt.ru;
                if (currentCategory === opt.value) option.selected = true;
                optgroup.appendChild(option);
            });
            sel.appendChild(optgroup);
        });
        document.getElementById('step3').classList.remove('hidden');
    }

    function loadOrgsForSearch(cityId, category) {
        const list = document.getElementById('orgList');
        list.innerHTML = `<div class="empty-state">Загрузка...</div>`;
        document.getElementById('orgSearchInput').value = '';
        expandedOrgId = null;

        fetch(`/documents/recipients/organizations/${cityId}/${encodeURIComponent(category)}`)
            .then(res => res.json())
            .then(orgs => {
                orgsCache = orgs || [];
                renderOrgList(orgsCache);
                document.getElementById('step4').classList.remove('hidden');
            })
            .catch(() => {
                orgsCache = [];
                list.innerHTML = `<div class="empty-state">${getTrans('no_data')}</div>`;
            });
    }

    function renderOrgList(orgs) {
        const list = document.getElementById('orgList');
        if (!orgs || orgs.length === 0) {
            list.innerHTML = `<div class="empty-state">${getTrans('no_data')}</div>`;
            return;
        }
        list.innerHTML = orgs.map(org => {
            const isExpanded = String(org.id) === String(expandedOrgId);
            return `
            <div class="org-wrapper">
                <div class="org-item ${isExpanded ? 'active' : ''}" onclick="toggleOrgUsers('${org.id}', '${org.name.replace(/'/g, "\\'")}')">
                    <span class="org-item-name">${org.name}</span>
                    <i class="bi bi-chevron-down org-item-icon"></i>
                </div>
                <div id="org-users-${org.id}" class="org-users-container" style="display: ${isExpanded ? 'block' : 'none'};">
                    ${isExpanded ? '<div class="empty-state" style="padding:10px;">Загрузка сотрудников...</div>' : ''}
                </div>
            </div>`;
        }).join('');
    }

    async function toggleOrgUsers(orgId, orgName) {
        const container = document.getElementById(`org-users-${orgId}`);
        const orgItem = container.previousElementSibling;

        if (expandedOrgId === orgId) {
            expandedOrgId = null;
            orgItem.classList.remove('active');
            container.style.display = 'none';
            return;
        }

        if (expandedOrgId) {
            const prevContainer = document.getElementById(`org-users-${expandedOrgId}`);
            if (prevContainer) {
                prevContainer.style.display = 'none';
                prevContainer.previousElementSibling.classList.remove('active');
            }
        }

        expandedOrgId = orgId;
        orgItem.classList.add('active');
        container.style.display = 'block';

        if (!container.dataset.loaded) {
            try {
                const res = await fetch(`/documents/recipients/users/${orgId}`);
                const users = await res.json();
                container.dataset.loaded = 'true';

                if (!users || users.length === 0) {
                    container.innerHTML = `<div class="empty-state" style="padding:10px;">${getTrans('no_users')}</div>`;
                    return;
                }

                container.innerHTML = users.map(u => {
                    const isChecked = isUserInCart(orgId, u.id) ? 'checked' : '';
                    let roleDisplay = u.role || 'USER';
                    if (roleDisplay === 'admin') roleDisplay = 'Админ';
                    else if (roleDisplay === 'super_admin') roleDisplay = 'Супер Админ';
                    else if (roleDisplay === 'employee') roleDisplay = 'Сотрудник';

                    const email = u.email ? `<span style="color:#8892a6; font-size:9px;"> • ${u.email}</span>` : '';
                    const phone = u.phone ? `<span style="color:#8892a6; font-size:9px;"> • ${u.phone}</span>` : '';

                    return `
                    <label class="user-item">
                        <input type="checkbox" class="user-checkbox" value="${u.id}"
                               data-org-id="${orgId}" data-org-name="${orgName.replace(/'/g, "\\'")}"
                               data-name="${u.name}" data-role="${roleDisplay}"
                               data-email="${u.email || ''}" data-phone="${u.phone || ''}"
                               ${isChecked} onchange="handleUserCheckbox(this)">
                        <div class="user-info">
                            <div class="user-name">${u.name}</div>
                            <div class="user-role">${roleDisplay}${email}${phone}</div>
                        </div>
                    </label>`;
                }).join('');
            } catch (e) {
                container.innerHTML = `<div class="empty-state" style="padding:10px; color:#ff6b6b;">Ошибка загрузки</div>`;
            }
        }
    }

    function isUserInCart(orgId, userId) {
        if (!cart.has(String(orgId))) return false;
        return cart.get(String(orgId)).users.has(String(userId));
    }

    window.handleUserCheckbox = function(cb) {
        const orgId = cb.dataset.orgId;
        const orgName = cb.dataset.orgName;
        const userId = cb.value;

        if (cb.checked) {
            addToCart(orgId, orgName, userId, cb.dataset.name, cb.dataset.role, cb.dataset.email, cb.dataset.phone);
        } else {
            removeFromCart(orgId, userId);
        }
    };

    function addToCart(orgId, orgName, userId, userName, userRole, userEmail, userPhone) {
        const key = String(orgId);
        if (!cart.has(key)) cart.set(key, { name: orgName, users: new Map() });
        cart.get(key).users.set(String(userId), { name: userName, role: userRole, email: userEmail, phone: userPhone });
        renderCart();
    }

    function removeFromCart(orgId, userId) {
        const key = String(orgId);
        if (!cart.has(key)) return;
        cart.get(key).users.delete(String(userId));
        if (cart.get(key).users.size === 0) cart.delete(key);
        renderCart();

        const cb = document.querySelector(`.user-checkbox[data-org-id="${orgId}"][value="${userId}"]`);
        if (cb) cb.checked = false;
    }

    window.removeOrgGroup = function(orgId) {
        cart.delete(String(orgId));
        renderCart();
        document.querySelectorAll(`.user-checkbox[data-org-id="${orgId}"]`).forEach(cb => cb.checked = false);
    };

    function renderCart() {
        const summary = document.getElementById('selectedSummary');
        const hidden = document.getElementById('hiddenInputs');
        const saveBtn = document.getElementById('saveBtn');
        let totalCount = 0;
        hidden.innerHTML = '';

        if (cart.size === 0) {
            summary.innerHTML = `<div class="empty-state">${getTrans('cart_empty')}</div>`;
            document.getElementById('badgeCount').textContent = '0';
            saveBtn.disabled = true;
            return;
        }

        let html = '';
        cart.forEach((group, orgId) => {
            const userEntries = Array.from(group.users.entries());
            totalCount += userEntries.length;

            html += `<div class="org-group">
                <div class="org-group-header">
                    <span class="org-group-name" title="${group.name}">✔ ${group.name}</span>
                    <button type="button" class="btn-remove-group" onclick="removeOrgGroup('${orgId}')"><i class="bi bi-x-circle"></i></button>
                </div>
                <div class="org-group-users">`;

            userEntries.forEach(([userId, data]) => {
                let contactInfo = '';
                if (data.email) contactInfo += `<div style="font-size:9px; color:#8892a6; margin-top:2px;">${data.email}</div>`;
                if (data.phone) contactInfo += `<div style="font-size:9px; color:#8892a6;">${data.phone}</div>`;

                html += `
                    <div class="selected-item">
                        <div class="selected-item-info">
                            <span class="selected-item-name">${data.name}</span>
                            ${contactInfo}
                        </div>
                        <button type="button" class="btn-remove" onclick="removeFromCart('${orgId}','${userId}')"><i class="bi bi-x-lg"></i></button>
                    </div>`;
                hidden.innerHTML += `<input type="hidden" name="recipient_ids[]" value="${userId}">`;
            });

            html += `</div><div class="org-group-total">${getTrans('total')}: ${userEntries.length}</div></div>`;
        });

        summary.innerHTML = html;
        document.getElementById('badgeCount').textContent = totalCount;
        saveBtn.disabled = totalCount === 0;
    }

    document.getElementById('selectAllBtn').addEventListener('click', () => {
        if (!expandedOrgId) return;
        document.querySelectorAll(`.user-checkbox[data-org-id="${expandedOrgId}"]`).forEach(cb => {
            cb.checked = true;
            handleUserCheckbox(cb);
        });
    });

    document.getElementById('deselectAllBtn').addEventListener('click', () => {
        if (!expandedOrgId) return;
        document.querySelectorAll(`.user-checkbox[data-org-id="${expandedOrgId}"]`).forEach(cb => {
            cb.checked = false;
            handleUserCheckbox(cb);
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const initialLang = localStorage.getItem('docsign_lang') || 'tj';
        applyTranslations(initialLang);

        document.getElementById('regionSelect').addEventListener('change', function() {
            currentRegionId = this.value;
            resetDownstream('region');
            if (currentRegionId) populateCities(currentRegionId);
        });

        document.getElementById('citySelect').addEventListener('change', function() {
            currentCityId = this.value;
            resetDownstream('city');
            if (currentCityId) {
                populateCategories();
                document.getElementById('step3').classList.remove('hidden');
            }
        });

        document.getElementById('typeSelect').addEventListener('change', function() {
            currentCategory = this.value;
            resetDownstream('category');
            if (currentCategory && currentCityId) loadOrgsForSearch(currentCityId, currentCategory);
        });

        document.getElementById('orgSearchInput').addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            const filtered = q ? orgsCache.filter(o => o.name.toLowerCase().includes(q)) : orgsCache;
            expandedOrgId = null;
            renderOrgList(filtered);
        });

        renderCart();
    });

    window.addEventListener('docsign:lang-changed', (e) => {
        const lang = e.detail?.lang || 'tj';
        applyTranslations(lang);
    });

    window.addEventListener('storage', (e) => {
        if (e.key === 'docsign_lang' && e.newValue) {
            applyTranslations(e.newValue);
        }
    });
</script>
@endsection