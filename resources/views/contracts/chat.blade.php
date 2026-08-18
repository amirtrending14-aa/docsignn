@extends('layouts.admin')

@section('content')
<style>
.rag-wrap{max-width:960px;margin:24px auto;color:#eee;font-family:'Segoe UI',Tahoma,sans-serif}
.rag-title{font-size:24px;font-weight:700;color:#f5c542;margin-bottom:4px}
.rag-sub{color:#8a8a8a;margin-bottom:20px;font-size:13px}
.rag-card{background:#151515;border:1px solid #332b12;border-radius:12px;margin-bottom:16px;overflow:hidden}
.rag-card>h3{margin:0;padding:12px 18px;font-size:14px;color:#f5c542;background:#1c1707;border-bottom:1px solid #332b12}
.rag-body{padding:16px 18px;display:grid;grid-template-columns:1fr;gap:12px}
.rag-field label{display:block;font-size:12px;color:#9a9a9a;margin-bottom:5px}
.rag-field input,.rag-field select{width:100%;background:#0c0c0c;border:1px solid #3d3d3d;border-radius:8px;color:#fff;padding:9px 12px;font-size:14px;outline:none}
.rag-field input:focus,.rag-field select:focus{border-color:#f5c542}
.rag-btn{display:block;width:100%;padding:14px;font-size:16px;font-weight:700;border:none;border-radius:10px;background:linear-gradient(90deg,#f5c542,#e0a92c);color:#111;cursor:pointer}
.rag-btn:disabled{opacity:.6;cursor:wait}
#chatText{white-space:pre-wrap;color:#ddd;line-height:1.7;font-size:14px}
</style>

<div class="rag-wrap">
    <div class="rag-title">💬 Чат по документам</div>
    <div class="rag-sub">Выбери файл из архива и задай вопрос — ИИ ответит с указанием страницы/абзаца</div>

    <form id="chatForm">
        @csrf
        <div class="rag-card">
            <h3>📁 Выбери документ и задай вопрос</h3>
            <div class="rag-body">
                <div class="rag-field">
                    <label>Документ</label>
                    <select name="filename" id="chatFile">
                        @forelse($files as $f)
                            <option value="{{ $f }}">{{ $f }}</option>
                        @empty
                            <option value="">Папка my_documents пуста</option>
                        @endforelse
                    </select>
                </div>
                <div class="rag-field">
                    <label>Твой вопрос</label>
                    <input type="text" name="question" id="chatQ" placeholder="Например: какая сумма в договоре? кто директор?">
                </div>
                <button type="submit" class="rag-btn" id="chatBtn">❓ Спросить ИИ</button>
            </div>
        </div>
    </form>

    <div class="rag-card" id="answerCard" style="display:none">
        <h3>🤖 Ответ ИИ</h3>
        <div class="rag-body">
            <div id="chatText"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('chatForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('chatBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ ИИ читает документ...';

    const fd = new FormData(this);
    try {
        const resp = await fetch('{{ route("contracts.chat-ask") }}', {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await resp.json();
        const card = document.getElementById('answerCard');
        card.style.display = 'block';
        document.getElementById('chatText').textContent =
            data.success ? data.answer : ('❌ ' + (data.error || 'Ошибка'));
        card.scrollIntoView({ behavior: 'smooth' });
    } catch (err) {
        alert('❌ Ошибка: ' + err.message);
    }
    btn.disabled = false;
    btn.innerHTML = '❓ Спросить ИИ';
});
</script>
@endsection