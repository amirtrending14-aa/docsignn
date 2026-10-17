<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование #{{ $guestRequest->id }} — DocSign</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">
<div class="max-w-2xl mx-auto py-10 px-4">
    <a href="{{ route('guest.requests.show', $guestRequest) }}" class="text-blue-600 hover:underline text-sm">← К заявке</a>

    <h1 class="text-2xl font-bold mt-4 mb-6">Редактирование заявки #{{ $guestRequest->id }}</h1>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg mb-4 text-sm">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('guest.requests.update', $guestRequest) }}" method="POST"
          class="bg-white p-8 rounded-2xl shadow-sm border space-y-5">
        @csrf @method('PUT')

        <label class="block">
            <span class="text-sm text-slate-600">Статус</span>
            <select name="status" class="mt-1 w-full border rounded-lg px-3 py-2">
                <option value="new"         @selected($guestRequest->status==='new')>Новая</option>
                <option value="in_progress" @selected($guestRequest->status==='in_progress')>В работе</option>
                <option value="answered"    @selected($guestRequest->status==='answered')>Отвечена</option>
                <option value="closed"      @selected($guestRequest->status==='closed')>Закрыта</option>
            </select>
        </label>

        <label class="block">
            <span class="text-sm text-slate-600">Комментарий директора</span>
            <textarea name="admin_comment" rows="5"
                      class="mt-1 w-full border rounded-lg px-3 py-2">{{ old('admin_comment', $guestRequest->admin_comment) }}</textarea>
        </label>

        <div class="flex gap-3 pt-2">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">Сохранить</button>
            <a href="{{ route('guest.requests.show', $guestRequest) }}"
               class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2 rounded-lg">Отмена</a>
        </div>
    </form>
</div>
</body>
</html>