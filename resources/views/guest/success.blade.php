<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заявка отправлена — DocSign</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md bg-white p-8 rounded-2xl shadow-sm border text-center">
        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">✓</div>
        <h1 class="text-2xl font-bold mb-2">Заявка отправлена</h1>
        <p class="text-slate-500 mb-4">Номер вашей заявки:</p>
        <div class="text-4xl font-bold text-blue-600 mb-6">#{{ $guestRequest->id }}</div>
        <p class="text-sm text-slate-500 mb-6">
            Получатель: <b>{{ $guestRequest->recipient->name }}</b><br>
            Мы уведомим его. Статус можно уточнить по номеру заявки.
        </p>
        <a href="/" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">На главную</a>
    </div>
</body>
</html>