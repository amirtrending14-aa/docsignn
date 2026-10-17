<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Гостевые заявки — DocSign</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">
<div class="max-w-6xl mx-auto py-10 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Гостевые заявки</h1>
        <a href="/" class="text-blue-600 hover:underline text-sm">← На главную</a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded-lg mb-4">{{ session('success') }}</div>
    @endif

    <form method="GET" class="flex gap-3 mb-6">
        <input name="search" value="{{ request('search') }}" placeholder="Поиск по заголовку, email, номеру…"
               class="flex-1 border rounded-lg px-3 py-2">
        <select name="status" class="border rounded-lg px-3 py-2">
            <option value="">Все статусы</option>
            <option value="new"         @selected(request('status')==='new')>Новые</option>
            <option value="in_progress" @selected(request('status')==='in_progress')>В работе</option>
            <option value="answered"    @selected(request('status')==='answered')>Отвечены</option>
            <option value="closed"      @selected(request('status')==='closed')>Закрыты</option>
        </select>
        <button class="bg-blue-600 text-white px-4 rounded-lg hover:bg-blue-700">Фильтр</button>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="p-3 text-left">№</th>
                    <th class="p-3 text-left">Заголовок</th>
                    <th class="p-3 text-left">От кого</th>
                    <th class="p-3 text-left">Кому</th>
                    <th class="p-3 text-left">Дедлайн</th>
                    <th class="p-3 text-left">Статус</th>
                    <th class="p-3 text-left">Дата</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $r)
                    <tr class="border-t hover:bg-slate-50">
                        <td class="p-3 font-mono">#{{ $r->id }}</td>
                        <td class="p-3">{{ $r->doc_title }}</td>
                        <td class="p-3">
                            <div>{{ $r->sender_who }}</div>
                            <div class="text-xs text-slate-500">{{ $r->sender_email }}</div>
                        </td>
                        <td class="p-3">{{ $r->recipient->name }}</td>
                        <td class="p-3">{{ optional($r->deadline)->format('d.m.Y') ?? '—' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $r->statusColor() }}">
                                {{ $r->statusLabel() }}
                            </span>
                        </td>
                        <td class="p-3 text-slate-500">{{ $r->created_at->format('d.m.Y H:i') }}</td>
                        <td class="p-3 whitespace-nowrap">
                            <a href="{{ route('guest.requests.show', $r) }}" class="text-blue-600 hover:underline mr-2">открыть</a>
                            <a href="{{ route('guest.requests.edit', $r) }}" class="text-slate-600 hover:underline">правка</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-8 text-center text-slate-500">Заявок пока нет</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>
</div>
</body>
</html>