<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заявка #{{ $guestRequest->id }} — DocSign</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">
<div class="max-w-3xl mx-auto py-10 px-4">
    <a href="{{ route('guest.requests.index') }}" class="text-blue-600 hover:underline text-sm">← К списку</a>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded-lg mt-3">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border p-8 mt-4">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">{{ $guestRequest->doc_title }}</h1>
                <p class="text-slate-500 text-sm">Заявка #{{ $guestRequest->id }} · {{ $guestRequest->created_at->format('d.m.Y H:i') }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm {{ $guestRequest->statusColor() }}">
                {{ $guestRequest->statusLabel() }}
            </span>
        </div>

        <div class="grid md:grid-cols-2 gap-6 text-sm">
            <div>
                <h3 class="font-semibold mb-2 text-slate-700">Отправитель</h3>
                <p><b>Кто:</b> {{ $guestRequest->sender_who }}</p>
                <p><b>Email:</b> {{ $guestRequest->sender_email }}</p>
                <p><b>Телефон:</b> {{ $guestRequest->sender_phone }}</p>
            </div>
            <div>
                <h3 class="font-semibold mb-2 text-slate-700">Получатель</h3>
                <p><b>Имя:</b> {{ $guestRequest->recipient->name }}</p>
                <p><b>Email:</b> {{ $guestRequest->recipient->email }}</p>
                <p><b>Роль:</b> {{ $guestRequest->recipient->role ?? '—' }}</p>
            </div>
        </div>

        <hr class="my-6">

        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <p><b>Тип:</b> {{ $guestRequest->doc_type }}</p>
            <p><b>Номер:</b> {{ $guestRequest->doc_number ?: '—' }}</p>
            <p><b>Дедлайн:</b> {{ optional($guestRequest->deadline)->format('d.m.Y') ?? '—' }}</p>
            <p>
                <b>Файл:</b>
                @if ($guestRequest->doc_file)
                    <a href="{{ asset('storage/' . $guestRequest->doc_file) }}" target="_blank"
                       class="text-blue-600 hover:underline">скачать</a>
                @else — @endif
            </p>
        </div>

        @if ($guestRequest->doc_description)
            <div class="mt-4">
                <h3 class="font-semibold mb-1 text-slate-700 text-sm">Описание</h3>
                <p class="text-sm bg-slate-50 p-3 rounded-lg whitespace-pre-wrap">{{ $guestRequest->doc_description }}</p>
            </div>
        @endif

        @if ($guestRequest->admin_comment)
            <div class="mt-4">
                <h3 class="font-semibold mb-1 text-slate-700 text-sm">Комментарий директора</h3>
                <p class="text-sm bg-yellow-50 p-3 rounded-lg whitespace-pre-wrap">{{ $guestRequest->admin_comment }}</p>
            </div>
        @endif

        <div class="flex gap-3 mt-8">
            <a href="{{ route('guest.requests.edit', $guestRequest) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Редактировать</a>

            <form action="{{ route('guest.requests.destroy', $guestRequest) }}" method="POST"
                  onsubmit="return confirm('Удалить заявку?')">
                @csrf @method('DELETE')
                <button class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg">Удалить</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>