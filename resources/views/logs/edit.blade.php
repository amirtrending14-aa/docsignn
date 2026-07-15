@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-8">

        {{-- Навигация и заголовок --}}
        <div class="mb-8">
            <a href="{{ route('logs.index') }}" class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-indigo-600 transition flex items-center gap-2 mb-2">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                Вернуться к логам
            </a>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Редактировать запись <span class="text-indigo-600">#{{ $log->id }}</span></h1>
        </div>

        <div class="max-w-3xl">
            {{-- Ошибки валидации --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl">
                    <p class="text-xs font-black uppercase tracking-widest mb-2">Обнаружены ошибки:</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Основная форма --}}
            <form action="{{ route('logs.update', $log->id) }}" method="POST" class="bg-white p-8 rounded-3xl border border-gray-200 shadow-sm space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Выбор документа --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Документ</label>
                        <select name="document_id" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-0 transition-all font-bold text-gray-700 text-sm outline-none cursor-pointer">
                            @foreach($documents as $document)
                                <option value="{{ $document->id }}" {{ $log->document_id == $document->id ? 'selected' : '' }}>
                                    {{ $document->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Выбор пользователя --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Пользователь</label>
                        <select name="user_id" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-0 transition-all font-bold text-gray-700 text-sm outline-none cursor-pointer">
                            <option value="">⚙️ Система</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $log->user_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Действие --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Тип действия</label>
                    <select name="action" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-0 transition-all font-bold text-gray-700 text-sm outline-none cursor-pointer">
                        <option value="created" {{ $log->action == 'created' ? 'selected' : '' }}>🟢 Создание</option>
                        <option value="updated" {{ $log->action == 'updated' ? 'selected' : '' }}>🔵 Обновление</option>
                        <option value="deleted" {{ $log->action == 'deleted' ? 'selected' : '' }}>🔴 Удаление</option>
                        <option value="signed" {{ $log->action == 'signed' ? 'selected' : '' }}>🖋️ Подписание</option>
                        <option value="status_changed" {{ $log->action == 'status_changed' ? 'selected' : '' }}>⚙️ Смена статуса</option>
                    </select>
                </div>

                {{-- Описание --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Описание события</label>
                    <textarea name="description" rows="4" class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-0 transition-all font-bold text-gray-700 text-sm outline-none resize-none">{{ $log->description }}</textarea>
                </div>

                {{-- Футер формы --}}
                <div class="pt-4 flex items-center gap-4">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] transition-all shadow-xl shadow-indigo-100">
                        Обновить данные
                    </button>
                    <a href="{{ route('logs.index') }}" class="px-8 py-4 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-red-500 transition">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
