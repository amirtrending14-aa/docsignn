@extends('layouts.admin')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@php
$authUser = auth()->user();
$hex = ltrim($department->color ?? '#4f8cff', '#');
$r = hexdec(substr($hex, 0, 2));
$g = hexdec(substr($hex, 2, 2));
$b = hexdec(substr($hex, 4, 2));
$rgb = "$r, $g, $b";
@endphp

<style>
    .send-page{min-height:100vh;padding:32px 24px 80px;color:var(--text);font-family:'Inter',sans-serif;background:var(--bg,#0a0d14);}
    .send-wrap{max-width:1000px;margin:0 auto;}

    .page-topbar{display:flex;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap;}
    .back-btn{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);color:var(--text);text-decoration:none;transition:all .3s;flex-shrink:0;}
    .back-btn:hover{transform:translateX(-3px);border-color:rgba(255,255,255,0.2);}
    .back-btn i{font-size:18px;}
    .logo-mark{width:50px;height:50px;border-radius:14px;display:grid;place-items:center;background:linear-gradient(135deg,rgba(var(--rc),0.25),rgba(var(--rc),0.05));border:1px solid rgba(var(--rc),0.3);flex-shrink:0;font-size:22px;}
    .topbar-info{flex:1;min-width:0;}
    .topbar-title{font-size:22px;font-weight:800;letter-spacing:-0.4px;margin:0;color:var(--text);}
    .topbar-subtitle{font-size:12px;color:var(--muted);font-weight:600;margin-top:3px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
    .dept-badge{display:inline-flex;align-items:center;gap:5px;padding:2px 9px;border-radius:7px;font-size:11px;font-weight:700;color:rgba(var(--rc),1);background:rgba(var(--rc),0.12);border:1px solid rgba(var(--rc),0.28);}

    .send-grid{display:grid;grid-template-columns:1fr 320px;gap:20px;}
    .send-card{padding:28px;border-radius:20px;background:rgba(255,255,255,0.025);border:1px solid rgba(255,255,255,0.06);}

    .form-group{margin-bottom:22px;}
    .form-label{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);margin-bottom:10px;}
    .form-label i{color:rgba(var(--rc),1);font-size:13px;}
    .form-label .req{color:#ff6363;font-size:14px;}

    .form-select,.form-textarea{width:100%;padding:13px 16px;border-radius:12px;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.08);color:var(--text);font-size:14px;font-weight:500;font-family:'Inter',sans-serif;outline:none;transition:all .25s;}
    .form-textarea{resize:vertical;min-height:100px;line-height:1.6;}
    .form-select{appearance:none;cursor:pointer;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%238892a6' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 16px center;padding-right:40px;}
    .form-select option{background:#0a0d14;color:var(--text);}
    .form-select:focus,.form-textarea:focus{border-color:rgba(var(--rc),0.5);box-shadow:0 0 0 3px rgba(var(--rc),0.1);background:rgba(0,0,0,0.5);}
    .form-error{display:flex;align-items:center;gap:6px;color:#ff6363;font-size:11px;font-weight:600;margin-top:6px;}

    .form-actions{display:flex;gap:12px;padding-top:24px;margin-top:8px;border-top:1px solid rgba(255,255,255,0.06);}
    .btn-send{position:relative;overflow:hidden;flex:1;display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:14px 24px;border-radius:12px;border:1px solid rgba(var(--rc),0.4);cursor:pointer;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(var(--rc),1);background:rgba(var(--rc),0.12);transition:all .3s ease;}
    .btn-send:hover{transform:translateY(-2px);background:rgba(var(--rc),0.22);border-color:rgba(var(--rc),0.6);}
    .btn-cancel{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 22px;border-radius:12px;text-decoration:none;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);transition:all .25s ease;}
    .btn-cancel:hover{color:#ff6363;border-color:rgba(255,99,99,0.3);background:rgba(255,99,99,0.06);}

    /* PREVIEW */
    .preview-wrap{position:sticky;top:24px;align-self:start;}
    .preview-card{padding:22px;border-radius:20px;background:rgba(255,255,255,0.025);border:1px solid rgba(255,255,255,0.06);}
    .preview-title{display:flex;align-items:center;gap:8px;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.06);}
    .pv-block{padding:14px;border-radius:12px;background:rgba(0,0,0,0.25);border:1px solid rgba(255,255,255,0.05);margin-bottom:12px;}
    .pv-block:last-child{margin-bottom:0;}
    .pv-label{font-size:9px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;display:flex;align-items:center;gap:6px;}
    .pv-label i{color:rgba(var(--rc),1);}
    .pv-value{display:flex;align-items:center;gap:10px;font-size:13px;font-weight:700;color:var(--text);}
    .pv-value.empty{color:var(--muted);font-weight:500;font-style:italic;}
    .pv-av{width:34px;height:34px;border-radius:9px;display:grid;place-items:center;font-size:13px;font-weight:800;color:#0a0d14;background:linear-gradient(135deg,rgba(var(--rc),1),rgba(var(--rc),0.6));flex-shrink:0;}
    .pv-doc-ic{width:34px;height:34px;border-radius:9px;display:grid;place-items:center;font-size:16px;background:rgba(var(--rc),0.12);border:1px solid rgba(var(--rc),0.28);flex-shrink:0;}

    .warn-box{display:flex;align-items:center;gap:12px;padding:16px;border-radius:14px;background:rgba(251,191,36,0.06);border:1px solid rgba(251,191,36,0.2);color:#fbbf24;font-size:13px;font-weight:600;margin-bottom:20px;}
    .warn-box i{font-size:20px;flex-shrink:0;}

    @media (max-width:900px){.send-grid{grid-template-columns:1fr;}.preview-wrap{position:static;order:-1;}}
    @media (max-width:480px){.send-page{padding:18px 12px 44px;}.send-card{padding:18px;}.form-actions{flex-direction:column;}.btn-send,.btn-cancel{width:100%;}}
</style>

<div class="send-page" style="--rc: {{ $rgb }}">
    <div class="send-wrap">

        {{-- TOP BAR --}}
        <div class="page-topbar">
            <a href="{{ route('departments.show', $department) }}" class="back-btn"><i class="bi bi-arrow-left"></i></a>
            <div class="logo-mark">{{ $department->icon }}</div>
            <div class="topbar-info">
                <h1 class="topbar-title" data-i18n="title">Отправить документ</h1>
                <div class="topbar-subtitle">
                    <span class="dept-badge"><i class="bi bi-diagram-3-fill"></i> {{ $department->name }}</span>
                    <span data-i18n="subtitle">Выберите документ и получателя</span>
                </div>
            </div>
        </div>

        {{-- ПРЕДУПРЕЖДЕНИЯ --}}
        @if($members->isEmpty())
        <div class="warn-box"><i class="bi bi-person-x-fill"></i> <span data-i18n="noMembers">В этом отделе нет сотрудников — некому отправлять.</span></div>
        @endif
        @if($documents->isEmpty())
        <div class="warn-box"><i class="bi bi-file-earmark-x-fill"></i> <span data-i18n="noDocs">Нет доступных документов для отправки.</span></div>
        @endif

        <div class="send-grid">

            {{-- FORM --}}
            <div class="send-card">
                <form action="{{ route('departments.send-document.store', $department) }}" method="POST">
                    @csrf

                    {{-- DOCUMENT --}}
                    <div class="form-group">
                        <label class="form-label" for="document_id">
                            <i class="bi bi-file-earmark-text-fill"></i>
                            <span data-i18n="docLabel">Документ</span>
                            <span class="req">*</span>
                        </label>
                        <select id="document_id" name="document_id" class="form-select" required {{ $documents->isEmpty() ? 'disabled' : '' }}>
                            <option value="" data-i18n="selectDoc">— Выберите документ —</option>
                            @foreach($documents as $doc)
                            <option value="{{ $doc->id }}" data-name="{{ $doc->name }}" {{ old('document_id') == $doc->id ? 'selected' : '' }}>
                            {{ $doc->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('document_id')<span class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>@enderror
                    </div>

                    {{-- RECIPIENT --}}
                    <div class="form-group">
                        <label class="form-label" for="recipient_id">
                            <i class="bi bi-person-fill"></i>
                            <span data-i18n="recipientLabel">Получатель (из отдела)</span>
                            <span class="req">*</span>
                        </label>
                        <select id="recipient_id" name="recipient_id" class="form-select" required {{ $members->isEmpty() ? 'disabled' : '' }}>
                            <option value="" data-i18n="selectRecipient">— Выберите сотрудника —</option>
                            @foreach($members as $m)
                            <option value="{{ $m->id }}" data-name="{{ $m->name }}" data-email="{{ $m->email }}" {{ old('recipient_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->name }} ({{ $m->email }})
                            </option>
                            @endforeach
                        </select>
                        @error('recipient_id')<span class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>@enderror
                    </div>

                    {{-- MESSAGE --}}
                    <div class="form-group">
                        <label class="form-label" for="message">
                            <i class="bi bi-chat-left-text-fill"></i>
                            <span data-i18n="msgLabel">Сообщение</span>
                        </label>
                        <textarea id="message" name="message" rows="3" class="form-textarea" data-i18n-placeholder="msgPlaceholder" placeholder="Сопроводительное сообщение (необязательно)..." maxlength="1000">{{ old('message') }}</textarea>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="form-actions">
                        <button type="submit" class="btn-send" {{ ($members->isEmpty() || $documents->isEmpty()) ? 'disabled style=opacity:0.4;cursor:not-allowed' : '' }}>
                            <i class="bi bi-send-fill"></i>
                            <span data-i18n="sendBtn">Отправить</span>
                        </button>
                        <a href="{{ route('departments.show', $department) }}" class="btn-cancel">
                            <i class="bi bi-x-lg"></i>
                            <span data-i18n="cancelBtn">Отмена</span>
                        </a>
                    </div>
                </form>
            </div>

            {{-- PREVIEW --}}
            <div class="preview-wrap">
                <div class="preview-card">
                    <div class="preview-title"><i class="bi bi-eye-fill"></i> <span data-i18n="previewTitle">Предпросмотр отправки</span></div>

                    <div class="pv-block">
                        <div class="pv-label"><i class="bi bi-file-earmark-text-fill"></i> <span data-i18n="pvDoc">Документ</span></div>
                        <div class="pv-value empty" id="pvDoc"><span data-i18n="pvNotSelected">Не выбран</span></div>
                    </div>

                    <div class="pv-block">
                        <div class="pv-label"><i class="bi bi-person-fill"></i> <span data-i18n="pvRecipient">Получатель</span></div>
                        <div class="pv-value empty" id="pvRecipient"><span data-i18n="pvNotSelected">Не выбран</span></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const T = {
            ru:{title:'Отправить документ',subtitle:'Выберите документ и получателя',docLabel:'Документ',selectDoc:'— Выберите документ —',recipientLabel:'Получатель (из отдела)',selectRecipient:'— Выберите сотрудника —',msgLabel:'Сообщение',msgPlaceholder:'Сопроводительное сообщение (необязательно)...',sendBtn:'Отправить',cancelBtn:'Отмена',previewTitle:'Предпросмотр отправки',pvDoc:'Документ',pvRecipient:'Получатель',pvNotSelected:'Не выбран',noMembers:'В этом отделе нет сотрудников — некому отправлять.',noDocs:'Нет доступных документов для отправки.'},
            tj:{title:'Фиристодани ҳуҷҷат',subtitle:'Ҳуҷҷат ва гирандаро интихоб кунед',docLabel:'Ҳуҷҷат',selectDoc:'— Ҳуҷҷатро интихоб кунед —',recipientLabel:'Гиранда (аз шӯъба)',selectRecipient:'— Кормандро интихоб кунед —',msgLabel:'Паём',msgPlaceholder:'Паёми ҳамроҳ (ихтиёрӣ)...',sendBtn:'Фиристодан',cancelBtn:'Бекор',previewTitle:'Пешнамоиши фиристанӣ',pvDoc:'Ҳуҷҷат',pvRecipient:'Гиранда',pvNotSelected:'Интихоб нашудааст',noMembers:'Дар ин шӯъба кормандон нестанд — касе барои фиристодан нест.',noDocs:'Ҳуҷҷатҳои дастрас барои фиристодан нестанд.'},
            en:{title:'Send Document',subtitle:'Select document and recipient',docLabel:'Document',selectDoc:'— Select document —',recipientLabel:'Recipient (from department)',selectRecipient:'— Select employee —',msgLabel:'Message',msgPlaceholder:'Accompanying message (optional)...',sendBtn:'Send',cancelBtn:'Cancel',previewTitle:'Send preview',pvDoc:'Document',pvRecipient:'Recipient',pvNotSelected:'Not selected',noMembers:'No employees in this department — no one to send to.',noDocs:'No available documents to send.'}
        };

        let lang = localStorage.getItem('docsign_lang') || 'ru';
        const dict = () => T[lang] || T.ru;

        const docSel = document.getElementById('document_id');
        const recSel = document.getElementById('recipient_id');
        const pvDoc = document.getElementById('pvDoc');
        const pvRecipient = document.getElementById('pvRecipient');

        function updatePreview() {
            const d = dict();

            // документ
            if (docSel.value && docSel.selectedIndex > 0) {
                const name = docSel.options[docSel.selectedIndex].dataset.name;
                pvDoc.className = 'pv-value';
                pvDoc.innerHTML = `<span class="pv-doc-ic"><i class="bi bi-file-earmark-text-fill"></i></span> ${esc(name)}`;
            } else {
                pvDoc.className = 'pv-value empty';
                pvDoc.innerHTML = `<span>${d.pvNotSelected}</span>`;
            }

            // получатель
            if (recSel.value && recSel.selectedIndex > 0) {
                const opt = recSel.options[recSel.selectedIndex];
                const name = opt.dataset.name;
                const email = opt.dataset.email;
                const initial = (name || '?').charAt(0).toUpperCase();
                pvRecipient.className = 'pv-value';
                pvRecipient.innerHTML = `<span class="pv-av">${esc(initial)}</span><span style="min-width:0"><span style="display:block">${esc(name)}</span><small style="color:var(--muted);font-weight:500;font-size:11px">${esc(email)}</small></span>`;
            } else {
                pvRecipient.className = 'pv-value empty';
                pvRecipient.innerHTML = `<span>${d.pvNotSelected}</span>`;
            }
        }

        function applyLang(l) {
            lang = l;
            const d = dict();
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const k = el.getAttribute('data-i18n');
                if (d[k] !== undefined) el.textContent = d[k];
            });
            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                const k = el.getAttribute('data-i18n-placeholder');
                if (d[k] !== undefined) el.setAttribute('placeholder', d[k]);
            });
            updatePreview();
        }

        function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

        docSel.addEventListener('change', updatePreview);
        recSel.addEventListener('change', updatePreview);

        applyLang(lang);
        window.addEventListener('docsign:lang-changed', e => applyLang(e.detail?.lang || 'ru'));
        window.addEventListener('storage', e => { if (e.key === 'docsign_lang' && e.newValue) applyLang(e.newValue); });
    });
</script>

@endsection