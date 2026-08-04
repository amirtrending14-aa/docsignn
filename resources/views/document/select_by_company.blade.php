@extends('layouts.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        /* Красная тема как на скриншоте */
        --glow: 239, 68, 68; 
        --gold: 234, 179, 8;
        --green: 34, 197, 94;
        
        --line-strong: rgba(var(--glow), 0.5);
        --line-soft: rgba(var(--glow), 0.2);
        --text: #f3f4f6;
        --muted: #9ca3af;
        --line: rgba(255,255,255,0.08);
        --radius: 16px;
        --card-bg: #11131a;
    }

    body { background-color: #050505; }

    .ot-page { 
        min-height: 100vh; padding: 40px 24px 60px; color: var(--text); 
        font-family: 'Inter', sans-serif; position: relative; --card-scale: 1; 
        background: radial-gradient(circle at 50% 0%, #1a0b0b 0%, #050505 60%);
    }

    /* Фоновые эффекты */
    .ot-blob { position: absolute; border-radius: 50%; pointer-events: none; z-index: 0; filter: blur(120px); opacity: .25; }
    .ot-blob-1 { top: -100px; left: -100px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(var(--glow), .4) 0%, transparent 70%); animation: otBlob 20s ease-in-out infinite; }
    .ot-blob-2 { bottom: -100px; right: -100px; width: 600px; height: 600px; background: radial-gradient(circle, rgba(var(--glow), .2) 0%, transparent 70%); animation: otBlob 25s ease-in-out infinite reverse; }
    @keyframes otBlob { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(30px, -30px); } }

    /* Верхняя панель */
    .ot-topbar { max-width: 1400px; margin: 0 auto 28px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; position: relative; z-index: 1; }
    .ot-topbar-left { display: flex; align-items: center; gap: 16px; min-width: 0; flex: 1; }
    .ot-topbar-title { font-size: 24px; font-weight: 800; color: #fff; margin: 0; line-height: 1.2; text-shadow: 0 0 20px rgba(var(--glow), 0.3); }
    .ot-topbar-subtitle { font-size: 13px; color: var(--muted); font-weight: 500; margin-top: 3px; }
    
    .ot-btn-back { 
        display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; 
        background: rgba(255,255,255,0.03); color: var(--text); border-radius: 10px; 
        font-size: 12px; font-weight: 600; text-decoration: none; transition: all .25s ease; 
        border: 1px solid rgba(255,255,255,0.1); 
    }
    .ot-btn-back:hover { background: rgba(var(--glow), 0.1); border-color: rgba(var(--glow), 0.5); color: #fff; box-shadow: 0 0 15px rgba(var(--glow), 0.2); }

    /* Контейнер дерева */
    .ot-tree-wrap { 
        max-width: 1400px; margin: 0 auto; position: relative; z-index: 1; 
        background: rgba(10, 10, 12, 0.6); backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.05); border-radius: var(--radius); 
        padding: 40px 24px; overflow-x: auto; 
        box-shadow: inset 0 0 40px rgba(0,0,0,0.5);
    }
    .ot-tree-header { text-align: center; margin-bottom: 40px; }
    .ot-tree-header h2 { font-size: 20px; font-weight: 700; color: #fff; margin: 0 0 8px; letter-spacing: 0.5px; }
    .ot-tree-header p { font-size: 13px; color: var(--muted); margin: 0; }

    /* Логика линий дерева */
    .org-tree { display: flex; justify-content: center; min-width: fit-content; padding: 10px 0; }
    .org-node { display: flex; flex-direction: column; align-items: center; position: relative; padding-top: calc(30px * var(--card-scale)); }
    .org-node::before { content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 2px; height: calc(30px * var(--card-scale)); background: linear-gradient(to bottom, transparent, var(--line-strong)); }
    .org-node::after { content: ''; position: absolute; top: 0; height: 2px; background: var(--line-strong); }
    .org-node:first-child::after { left: 50%; width: 50%; }
    .org-node:last-child::after { right: 50%; width: 50%; }
    .org-node:not(:first-child):not(:last-child)::after { left: 0; width: 100%; }
    .org-node:only-child::after { display: none; }
    .org-tree > .org-node::before, .org-tree > .org-node::after { display: none; }
    .org-tree > .org-node { padding-top: 0; }
    
    .org-children { display: flex; justify-content: center; gap: calc(24px * var(--card-scale)); padding-top: calc(30px * var(--card-scale)); position: relative; }
    .org-children::before { content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 2px; height: calc(30px * var(--card-scale)); background: linear-gradient(to bottom, var(--line-strong), transparent); }
    
    .org-dot { 
        position: absolute; top: -6px; left: 50%; transform: translateX(-50%); 
        width: calc(10px * var(--card-scale)); height: calc(10px * var(--card-scale)); border-radius: 50%; background: #000; 
        border: 2px solid rgb(var(--glow)); box-shadow: 0 0 10px rgba(var(--glow), .8); z-index: 3; 
    }
    .org-tree > .org-node > .org-card .org-dot { display: none; }

    /* --- СТИЛИ КАРТОЧКИ (ПОД ФОТО) --- */
    .org-card { 
        position: relative; width: calc(240px * var(--card-scale)); /* Чуть шире */
        background: var(--card-bg); 
        border: 1px solid rgba(var(--glow), 0.3); 
        border-radius: calc(12px * var(--card-scale)); 
        padding: calc(14px * var(--card-scale)); 
        transition: all .3s cubic-bezier(.4, 0, .2, 1); 
        z-index: 2;
        box-shadow: 0 4px 20px rgba(0,0,0,0.5), inset 0 0 20px rgba(var(--glow), 0.05);
    }
    .org-card:hover { 
        border-color: rgba(var(--glow), 0.8); 
        box-shadow: 0 0 25px rgba(var(--glow), 0.4), inset 0 0 10px rgba(var(--glow), 0.1); 
        transform: translateY(-4px); 
    }

    /* Header: Avatar + Badges */
    .oc-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: calc(12px * var(--card-scale)); }
    .oc-avatar { 
        width: calc(38px * var(--card-scale)); height: calc(38px * var(--card-scale)); border-radius: calc(8px * var(--card-scale)); 
        background: linear-gradient(135deg, #ef4444, #991b1b); 
        display: grid; place-items: center; 
        font-size: calc(14px * var(--card-scale)); font-weight: 900; font-style: italic; color: #fff; 
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        border: 1px solid rgba(255,255,255,0.1);
    }
    .oc-badges { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
    .oc-lvl { 
        background: #ef4444; color: #fff; font-size: calc(9px * var(--card-scale)); font-weight: 800; 
        padding: 2px 6px; border-radius: 4px; font-family: 'JetBrains Mono', monospace;
        box-shadow: 0 0 8px rgba(239, 68, 68, 0.6);
    }
    .oc-status { display: flex; align-items: center; gap: 4px; font-size: calc(9px * var(--card-scale)); font-weight: 700; text-transform: uppercase; }
    .oc-status.on { color: #4ade80; }
    .oc-status.off { color: #6b7280; }
    .oc-status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; box-shadow: 0 0 6px currentColor; }

    /* Name */
    .oc-name { 
        font-size: calc(13px * var(--card-scale)); font-weight: 700; color: #fff; text-align: center; 
        margin: 0 0 calc(12px * var(--card-scale)); line-height: 1.3; word-break: break-word;
    }

    /* Type Badge (Красная плашка как "ФИНАНСЫ") */
    .oc-type-badge { 
        display: block; width: 100%; text-align: center;
        background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); 
        color: #fca5a5; padding: calc(5px * var(--card-scale)); border-radius: 6px; 
        font-size: calc(10px * var(--card-scale)); font-weight: 800; text-transform: uppercase; 
        margin-bottom: calc(12px * var(--card-scale));
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    /* Root Badge (Золотая плашка, если корень) */
    .oc-root-badge {
        display: flex; align-items: center; justify-content: center; gap: 4px;
        width: 100%; background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.3); 
        color: #fde047; padding: calc(5px * var(--card-scale)); border-radius: 6px; 
        font-size: calc(10px * var(--card-scale)); font-weight: 800; text-transform: uppercase; 
        margin-bottom: calc(12px * var(--card-scale));
    }

    /* Location Info */
    .oc-info-row { 
        display: flex; align-items: center; gap: 8px; font-size: calc(11px * var(--card-scale)); color: #d1d5db; 
        margin-bottom: 6px; 
    }
    .oc-info-row i { color: #ef4444; font-size: calc(12px * var(--card-scale)); width: 14px; text-align: center; flex-shrink: 0; }
    .oc-info-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Footer */
    .oc-footer { 
        margin-top: calc(12px * var(--card-scale)); padding-top: calc(12px * var(--card-scale)); 
        border-top: 1px solid rgba(255,255,255,0.05); 
        display: flex; flex-direction: column; gap: 10px;
    }
    
    /* Subdivisions Counter (Зеленая плашка) */
    .oc-subdivs { 
        display: flex; align-items: center; justify-content: center; gap: 6px; 
        background: rgba(34, 197, 94, 0.05); border: 1px solid rgba(34, 197, 94, 0.2); 
        padding: calc(5px * var(--card-scale)); border-radius: 6px; color: #4ade80; 
        font-size: calc(10px * var(--card-scale)); font-weight: 600;
    }
    .oc-subdivs span { font-weight: 800; color: #fff; }

    /* Actions */
    .oc-actions { display: flex; gap: 8px; }
    .oc-act { 
        flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
        height: calc(30px * var(--card-scale)); border-radius: 6px; border: 1px solid rgba(255,255,255,0.1); 
        background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; 
        font-size: calc(11px * var(--card-scale)); font-weight: 600; transition: all .2s; cursor: pointer;
    }
    .oc-act:hover { background: rgba(255,255,255,0.08); }
    .oc-act.select { 
        background: linear-gradient(90deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.05)); 
        border-color: rgba(239, 68, 68, 0.5); color: #fca5a5; 
    }
    .oc-act.select:hover { 
        background: rgba(239, 68, 68, 0.3); border-color: #ef4444; color: #fff;
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.3);
    }

    /* Empty State */
    .ot-empty { text-align: center; padding: 70px 20px; }
    .ot-empty-icon { width: 90px; height: 90px; border-radius: 24px; background: rgba(255, 255, 255, .05); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; border: 1px solid var(--line); }
    .ot-empty-icon i { font-size: 40px; color: var(--muted); }
    .ot-empty-title { font-size: 22px; font-weight: 800; color: var(--text); margin-bottom: 10px; }
    .ot-empty-desc { font-size: 14px; color: var(--muted); }

    /* Modal Styles (Updated to Red Theme) */
    .m-ov { position: fixed; inset: 0; background: rgba(0,0,0,.85); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 9999; padding: 20px; }
    .m-ov.active { display: flex; animation: mF .2s ease; }
    @keyframes mF { from { opacity: 0; } to { opacity: 1; } }
    .m-bx { width: 100%; max-width: 500px; max-height: 80vh; display: flex; flex-direction: column; background: #11131a; border: 1px solid rgba(var(--glow), .4); border-radius: 16px; overflow: hidden; box-shadow: 0 0 40px rgba(var(--glow), 0.2); animation: mP .25s cubic-bezier(.16,1,.3,1); }
    @keyframes mP { from { transform: translateY(16px) scale(.96); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }
    .m-hd { padding: 18px 20px; background: rgba(239, 68, 68, 0.1); display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 1px solid rgba(255,255,255,.05); }
    .m-hd h4 { margin: 0 0 2px; font-size: 15px; font-weight: 800; color: #fff; }
    .m-hd span { font-size: 11px; color: rgba(255,255,255,.55); font-weight: 600; }
    .m-cl { width: 28px; height: 28px; border-radius: 7px; border: 1px solid rgba(255,255,255,.15); background: rgba(0,0,0,.25); color: #fff; display: grid; place-items: center; cursor: pointer; transition: all .2s; flex-shrink: 0; font-size: 12px; }
    .m-cl:hover { background: rgba(248,113,113,.25); border-color: rgba(248,113,113,.4); }
    .m-bd { padding: 10px; overflow-y: auto; flex: 1; }
    .m-user-row { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 10px; transition: background .2s; cursor: pointer; border: 1px solid transparent; }
    .m-user-row:hover { background: rgba(255,255,255,.03); }
    .m-user-row.selected { background: rgba(var(--glow), 0.08); border-color: rgba(var(--glow), 0.4); }
    .m-chk { width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.2); border-radius: 5px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; }
    .m-user-row.selected .m-chk { background: rgb(var(--glow)); border-color: rgb(var(--glow)); }
    .m-user-row.selected .m-chk::after { content: "\F26A"; font-family: "bootstrap-icons"; font-size: 12px; color: #fff; font-weight: 900; }
    .m-av { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.1); display: grid; place-items: center; font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0; }
    .m-inf { min-width: 0; flex: 1; }
    .m-nm { font-size: 13px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .m-em { font-size: 11px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .m-ft { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.05); display: flex; gap: 10px; justify-content: flex-end; }
    .m-btn { padding: 10px 20px; border-radius: 8px; font: 600 12px 'Inter', sans-serif; cursor: pointer; transition: all 0.2s; border: none; display: inline-flex; align-items: center; gap: 6px; }
    .m-btn-cancel { background: rgba(255,255,255,0.05); color: var(--muted); border: 1px solid rgba(255,255,255,0.1); }
    .m-btn-cancel:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .m-btn-save { background: linear-gradient(135deg, #ef4444, #b91c1c); color: #fff; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }
    .m-btn-save:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4); }
    .m-btn-save:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    @media (max-width: 768px) {
        .ot-page { padding: 28px 16px 45px; }
        .ot-topbar { flex-direction: column; align-items: stretch; }
        .ot-tree-wrap { padding: 28px 12px; }
    }
</style>

<div class="ot-page" id="otPage">
    <div class="ot-blob ot-blob-1"></div>
    <div class="ot-blob ot-blob-2"></div>

    <div class="ot-topbar">
        <div class="ot-topbar-left">
            <a href="{{ $returnUrl ?? route('documents.create') }}" class="ot-btn-back">
                <i class="bi bi-arrow-left"></i> <span>Назад к документу</span>
            </a>
            <div>
                <div class="ot-topbar-title">Выбор получателей из компаний</div>
                <div class="ot-topbar-subtitle">Нажмите "Выбрать" на карточке компании, чтобы отметить сотрудников</div>
            </div>
        </div>
    </div>

    <div class="ot-tree-wrap">
        <div class="ot-tree-header">
            <h2>Иерархическое дерево компаний</h2>
            <p>Визуальная структура для выбора получателей документа</p>
        </div>

        @if(isset($nestedTree) && $nestedTree->count() > 0)
            <div class="org-tree">
                @foreach($nestedTree as $node)
                    @include('document.select_company_node', ['node' => $node])
                @endforeach
            </div>
        @else
            <div class="ot-empty">
                <div class="ot-empty-icon"><i class="bi bi-building"></i></div>
                <div class="ot-empty-title">Нет компаний</div>
                <div class="ot-empty-desc">В системе пока нет зарегистрированных компаний</div>
            </div>
        @endif
    </div>
</div>

{{-- Modal --}}
<div class="m-ov" id="userSelectModal" onclick="if(event.target===this)closeModal()">
    <div class="m-bx">
        <div class="m-hd">
            <div>
                <h4 id="modalCompanyName">Компания</h4>
                <span id="modalUserCount">0 сотрудников</span>
            </div>
            <button type="button" class="m-cl" onclick="closeModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="m-bd" id="modalUserList"></div>
        <div class="m-ft">
            <button type="button" class="m-btn m-btn-cancel" onclick="closeModal()">Отмена</button>
            <button type="button" class="m-btn m-btn-save" id="modalSaveBtn" onclick="saveSelection()" disabled>
                <i class="bi bi-check-lg"></i> Сохранить выбор
            </button>
        </div>
    </div>
</div>

<form id="selectionForm" action="{{ route('documents.select-by-company.store') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="company_id" id="formCompanyId">
    <input type="hidden" name="user_ids" id="formUserIds">
    <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('documents.create') }}">
</form>

<script>
    const allCompanies = @json($companiesData ?? []);
    let currentCompanyId = null;
    let selectedUserIds = new Set();

    function selectCompanyUsers(companyId) {
        currentCompanyId = companyId;
        const company = allCompanies.find(c => c.id === companyId);
        if (!company) return;

        document.getElementById('modalCompanyName').textContent = company.name;
        document.getElementById('modalUserCount').textContent = company.users.length + ' сотрудников';
        
        const list = document.getElementById('modalUserList');
        list.innerHTML = '';
        selectedUserIds.clear();
        document.getElementById('modalSaveBtn').disabled = true;

        if (company.users.length === 0) {
            list.innerHTML = '<div style="text-align:center;padding:30px;color:#666;">В этой компании нет сотрудников</div>';
        } else {
            company.users.forEach(u => {
                const initial = (u.name || '?').charAt(0).toUpperCase();
                const div = document.createElement('div');
                div.className = 'm-user-row';
                div.dataset.userId = u.id;
                div.onclick = () => toggleUser(u.id, div);
                div.innerHTML = `
                    <div class="m-chk"></div>
                    <div class="m-av">${initial}</div>
                    <div class="m-inf">
                        <div class="m-nm">${escapeHtml(u.name)}</div>
                        <div class="m-em">${escapeHtml(u.email || 'Нет email')}</div>
                    </div>
                `;
                list.appendChild(div);
            });
        }

        document.getElementById('userSelectModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('userSelectModal').classList.remove('active');
        document.body.style.overflow = '';
        currentCompanyId = null;
    }

    function toggleUser(userId, element) {
        if (selectedUserIds.has(userId)) {
            selectedUserIds.delete(userId);
            element.classList.remove('selected');
        } else {
            selectedUserIds.add(userId);
            element.classList.add('selected');
        }
        document.getElementById('modalSaveBtn').disabled = selectedUserIds.size === 0;
    }

    function saveSelection() {
        if (!currentCompanyId || selectedUserIds.size === 0) return;
        document.getElementById('formCompanyId').value = currentCompanyId;
        document.getElementById('formUserIds').value = Array.from(selectedUserIds).join(',');
        document.getElementById('selectionForm').submit();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endsection