@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .recipients-page { color: #e7ecf3; padding: 24px 16px; }
    .form-card { background: linear-gradient(180deg, rgba(22, 26, 38, 0.95), rgba(16, 19, 28, 0.95)); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 28px; position: relative; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
    .form-card::before { content: ""; position: absolute; inset: -1px; border-radius: 16px; padding: 1px; background: linear-gradient(135deg, rgba(168,85,247,0.5), transparent 40%, transparent 60%, rgba(168,85,247,0.3)); -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none; opacity: 0.7; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #8892a6; text-decoration: none; font-size: 12px; font-weight: 600; transition: all 0.25s ease; margin-bottom: 16px; }
    .back-btn:hover { color: #fff; border-color: rgba(168,85,247, 0.5); background: rgba(168,85,247, 0.08); transform: translateX(-2px); }
    .page-title { font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
    .page-title::before { content: ""; width: 4px; height: 18px; background: linear-gradient(180deg, #a855f7, rgba(168,85,247,0.3)); border-radius: 2px; box-shadow: 0 0 8px rgba(168,85,247,0.6); }
    .page-subtitle { font-size: 11px; color: #8892a6; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px; }

    .search-box { width: 100%; height: 42px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0 14px 0 40px; color: #fff; font-size: 13px; outline: none; transition: all 0.2s ease; margin-bottom: 16px; }
    .search-box:focus { border-color: rgba(168,85,247,0.7); box-shadow: 0 0 0 2px rgba(168,85,247,0.15); }
    .search-wrapper { position: relative; margin-bottom: 16px; }
    .search-wrapper i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #8892a6; font-size: 14px; }

    .users-list { display: flex; flex-direction: column; gap: 8px; max-height: 400px; overflow-y: auto; padding-right: 4px; }
    .user-item { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.03); border: 1.5px solid rgba(255,255,255,0.08); border-radius: 11px; padding: 12px 14px; cursor: pointer; transition: all 0.2s ease; }
    .user-item:hover { border-color: rgba(168,85,247,0.35); background: rgba(168,85,247,0.05); }
    .user-item.selected { border-color: rgba(168,85,247,0.8); background: rgba(168,85,247,0.12); box-shadow: 0 0 12px rgba(168,85,247,0.2); }
    .user-avatar { width: 38px; height: 38px; flex-shrink: 0; border-radius: 50%; background: linear-gradient(135deg, #a855f7, #7c3aed); display: flex; align-items: center; justify-content: center; color: #1a0625; font-size: 14px; font-weight: 800; text-transform: uppercase; }
    .user-info { flex: 1; min-width: 0; }
    .user-name { font-size: 13px; font-weight: 700; color: #fff; }
    .user-meta { font-size: 10px; color: #8892a6; margin-top: 2px; display: flex; gap: 10px; flex-wrap: wrap; }
    .user-meta span { display: inline-flex; align-items: center; gap: 4px; }
    .user-check { width: 22px; height: 22px; border-radius: 6px; border: 1.5px solid rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; flex-shrink: 0; }
    .user-item.selected .user-check { background: #a855f7; border-color: #a855f7; color: #fff; box-shadow: 0 0 8px rgba(168,85,247,0.6); }
    .user-item.selected .user-check::after { content: "\F26A"; font-family: "bootstrap-icons"; font-size: 12px; font-weight: 900; }

    .selected-bar { display: flex; align-items: center; justify-content: space-between; background: rgba(168,85,247,0.1); border: 1px solid rgba(168,85,247,0.3); border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; }
    .selected-count { font-size: 13px; font-weight: 700; color: #a855f7; }
    .selected-label { font-size: 11px; color: #8892a6; }

    .btn-save { width: 100%; padding: 13px; background: linear-gradient(135deg, #a855f7, #7c3aed); border: none; border-radius: 10px; color: #fff; font: 700 13px 'Inter', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 16px rgba(168,85,247,0.4); transition: all 0.3s ease; margin-top: 16px; }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(168,85,247,0.5); }
    .btn-save:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    .empty-state { text-align: center; padding: 40px 20px; color: #8892a6; }
    .empty-state i { font-size: 40px; margin-bottom: 12px; display: block; color: rgba(168,85,247,0.4); }
</style>

<div class="recipients-page">
    <div class="max-w-2xl mx-auto">
        <a href="{{ $returnUrl }}" class="back-btn">
            <i class="bi bi-arrow-left"></i> Назад к документу
        </a>

        <div class="form-card">
            <h1 class="page-title">Выбор получателей по региону</h1>
            <p class="page-subtitle">Выберите пользователей которым отправить документ</p>

            <div class="selected-bar">
                <div>
                    <div class="selected-count"><span id="selectedCount">{{ count($selectedIds) }}</span> выбрано</div>
                    <div class="selected-label">Минимум 1 получатель</div>
                </div>
                <button type="button" id="clearAll" style="background:none;border:1px solid rgba(255,107,107,0.4);color:#ff6b6b;padding:6px 12px;border-radius:6px;font-size:11px;cursor:pointer;">Очистить</button>
            </div>

            <div class="search-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" class="search-box" placeholder="Поиск по имени, email, компании...">
            </div>

            <form action="{{ route('documents.recipients.store') }}" method="POST" id="recipientsForm">
                @csrf
                <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                <div id="recipientIdsContainer"></div>

                <div class="users-list" id="usersList">
                    @forelse($users as $u)
                        @php $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($u->name ?? '?', 0, 1)); @endphp
                        <div class="user-item {{ in_array($u->id, $selectedIds) ? 'selected' : '' }}" 
                             data-id="{{ $u->id }}" 
                             data-name="{{ strtolower($u->name) }}" 
                             data-email="{{ strtolower($u->email ?? '') }}"
                             data-company="{{ strtolower($u->company?->name ?? '') }}">
                            <div class="user-avatar">{{ $initial }}</div>
                            <div class="user-info">
                                <div class="user-name">{{ $u->name }}</div>
                                <div class="user-meta">
                                    @if($u->email)<span><i class="bi bi-envelope"></i> {{ $u->email }}</span>@endif
                                    @if($u->company)<span><i class="bi bi-building"></i> {{ $u->company->name }}</span>@endif
                                </div>
                            </div>
                            <div class="user-check"></div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-people"></i>
                            Пользователи не найдены
                        </div>
                    @endforelse
                </div>

                <button type="submit" class="btn-save" id="saveBtn" {{ empty($selectedIds) ? 'disabled' : '' }}>
                    <i class="bi bi-check-circle-fill"></i>
                    Сохранить и вернуться
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectedSet = new Set(@json($selectedIds).map(id => String(id)));
    const container = document.getElementById('recipientIdsContainer');
    const countEl = document.getElementById('selectedCount');
    const saveBtn = document.getElementById('saveBtn');
    const searchInput = document.getElementById('searchInput');
    const clearAll = document.getElementById('clearAll');

    function renderHiddenInputs() {
        container.innerHTML = '';
        selectedSet.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'recipient_ids[]';
            input.value = id;
            container.appendChild(input);
        });
    }

    function updateUI() {
        countEl.textContent = selectedSet.size;
        saveBtn.disabled = selectedSet.size === 0;
        renderHiddenInputs();
    }

    // Инициализация
    renderHiddenInputs();

    // Клик по пользователю
    document.querySelectorAll('.user-item').forEach(item => {
        item.addEventListener('click', function() {
            const id = String(this.dataset.id);
            if (selectedSet.has(id)) {
                selectedSet.delete(id);
                this.classList.remove('selected');
            } else {
                selectedSet.add(id);
                this.classList.add('selected');
            }
            updateUI();
        });
    });

    // Поиск
    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.user-item').forEach(item => {
            const name = item.dataset.name || '';
            const email = item.dataset.email || '';
            const company = item.dataset.company || '';
            const match = !q || name.includes(q) || email.includes(q) || company.includes(q);
            item.style.display = match ? '' : 'none';
        });
    });

    // Очистить всё
    clearAll.addEventListener('click', function() {
        selectedSet.clear();
        document.querySelectorAll('.user-item.selected').forEach(el => el.classList.remove('selected'));
        updateUI();
    });
});
</script>
@endsection