<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отправить документ без регистрации — DocSign</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">

<div class="max-w-3xl mx-auto py-10 px-4">
    <a href="/" class="text-blue-600 hover:underline text-sm">← На главную</a>

    <h1 class="text-3xl font-bold mt-4 mb-2">Отправить документ без регистрации</h1>
    <p class="text-slate-500 mb-8">Заполните анкету — заявка попадёт напрямую получателю.</p>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('guest.store') }}" method="POST" enctype="multipart/form-data"
          class="space-y-8 bg-white p-8 rounded-2xl shadow-sm border">
        @csrf

        {{-- БЛОК 1: ОТПРАВИТЕЛЬ --}}
        <section>
            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-sm flex items-center justify-center">1</span>
                Кто отправляет
            </h2>
            <div class="grid md:grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-sm text-slate-600">Кто вы (свободно)</span>
                    <input name="sender_who" required value="{{ old('sender_who') }}"
                           placeholder="Родитель ученика 7Б, Иванов А."
                           class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </label>
                <label class="block">
                    <span class="text-sm text-slate-600">Email</span>
                    <input name="sender_email" type="email" required value="{{ old('sender_email') }}"
                           class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm text-slate-600">Телефон</span>
                    <input name="sender_phone" required value="{{ old('sender_phone') }}"
                           placeholder="+7 (___) ___-__-__"
                           class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </label>
            </div>
        </section>

        {{-- БЛОК 2: ДОКУМЕНТ --}}
        <section>
            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-sm flex items-center justify-center">2</span>
                Документ
            </h2>
            <div class="grid md:grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-sm text-slate-600">Тип документа</span>
                    <input name="doc_type" required value="{{ old('doc_type') }}"
                           placeholder="Заявление, справка, договор…"
                           class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </label>
                <label class="block">
                    <span class="text-sm text-slate-600">Номер документа</span>
                    <input name="doc_number" value="{{ old('doc_number') }}"
                           class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm text-slate-600">Заголовок</span>
                    <input name="doc_title" required value="{{ old('doc_title') }}"
                           class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm text-slate-600">Описание</span>
                    <textarea name="doc_description" rows="3"
                              class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">{{ old('doc_description') }}</textarea>
                </label>
                <label class="block">
                    <span class="text-sm text-slate-600">Файл (PDF, DOC, до 10 МБ)</span>
                    <input name="doc_file" type="file"
                           class="mt-1 w-full border rounded-lg px-3 py-2">
                </label>
                <label class="block">
                    <span class="text-sm text-slate-600">Дедлайн</span>
                    <input name="deadline" type="date" value="{{ old('deadline') }}"
                           class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </label>
            </div>
        </section>

        {{-- БЛОК 3: ПОЛУЧАТЕЛЬ С АВТОКОМПЛИТОМ --}}
        <section>
            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-sm flex items-center justify-center">3</span>
                Кому отправляем
            </h2>

            <div class="relative">
                <input id="recipient-search" type="text" autocomplete="off"
                       placeholder="Начните вводить имя, email или телефон получателя…"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                <div id="recipient-suggestions"
                     class="absolute z-10 mt-1 w-full bg-white border rounded-lg shadow-lg hidden"></div>
            </div>

            <div id="recipient-selected" class="hidden mt-3 p-3 bg-green-50 border border-green-200 rounded-lg flex items-center justify-between">
                <div>
                    <div class="font-medium text-green-800" id="rs-name"></div>
                    <div class="text-sm text-green-700" id="rs-meta"></div>
                </div>
                <button type="button" id="rs-clear" class="text-green-700 hover:underline text-sm">изменить</button>
            </div>

            <input type="hidden" name="recipient_user_id" id="recipient_user_id" required>
            <p id="recipient-error" class="text-red-600 text-sm mt-2 hidden">
                Выберите получателя из списка — только зарегистрированные пользователи могут получать документы.
            </p>
        </section>

        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
            Отправить заявку
        </button>
    </form>
</div>

<script>
const input = document.getElementById('recipient-search');
const box = document.getElementById('recipient-suggestions');
const selected = document.getElementById('recipient-selected');
const hiddenId = document.getElementById('recipient_user_id');
const errBox = document.getElementById('recipient-error');
let timer;

input.addEventListener('input', () => {
    clearTimeout(timer);
    const q = input.value.trim();
    if (q.length < 2) { box.classList.add('hidden'); return; }

    timer = setTimeout(async () => {
        const res = await fetch(`/api/users/search?q=${encodeURIComponent(q)}`);
        const users = await res.json();
        if (!users.length) {
            box.innerHTML = `<div class="p-3 text-sm text-slate-500">Никого не найдено</div>`;
        } else {
            box.innerHTML = users.map(u => `
                <div class="p-3 hover:bg-blue-50 cursor-pointer border-b last:border-0"
                     data-id="${u.id}" data-name="${u.name}"
                     data-email="${u.email || ''}" data-phone="${u.phone || ''}" data-role="${u.role || ''}">
                    <div class="font-medium">${u.name}</div>
                    <div class="text-xs text-slate-500">
                        ${u.email || ''} ${u.phone ? '· ' + u.phone : ''} ${u.role ? '· ' + u.role : ''}
                    </div>
                </div>
            `).join('');
        }
        box.classList.remove('hidden');
    }, 250);
});

box.addEventListener('click', (e) => {
    const item = e.target.closest('[data-id]');
    if (!item) return;
    hiddenId.value = item.dataset.id;
    document.getElementById('rs-name').textContent = item.dataset.name;
    document.getElementById('rs-meta').textContent =
        [item.dataset.email, item.dataset.phone, item.dataset.role].filter(Boolean).join(' · ');
    selected.classList.remove('hidden');
    input.classList.add('hidden');
    box.classList.add('hidden');
    errBox.classList.add('hidden');
});

document.getElementById('rs-clear').addEventListener('click', () => {
    hiddenId.value = '';
    selected.classList.add('hidden');
    input.classList.remove('hidden');
    input.value = '';
    input.focus();
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('#recipient-search') && !e.target.closest('#recipient-suggestions')) {
        box.classList.add('hidden');
    }
});

// Валидация перед submit
document.querySelector('form').addEventListener('submit', (e) => {
    if (!hiddenId.value) {
        e.preventDefault();
        errBox.classList.remove('hidden');
        document.getElementById('recipient-search').focus();
    }
});
</script>
</body>
</html>