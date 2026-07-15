@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-8">

        {{-- Навигация --}}
        <div class="mb-8 flex items-center justify-between">
            <div>
                <a href="{{ route('logs.index') }}" class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-indigo-600 transition flex items-center gap-2 mb-2">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                    Назад к журналу
                </a>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Детализация события <span class="text-indigo-600">#{{ $log->id }}</span></h1>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('logs.edit', $log->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition shadow-lg shadow-amber-100">
                    ✏️ Редактировать
                </a>
                <form action="{{ route('logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Удалить этот лог безвозвратно?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-600 hover:text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition">
                        🗑 Удалить
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Основная информация --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-gray-50 bg-gray-50/50">
                        <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Описание действия</h3>
                    </div>
                    <div class="p-8 text-gray-700 leading-relaxed font-medium">
                        {{ $log->description ?? 'Описание отсутствует' }}
                    </div>
                </div>

                {{-- Дополнительные мета-данные --}}
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 grid grid-cols-2 gap-8">
                    <div>
                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-2">Создано в системе</label>
                        <span class="text-sm font-bold text-gray-900">{{ $log->created_at->format('d.m.Y — H:i:s') }}</span>
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-2">Последнее обновление</label>
                        <span class="text-sm font-bold text-gray-900">{{ $log->updated_at->format('d.m.Y — H:i:s') }}</span>
                    </div>
                </div>
            </div>

            {{-- Сайдбар с деталями --}}
            <div class="space-y-6">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 space-y-6">

                    {{-- Статус действия --}}
                    <div>
                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-3">Тип события</label>
                        @php
                            $actionColor = match($log->action) {
                                'created' => 'bg-green-500',
                                'deleted' => 'bg-red-500',
                                'updated' => 'bg-blue-500',
                                'signed'  => 'bg-indigo-600',
                                default   => 'bg-gray-900',
                            };
                        @endphp
                        <span class="{{ $actionColor }} text-white px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-md">
                        {{ $log->action }}
                    </span>
                    </div>

                    <hr class="border-gray-50">

                    {{-- Документ --}}
                    <div>
                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">Связанный документ</label>
                        @if($log->document)
                            <a href="{{ route('documents.show', $log->document_id) }}" class="text-sm font-bold text-indigo-600 hover:underline flex items-center gap-2">
                                📄 {{ $log->document->title }}
                            </a>
                        @else
                            <span class="text-sm font-bold text-gray-300 italic">Не привязан</span>
                        @endif
                    </div>

                    {{-- Инициатор --}}
                    <div>
                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-2">Инициатор</label>
                        <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-2xl">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-xs font-black text-indigo-600">
                                {{ mb_substr($log->user->name ?? 'S', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-xs font-black text-gray-900">{{ $log->user->name ?? 'Система' }}</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase">Пользователь</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
