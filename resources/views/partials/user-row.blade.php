<div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">

    {{-- Блок Аватар + Имя + Email --}}
    <div class="flex items-center gap-4 min-w-0">
        {{-- Аватарка --}}
        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center font-black text-white text-[12px] shadow-md">
            {{ Str::upper(Str::substr($user->name, 0, 1)) }}
        </div>

        {{-- Текст --}}
        <div class="min-w-0 flex flex-col">
            {{-- ИМЯ --}}
            <div class="font-black text-black dark:text-white text-[13px] truncate leading-tight">
                {{ $user->name }}
            </div>
            {{-- EMAIL (теперь в стиле имени) --}}

            <div class="font-normal text-black dark:text-white text-[11px] truncate leading-tight tracking-wide">
                {{ $user->email }}
            </div>
        </div>
    </div>

    {{-- Кнопка Профиль --}}
    <div class="flex-shrink-0 ml-8">
        <a href="{{ route('users.show', $user->id) }}"
           class="inline-block text-[10px] font-black uppercase text-black dark:text-white hover:text-blue-600 transition-colors">
            Профиль
        </a>
    </div>
</div>
