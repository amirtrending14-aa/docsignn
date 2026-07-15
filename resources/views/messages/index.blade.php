@extends('layouts.admin')

@section('content')
<div class="h-screen flex bg-slate-50 dark:bg-black transition-all duration-300 overflow-hidden font-sans text-sm">
    {{-- ЛЕВАЯ ПАНЕЛЬ (СПИСОК ЧАТОВ) --}}
    <div class="w-72 md:w-80 border-r border-slate-200 dark:border-zinc-800 flex flex-col bg-white dark:bg-zinc-950 flex-shrink-0 min-h-0 shadow-sm z-10">

        {{-- Шапка списка чатов --}}
        <div class="flex-shrink-0 p-2.5 border-b border-slate-200 dark:border-zinc-800 flex items-center gap-2 bg-white dark:bg-zinc-950">
            <div class="flex-1 relative">
                <input type="text" id="chat-search" placeholder="Поиск по имени или email..."
                       class="w-full bg-slate-100 dark:bg-zinc-900 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/30 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-zinc-500 transition-all border border-transparent focus:border-blue-500/20">
                <div id="search-results" class="absolute top-10 left-0 right-0 bg-white dark:bg-zinc-800 shadow-xl rounded-lg z-50 hidden border border-slate-200 dark:border-zinc-700 overflow-hidden max-h-60 overflow-y-auto"></div>
            </div>
            <button onclick="openBlockedUsersModal()" class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-400 transition-colors relative" title="Заблокированные пользователи">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                <span id="blocked-count" class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">0</span>
            </button>
        </div>

        {{-- Список чатов --}}
        <div id="chat-list" class="flex-1 min-h-0 overflow-y-auto custom-scrollbar">
            @foreach($users as $user)
            @php
            $sent = $user->sentMessages ?? collect();
            $received = $user->receivedMessages ?? collect();
            $lastMsg = $sent->merge($received)->sortByDesc('created_at')->first();
            $unreadCount = $received->where('is_read', 0)->count();
            $isActive = (isset($receiver) && $receiver->id == $user->id);
            $isOnline = $user->last_seen_at && $user->last_seen_at->diffInMinutes(now()) < 5;

            // Проверка блокировки в обе стороны
            $isBlocked = ($user->blockedByAuthUser ?? false) || ($user->blocksAuthUser ?? false) || ($user->is_blocked ?? false);

            $lastSeenText = '';
            if ($isBlocked) {
            $lastSeenText = 'заблокирован';
            } elseif ($isOnline) {
            $lastSeenText = 'в сети';
            } elseif ($user->last_seen_at) {
            if ($user->last_seen_at->isToday()) {
            $lastSeenText = 'был(а) сегодня в ' . $user->last_seen_at->format('H:i');
            } elseif ($user->last_seen_at->isYesterday()) {
            $lastSeenText = 'был(а) вчера';
            } else {
            $lastSeenText = 'был(а) ' . $user->last_seen_at->format('d.m.Y');
            }
            } else {
            $lastSeenText = 'был(а) давно';
            }
            @endphp

            <a href="{{ $isBlocked ? '#' : url('/messages/' . $user->id) }}"
               data-user-id="{{ $user->id }}"
               data-name="{{ strtolower($user->name) }}"
               data-email="{{ strtolower($user->email ?? '') }}"
               class="chat-item group flex items-center p-2.5 transition-all duration-200 {{ $isActive ? 'bg-blue-50 dark:bg-blue-900/20 border-l-2 border-blue-500' : 'hover:bg-slate-50 dark:hover:bg-zinc-900 border-l-2 border-transparent' }} {{ $isBlocked ? 'opacity-60' : '' }}"
               onclick="{{ $isBlocked ? 'event.preventDefault(); showToast(\'Пользователь заблокирован. Сначала разблокируйте его.\', \'error\'); return false;' : 'return clearUnread(event, ' . $user->id . ');' }}">

                <div class="relative flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 via-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                        {{ Str::substr($user->name, 0, 1) }}
                    </div>
                    @if($isBlocked)
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-red-500 border-2 border-white dark:border-zinc-950 rounded-full flex items-center justify-center">
                        <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </div>
                    @elseif($isOnline)
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-zinc-950 rounded-full"></div>
                    @else
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-slate-400 dark:bg-zinc-600 border-2 border-white dark:border-zinc-950 rounded-full"></div>
                    @endif
                </div>

                <div class="flex-1 min-w-0 ml-2.5">
                    <div class="flex justify-between items-baseline mb-0.5">
                        <div class="font-semibold {{ $isActive ? 'text-blue-600 dark:text-blue-400' : 'text-slate-900 dark:text-white' }} truncate pr-2 text-[13px]">
                            {{ $user->name }}
                            @if($isBlocked)
                            <span class="text-[9px] text-red-500 font-normal ml-1">(заблокирован)</span>
                            @endif
                        </div>
                        @if($lastMsg && !$isBlocked)
                        <div class="text-[10px] {{ $unreadCount > 0 && !$isActive ? 'text-blue-500 font-semibold' : 'text-slate-400 dark:text-zinc-500' }} flex-shrink-0">
                            @php
                            if ($lastMsg->created_at->isToday()) {
                            echo $lastMsg->created_at->format('H:i');
                            } elseif ($lastMsg->created_at->isYesterday()) {
                            echo 'Вчера';
                            } else {
                            echo $lastMsg->created_at->format('d.m.y');
                            }
                            @endphp
                        </div>
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="text-xs truncate pr-2 min-w-0 {{ $isActive ? 'text-slate-600 dark:text-zinc-300' : 'text-slate-500 dark:text-zinc-400' }}">
                            @if($isBlocked)
                            <span class="italic text-red-400 text-[11px]">Пользователь заблокирован</span>
                            @elseif($lastMsg)
                            @if($lastMsg->sender_id === auth()->id())
                            <span class="{{ $isActive ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }} font-medium">Вы: </span>
                            @endif
                            {{ Str::limit($lastMsg->body, 35) }}
                            @else
                            <span class="italic text-slate-400 dark:text-zinc-600 text-[11px]">{{ $lastSeenText }}</span>
                            @endif
                        </div>
                        @if($unreadCount > 0 && !$isBlocked)
                        <span class="unread-badge-{{ $user->id }} flex-shrink-0 bg-blue-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center shadow-sm {{ $isActive ? 'hidden' : '' }}">
                            {{ $unreadCount }}
                        </span>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ПРАВАЯ ПАНЕЛЬ (ОКНО ЧАТА) --}}
    <div class="flex-1 flex flex-col min-w-0 h-full bg-slate-50 dark:bg-black relative overflow-hidden">
        @if(isset($receiver) && $receiver)
        @php
        $isOnline = $receiver->last_seen_at && $receiver->last_seen_at->diffInMinutes(now()) < 5;
        $isBlocked = ($receiver->blockedByAuthUser ?? false) || ($receiver->blocksAuthUser ?? false) || ($receiver->is_blocked ?? false);

        if ($isBlocked) {
        $lastSeenText = 'заблокирован';
        } elseif ($isOnline) {
        $lastSeenText = 'в сети';
        } elseif ($receiver->last_seen_at) {
        if ($receiver->last_seen_at->isToday()) {
        $lastSeenText = 'был(а) сегодня в ' . $receiver->last_seen_at->format('H:i');
        } elseif ($receiver->last_seen_at->isYesterday()) {
        $lastSeenText = 'был(а) вчера';
        } else {
        $lastSeenText = 'был(а) ' . $receiver->last_seen_at->format('d.m.Y');
        }
        } else {
        $lastSeenText = 'был(а) давно';
        }
        @endphp

        {{-- ШАПКА ЧАТА --}}
        <div class="flex-shrink-0 h-14 px-4 border-b border-slate-200 dark:border-zinc-800 bg-white/80 dark:bg-black/80 backdrop-blur-md flex items-center justify-between shadow-sm z-20">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 via-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs shadow-sm">
                        {{ Str::substr($receiver->name, 0, 1) }}
                    </div>
                    @if($isBlocked)
                    <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-red-500 border-2 border-white dark:border-black rounded-full"></div>
                    @elseif($isOnline)
                    <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-white dark:border-black rounded-full"></div>
                    @else
                    <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-slate-400 dark:bg-zinc-600 border-2 border-white dark:border-black rounded-full"></div>
                    @endif
                </div>
                <div class="flex flex-col min-w-0">
                    <h3 class="font-bold text-slate-900 dark:text-white text-[14px] leading-tight truncate">{{ $receiver->name }}</h3>
                    <span class="text-[11px] {{ $isBlocked ? 'text-red-500 font-medium' : ($isOnline ? 'text-emerald-500 font-medium' : 'text-slate-500 dark:text-zinc-400') }} flex items-center gap-1">
                        @if($isBlocked)
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        @elseif($isOnline)
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                        @endif
                        {{ $lastSeenText }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-1 relative">
                <button class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 dark:text-zinc-400 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </button>
                <button class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 dark:text-zinc-400 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                </button>

                {{-- Меню действий с контактом --}}
                <div class="relative">
                    <button onclick="toggleContactMenu()" class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 dark:text-zinc-400 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                    </button>
                    <div id="contact-menu" class="hidden absolute right-0 top-10 w-48 bg-white dark:bg-zinc-900 rounded-lg shadow-xl border border-slate-200 dark:border-zinc-700 py-1 z-50">
                        @if($isBlocked)
                        <button onclick="unblockUser({{ $receiver->id }})" class="w-full px-4 py-2 text-left text-xs text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 flex items-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                            Разблокировать
                        </button>
                        @else
                        <button onclick="blockUser({{ $receiver->id }})" class="w-full px-4 py-2 text-left text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 flex items-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            Заблокировать
                        </button>
                        @endif
                        <button onclick="clearMessages({{ $receiver->id }})" class="w-full px-4 py-2 text-left text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 flex items-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Очистить чат
                        </button>
                        <button onclick="deleteChat({{ $receiver->id }})" class="w-full px-4 py-2 text-left text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Удалить чат
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ОБЛАСТЬ СООБЩЕНИЙ --}}
        <div id="chat-container" class="flex-1 min-h-0 overflow-y-auto custom-scrollbar relative bg-slate-50 dark:bg-black">
            @if($isBlocked)
            <div class="flex items-center justify-center h-full text-center p-8">
                <div class="max-w-md">
                    <div class="w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Пользователь заблокирован</h3>
                    <p class="text-sm text-slate-500 dark:text-zinc-400 mb-6 leading-relaxed">
                        Вы заблокировали {{ $receiver->name }}. Обмен сообщениями невозможен.<br>
                        Чтобы продолжить общение, сначала разблокируйте пользователя.
                    </p>
                    <button onclick="unblockUser({{ $receiver->id }})" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-sm rounded-xl transition-all shadow-lg hover:shadow-emerald-500/30 flex items-center gap-2 mx-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                        Разблокировать пользователя
                    </button>
                </div>
            </div>
            @else
            <div class="max-w-3xl mx-auto px-3 md:px-6 py-4 space-y-1.5">
                @isset($messages)
                @php $lastDate = null; @endphp
                @foreach($messages as $msg)
                @if($lastDate !== $msg->created_at->format('d.m.Y'))
                @php $lastDate = $msg->created_at->format('d.m.Y'); @endphp
                <div class="flex justify-center my-3">
                    <span class="bg-white/90 dark:bg-zinc-900/90 text-slate-600 dark:text-zinc-300 text-[11px] font-medium px-3 py-1 rounded-full shadow-sm border border-slate-200 dark:border-zinc-800">
                        {{ $msg->created_at->locale('ru')->isoFormat('D MMMM') }}
                    </span>
                </div>
                @endif

                @if($msg->sender_id === auth()->id())
                <div class="flex justify-end mb-1.5 animate-message-in message-row"
                     data-message-id="{{ $msg->id }}"
                     data-sender-id="{{ $msg->sender_id }}"
                     data-message-text="{{ htmlspecialchars($msg->body) }}"
                     oncontextmenu="showContextMenu(event, {{ $msg->id }}, 'sent', {{ $msg->sender_id }})">
                    <div class="bg-blue-500 text-white px-3 py-2 rounded-2xl rounded-tr-sm max-w-[80%] md:max-w-[65%] shadow-sm relative break-words cursor-pointer hover:shadow-md transition-shadow message-bubble-sent">
                        @if($msg->attachment)
                        @php $ext = strtolower(pathinfo($msg->attachment, PATHINFO_EXTENSION)); @endphp
                        @if(in_array($ext, ['jpg','jpeg','png','gif','webp']))
                        <img src="{{ asset('storage/' . $msg->attachment) }}" class="max-w-full rounded-lg mb-1.5 border border-white/20" alt="attachment">
                        @else
                        <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" class="flex items-center gap-2 bg-blue-600/50 px-2.5 py-1.5 rounded-lg text-[11px] mb-1.5 hover:bg-blue-600/70 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            {{ basename($msg->attachment) }}
                        </a>
                        @endif
                        @endif
                        <p class="text-[13px] leading-snug whitespace-pre-wrap message-text">{{ $msg->body }}</p>
                        <div class="float-right ml-2 mt-1 flex items-center gap-1 select-none">
                            <span class="text-[10px] text-white/80">
                                @php
                                    if ($msg->created_at->isToday()) echo $msg->created_at->format('H:i');
                                    elseif ($msg->created_at->isYesterday()) echo 'Вчера ' . $msg->created_at->format('H:i');
                                    else echo $msg->created_at->format('d.m H:i');
                                @endphp
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white/90 -ml-0.5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18 7l-1.41-1.41-6.34 6.34 1.41 1.41L18 7zm4.24-1.41L11.66 16.17 7.48 12l-1.41 1.41L11.66 19l12-12-1.42-1.41zM.41 13.41L6 19l1.41-1.41L1.83 12 .41 13.41z"/>
                            </svg>
                        </div>

                        {{-- INLINE РЕДАКТИРОВАНИЕ (внутри bubble, внизу) --}}
                        <div class="inline-edit-panel hidden mt-2 pt-2 border-t border-white/20">
                            <div class="flex items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <svg class="w-3.5 h-3.5 text-white/80 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span class="text-[11px] font-semibold text-white/90">Редактирование</span>
                                    </div>
                                    <textarea class="inline-edit-textarea w-full bg-blue-600/40 border border-white/20 rounded-lg px-2 py-1.5 text-[12px] text-white placeholder-white/50 focus:outline-none focus:border-white/40 resize-none" rows="2" maxlength="2000">{{ $msg->body }}</textarea>
                                </div>
                                <div class="flex flex-col gap-1 flex-shrink-0">
                                    <button type="button" onclick="cancelEdit({{ $msg->id }})" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors" title="Отмена">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <button type="button" onclick="saveInlineEdit({{ $msg->id }})" class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors" title="Сохранить">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="flex items-end gap-2 mb-1.5 animate-message-in message-row"
                     data-message-id="{{ $msg->id }}"
                     data-sender-id="{{ $msg->sender_id }}"
                     data-message-text="{{ htmlspecialchars($msg->body) }}"
                     oncontextmenu="showContextMenu(event, {{ $msg->id }}, 'received', {{ $msg->sender_id }})">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0 mb-0.5 shadow-sm">
                        {{ Str::substr($receiver->name, 0, 1) }}
                    </div>
                    <div class="bg-white dark:bg-zinc-800 text-slate-900 dark:text-white px-3 py-2 rounded-2xl rounded-tl-sm max-w-[80%] md:max-w-[65%] shadow-sm border border-slate-200 dark:border-zinc-700 break-words cursor-pointer hover:shadow-md transition-shadow message-bubble-received">
                        @if($msg->attachment)
                        @php $ext = strtolower(pathinfo($msg->attachment, PATHINFO_EXTENSION)); @endphp
                        @if(in_array($ext, ['jpg','jpeg','png','gif','webp']))
                        <img src="{{ asset('storage/' . $msg->attachment) }}" class="max-w-full rounded-lg mb-1.5" alt="attachment">
                        @else
                        <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" class="flex items-center gap-2 bg-slate-100 dark:bg-zinc-700/50 px-2.5 py-1.5 rounded-lg text-[11px] mb-1.5 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            {{ basename($msg->attachment) }}
                        </a>
                        @endif
                        @endif
                        <p class="text-[13px] leading-snug whitespace-pre-wrap message-text">{{ $msg->body }}</p>
                        <div class="float-right ml-2 mt-1 select-none">
                            <span class="text-[10px] text-slate-500 dark:text-zinc-400">
                                @php
                                    if ($msg->created_at->isToday()) echo $msg->created_at->format('H:i');
                                    elseif ($msg->created_at->isYesterday()) echo 'Вчера ' . $msg->created_at->format('H:i');
                                    else echo $msg->created_at->format('d.m H:i');
                                @endphp
                            </span>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
                @endisset
            </div>
            @endif
        </div>

        {{-- Контекстное меню сообщений (маленькое, fixed) --}}
        <div id="context-menu" class="hidden fixed bg-white dark:bg-zinc-900 rounded-lg shadow-xl border border-slate-200 dark:border-zinc-700 py-1 z-[9999] min-w-[180px]">
            <button id="ctx-edit" onclick="startInlineEdit()" class="ctx-item w-full px-3 py-2 text-left text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 flex items-center gap-2 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Редактировать
            </button>
            <button onclick="copyMessage()" class="ctx-item w-full px-3 py-2 text-left text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 flex items-center gap-2 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Копировать
            </button>
            <button onclick="forwardMessage()" class="ctx-item w-full px-3 py-2 text-left text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 flex items-center gap-2 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                Переслать
            </button>
            <div class="border-t border-slate-200 dark:border-zinc-700 my-1"></div>
            <button onclick="deleteMessage()" class="ctx-item w-full px-3 py-2 text-left text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Удалить у себя
            </button>
            <button id="ctx-delete-all" onclick="deleteMessageForAll()" class="ctx-item w-full px-3 py-2 text-left text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Удалить для всех
            </button>
        </div>

        {{-- Кнопка "прокрутить вниз" --}}
        <button id="scroll-down-btn" class="hidden absolute bottom-20 right-6 w-9 h-9 bg-white dark:bg-zinc-800 rounded-full shadow-lg hover:shadow-xl transition-all items-center justify-center text-slate-500 dark:text-zinc-400 z-30 border border-slate-200 dark:border-zinc-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
        </button>

        {{-- ПОЛЕ ВВОДА --}}
        @if(!$isBlocked)
        <div class="flex-shrink-0 bg-white/80 dark:bg-black/80 backdrop-blur-md border-t border-slate-200 dark:border-zinc-800 z-20 shadow-sm">
            <form id="message-form" action="{{ route('messages.store', $receiver->id) }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-2 px-3 py-2.5 max-w-3xl mx-auto">
                @csrf
                <input type="hidden" name="receiver_id" value="{{ $receiver->id }}">

                <label class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 dark:text-zinc-400 transition-colors cursor-pointer flex-shrink-0 hover:text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                    <input type="file" id="file-input" name="attachment" class="hidden" accept="image/*,video/*,application/pdf,.doc,.docx,.txt">
                </label>

                <div id="file-preview" class="hidden flex-shrink-0 bg-slate-100 dark:bg-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-white flex items-center gap-2 border border-slate-200 dark:border-zinc-700">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span id="file-name" class="max-w-[100px] truncate"></span>
                    <button type="button" onclick="removeFile()" class="text-red-500 hover:text-red-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <button type="button" onclick="toggleStickerPanel()" class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 dark:text-zinc-400 transition-colors flex-shrink-0 hover:text-yellow-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </button>

                <div class="flex-1 min-w-0 relative">
                    <input type="text" id="message-input" name="body" required autofocus autocomplete="off"
                           class="w-full bg-slate-100 dark:bg-zinc-900 border-none rounded-xl px-4 py-2.5 text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500/30 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-zinc-500 transition-all border border-transparent focus:border-blue-500/20"
                           placeholder="Сообщение"
                           maxlength="2000">
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-600 active:scale-95 text-white p-2.5 rounded-full transition-all flex items-center justify-center shadow-sm flex-shrink-0 hover:scale-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform translate-x-0.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" /></svg>
                </button>
            </form>
        </div>

        {{-- Панель стикеров --}}
        <div id="sticker-panel" class="hidden absolute bottom-16 left-0 right-0 bg-white dark:bg-zinc-900 border-t border-slate-200 dark:border-zinc-800 shadow-xl z-30 max-h-48 overflow-y-auto">
            <div class="p-2 grid grid-cols-8 gap-1">
                @php
                $stickers = ['😀', '😂', '😍', '🥰', '😊', '😢', '😭', '👍', '👎', '❤️', '🔥', '💯', '😎', '😅', '😢', '👏', '🙌', '😱', '🤗', '🤔', '🌟', '✨', '💡', '🙏', '🥳', '😴', '🤩', '😡'];
                @endphp
                @foreach($stickers as $sticker)
                <button type="button" onclick="insertSticker('{{ $sticker }}')" class="text-xl hover:scale-110 transition-transform p-1 rounded hover:bg-slate-100 dark:hover:bg-zinc-800 flex items-center justify-center">
                    {{ $sticker }}
                </button>
                @endforeach
            </div>
        </div>
        @endif
        @else
        {{-- Пустое состояние --}}
        <div class="flex-1 flex flex-col items-center justify-center text-slate-400 dark:text-zinc-500 bg-slate-50 dark:bg-black">
            <div class="w-24 h-24 bg-slate-200 dark:bg-zinc-900 rounded-full flex items-center justify-center mb-4 shadow-sm border border-slate-300 dark:border-zinc-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <p class="text-lg font-light text-slate-500 dark:text-zinc-400 mb-1">Выберите чат для начала общения</p>
            <p class="text-xs text-slate-400 dark:text-zinc-500">Отправьте сообщение или начните новый диалог</p>
        </div>
        @endif
    </div>
</div>

{{-- Модальное окно заблокированных пользователей --}}
<div id="blocked-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-2xl max-w-md w-full p-5 max-h-[80vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Заблокированные пользователи</h3>
            <button onclick="closeBlockedModal()" class="text-slate-400 dark:text-zinc-400 hover:text-slate-600 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="blocked-users-list" class="flex-1 overflow-y-auto space-y-2">
            <div class="text-center text-sm text-slate-500 dark:text-zinc-400 py-8">Загрузка...</div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, #3b82f6, #2563eb);
        border-radius: 20px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to bottom, #2563eb, #1d4ed8);
    }
    #chat-container { scroll-behavior: smooth; }

    @keyframes message-in {
        from { opacity: 0; transform: translateY(8px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .animate-message-in { animation: message-in 0.25s cubic-bezier(0.4, 0, 0.2, 1); }

    @keyframes fade-out {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.8); }
    }
    .fade-out { animation: fade-out 0.25s forwards; }

    @keyframes slide-up {
        from { opacity: 0; transform: translate(-50%, 15px); }
        to { opacity: 1; transform: translate(-50%, 0); }
    }
    .toast-in { animation: slide-up 0.25s ease-out; }

    /* Inline edit panel animation */
    .inline-edit-panel {
        animation: editSlideIn 0.2s ease-out;
    }
    @keyframes editSlideIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .inline-edit-textarea {
        font-family: inherit;
    }
</style>

<script>
    let selectedMessageId = null;
    let selectedMessageType = null;
    let selectedSenderId = null;
    let activeEditMessageId = null;
    const currentUserId = {{ auth()->id() }};

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ||
               document.querySelector('input[name="_token"]')?.value ||
               '{{ csrf_token() }}';
    }

    // Поиск по имени и email через AJAX
    let searchTimeout;
    document.getElementById('chat-search')?.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        const searchResults = document.getElementById('search-results');

        clearTimeout(searchTimeout);

        if (query.length < 2) {
            searchResults.classList.add('hidden');
            document.querySelectorAll('.chat-item').forEach(item => {
                item.style.display = 'flex';
            });
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch('/messages/search?q=' + encodeURIComponent(query), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            })
            .then(response => response.json())
            .then(users => {
                searchResults.innerHTML = '';

                if (users.length > 0) {
                    users.forEach(user => {
                        const div = document.createElement('a');
                        div.href = '/messages/' + user.id;
                        div.className = 'flex items-center p-2.5 hover:bg-slate-100 dark:hover:bg-zinc-700 transition border-b border-slate-100 dark:border-zinc-700 last:border-0';
                        div.innerHTML = `
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold mr-2.5 flex-shrink-0">
                                ${user.name.charAt(0)}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-semibold text-slate-900 dark:text-white truncate">${user.name}</div>
                                <div class="text-[10px] text-slate-500 dark:text-zinc-400 truncate">${user.email || ''}</div>
                            </div>
                        `;
                        searchResults.appendChild(div);
                    });
                    searchResults.classList.remove('hidden');
                } else {
                    searchResults.innerHTML = '<div class="p-3 text-slate-400 text-xs text-center">Не найдено</div>';
                    searchResults.classList.remove('hidden');
                }
            })
            .catch(err => {
                console.error('Ошибка поиска:', err);
                document.querySelectorAll('.chat-item').forEach(item => {
                    const name = item.dataset.name || '';
                    const email = item.dataset.email || '';
                    item.style.display = (name.includes(query) || email.includes(query)) ? 'flex' : 'none';
                });
            });
        }, 300);
    });

    function clearUnread(event, userId) {
        const badge = document.querySelector(`.unread-badge-${userId}`);
        if (badge) {
            badge.classList.add('fade-out');
            setTimeout(() => badge.remove(), 250);
        }

        fetch(`/messages/${userId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        }).catch(err => console.log('Mark read error:', err));

        return true;
    }

    function toggleContactMenu() {
        document.getElementById('contact-menu').classList.toggle('hidden');
    }

    function blockUser(userId) {
        if (!confirm('Заблокировать этого пользователя? Вы не сможете отправлять ему сообщения, и он не сможет отправлять их вам.')) return;

        fetch(`/messages/${userId}/block`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Пользователь заблокирован');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Ошибка блокировки', 'error');
            }
        })
        .catch(err => {
            showToast('Ошибка соединения', 'error');
        });

        toggleContactMenu();
    }

    function unblockUser(userId) {
        if (!confirm('Разблокировать этого пользователя? Обмен сообщениями станет возможным.')) return;

        fetch(`/messages/${userId}/unblock`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Пользователь разблокирован');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Ошибка разблокировки', 'error');
            }
        })
        .catch(err => {
            showToast('Ошибка соединения', 'error');
        });
    }

    function clearMessages(userId) {
        if (!confirm('Очистить историю сообщений с этим пользователем? Это действие необратимо.')) return;

        fetch(`/messages/${userId}/clear`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('История сообщений очищена');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Ошибка очистки чата', 'error');
            }
        })
        .catch(err => {
            showToast('Ошибка соединения', 'error');
        });

        toggleContactMenu();
    }

    function deleteChat(userId) {
        if (!confirm('Удалить этот чат из списка и всю историю сообщений? Это действие необратимо.')) return;

        fetch(`/messages/${userId}/delete-chat`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Чат удален');
                setTimeout(() => window.location.href = '/messages', 1000);
            } else {
                showToast(data.message || 'Ошибка удаления чата', 'error');
            }
        })
        .catch(err => {
            showToast('Ошибка соединения', 'error');
        });

        toggleContactMenu();
    }

    function openBlockedUsersModal() {
        document.getElementById('blocked-modal').classList.remove('hidden');
        loadBlockedUsers();
    }

    function closeBlockedModal() {
        document.getElementById('blocked-modal').classList.add('hidden');
    }

    function loadBlockedUsers() {
        const list = document.getElementById('blocked-users-list');
        list.innerHTML = '<div class="text-center text-sm text-slate-500 dark:text-zinc-400 py-8">Загрузка...</div>';

        fetch('/messages/blocked-users', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(users => {
            list.innerHTML = '';

            if (users.length === 0) {
                list.innerHTML = '<div class="text-center text-sm text-slate-500 dark:text-zinc-400 py-8">Нет заблокированных пользователей</div>';
                return;
            }

            users.forEach(user => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between p-3 bg-slate-50 dark:bg-zinc-800 rounded-lg';
                div.innerHTML = `
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            ${user.name.charAt(0)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-slate-900 dark:text-white truncate">${user.name}</div>
                            <div class="text-xs text-slate-500 dark:text-zinc-400 truncate">${user.email || ''}</div>
                        </div>
                    </div>
                    <button onclick="unblockUserFromModal(${user.id})" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs rounded-lg transition-colors flex-shrink-0 ml-2">
                        Разблокировать
                    </button>
                `;
                list.appendChild(div);
            });
        })
        .catch(err => {
            list.innerHTML = '<div class="text-center text-sm text-red-500 py-8">Ошибка загрузки</div>';
        });
    }

    function unblockUserFromModal(userId) {
        fetch(`/messages/${userId}/unblock`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Пользователь разблокирован');
                loadBlockedUsers();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Ошибка разблокировки', 'error');
            }
        })
        .catch(err => {
            showToast('Ошибка соединения', 'error');
        });
    }

    // Контекстное меню сообщений
    function showContextMenu(event, messageId, type, senderId) {
        event.preventDefault();
        event.stopPropagation();
        selectedMessageId = messageId;
        selectedMessageType = type;
        selectedSenderId = senderId;

        const contextMenu = document.getElementById('context-menu');
        const editBtn = document.getElementById('ctx-edit');
        const deleteAllBtn = document.getElementById('ctx-delete-all');

        // Используем строгое сравнение строк для надежной проверки владельца
        const isOwner = (String(senderId) === String(currentUserId));
        editBtn.style.display = isOwner ? 'flex' : 'none';
        deleteAllBtn.style.display = isOwner ? 'flex' : 'none';

        contextMenu.classList.remove('hidden');

        let x = event.clientX;
        let y = event.clientY;
        const menuWidth = 180;
        const menuHeight = 200;
        if (x + menuWidth > window.innerWidth) x = window.innerWidth - menuWidth - 10;
        if (y + menuHeight > window.innerHeight) y = window.innerHeight - menuHeight - 10;

        contextMenu.style.left = x + 'px';
        contextMenu.style.top = y + 'px';
    }

    document.addEventListener('click', function(event) {
        const contextMenu = document.getElementById('context-menu');
        if (!contextMenu.contains(event.target)) {
            contextMenu.classList.add('hidden');
        }
        const contactMenu = document.getElementById('contact-menu');
        if (!contactMenu.contains(event.target) && !event.target.closest('[onclick*="toggleContactMenu"]')) {
            contactMenu.classList.add('hidden');
        }
        const stickerPanel = document.getElementById('sticker-panel');
        if (stickerPanel && !stickerPanel.contains(event.target) && !event.target.closest('[onclick*="toggleStickerPanel"]')) {
            stickerPanel.classList.add('hidden');
        }
        const searchResults = document.getElementById('search-results');
        if (searchResults && !searchResults.contains(event.target) && !event.target.closest('#chat-search')) {
            searchResults.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('context-menu').classList.add('hidden');
            document.getElementById('contact-menu').classList.add('hidden');
            if (activeEditMessageId !== null) {
                cancelEdit(activeEditMessageId);
            }
            closeBlockedModal();
        }
    });

    // ===== INLINE РЕДАКТИРОВАНИЕ =====

    function startInlineEdit() {
        if (String(selectedSenderId) !== String(currentUserId)) {
            showToast('Можно редактировать только свои сообщения', 'error');
            return;
        }

        // Закрыть предыдущее редактирование если есть
        if (activeEditMessageId !== null) {
            cancelEdit(activeEditMessageId);
        }

        const messageRow = document.querySelector(`.message-row[data-message-id="${selectedMessageId}"]`);
        if (!messageRow) {
            showToast('Сообщение не найдено', 'error');
            return;
        }

        const editPanel = messageRow.querySelector('.inline-edit-panel');
        if (!editPanel) {
            showToast('Панель редактирования не найдена', 'error');
            return;
        }

        // Показать панель редактирования (явно убираем hidden и ставим display)
        editPanel.classList.remove('hidden');
        editPanel.style.display = 'block';

        activeEditMessageId = selectedMessageId;

        // Скрыть контекстное меню
        document.getElementById('context-menu').classList.add('hidden');

        // Фокус на textarea
        setTimeout(() => {
            const textarea = editPanel.querySelector('.inline-edit-textarea');
            if (textarea) {
                textarea.focus();
                textarea.setSelectionRange(textarea.value.length, textarea.value.length);
            }
        }, 50);

        // Прокрутить к сообщению
        messageRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function cancelEdit(messageId) {
        const messageRow = document.querySelector(`.message-row[data-message-id="${messageId}"]`);
        if (messageRow) {
            const editPanel = messageRow.querySelector('.inline-edit-panel');
            if (editPanel) {
                editPanel.classList.add('hidden');
                editPanel.style.display = 'none';
                // Восстановить оригинальный текст в textarea
                const textarea = editPanel.querySelector('.inline-edit-textarea');
                const originalText = messageRow.dataset.messageText || '';
                if (textarea) textarea.value = originalText;
            }
        }
        activeEditMessageId = null;
    }

    function saveInlineEdit(messageId) {
        const messageRow = document.querySelector(`.message-row[data-message-id="${messageId}"]`);
        if (!messageRow) return;

        const editPanel = messageRow.querySelector('.inline-edit-panel');
        const textarea = editPanel.querySelector('.inline-edit-textarea');
        const newText = textarea.value.trim();

        if (!newText) {
            showToast('Сообщение не может быть пустым', 'error');
            return;
        }
        if (newText.length > 2000) {
            showToast('Сообщение слишком длинное (макс. 2000 символов)', 'error');
            return;
        }

        fetch(`/messages/${messageId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ body: newText })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Обновить текст сообщения
                const textElement = messageRow.querySelector('.message-text');
                if (textElement) {
                    textElement.textContent = data.body || newText;
                }
                messageRow.dataset.messageText = data.body || newText;

                // Скрыть панель редактирования
                editPanel.classList.add('hidden');
                editPanel.style.display = 'none';
                activeEditMessageId = null;

                showToast('Сообщение отредактировано');
            } else {
                showToast(data.message || 'Ошибка редактирования', 'error');
            }
        })
        .catch(err => showToast('Ошибка соединения', 'error'));
    }

    function copyMessage() {
        const messageRow = document.querySelector(`.message-row[data-message-id="${selectedMessageId}"]`);
        if (messageRow) {
            const text = messageRow.dataset.messageText || messageRow.querySelector('.message-text').textContent;
            navigator.clipboard.writeText(text).then(() => {
                showToast('Скопировано в буфер обмена');
            }).catch(() => showToast('Не удалось скопировать', 'error'));
        }
        document.getElementById('context-menu').classList.add('hidden');
    }

    function forwardMessage() {
        const messageRow = document.querySelector(`.message-row[data-message-id="${selectedMessageId}"]`);
        if (messageRow) {
            const input = document.getElementById('message-input');
            input.value = messageRow.dataset.messageText || messageRow.querySelector('.message-text').textContent;
            input.focus();
            showToast('Сообщение готово к пересылке');
        }
        document.getElementById('context-menu').classList.add('hidden');
    }

    function deleteMessage() {
        if (!confirm('Удалить это сообщение у себя?')) return;

        fetch(`/messages/${selectedMessageId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ forAll: false })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                removeMessageElement(selectedMessageId);
                showToast('Сообщение удалено');
            } else {
                showToast(data.message || 'Ошибка удаления', 'error');
            }
        })
        .catch(err => showToast('Ошибка соединения', 'error'));

        document.getElementById('context-menu').classList.add('hidden');
    }

    function deleteMessageForAll() {
        if (String(selectedSenderId) !== String(currentUserId)) {
            showToast('Можно удалять для всех только свои сообщения', 'error');
            return;
        }
        if (!confirm('Удалить это сообщение для всех участников?')) return;

        fetch(`/messages/${selectedMessageId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ forAll: true })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                removeMessageElement(selectedMessageId);
                showToast('Сообщение удалено для всех');
            } else {
                showToast(data.message || 'Ошибка удаления', 'error');
            }
        })
        .catch(err => showToast('Ошибка соединения', 'error'));

        document.getElementById('context-menu').classList.add('hidden');
    }

    function removeMessageElement(messageId) {
        const row = document.querySelector(`.message-row[data-message-id="${messageId}"]`);
        if (row) {
            row.classList.add('fade-out');
            setTimeout(() => row.remove(), 250);
        }
    }

    function showToast(message, type = 'success') {
        const existing = document.querySelector('.toast-notification');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = `toast-notification toast-in fixed bottom-24 left-1/2 transform -translate-x-1/2 px-5 py-2.5 rounded-lg shadow-xl z-[10000] text-white text-xs font-medium ${type === 'error' ? 'bg-red-600' : 'bg-blue-600'}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translate(-50%, 15px)';
            toast.style.transition = 'all 0.25s';
            setTimeout(() => toast.remove(), 250);
        }, 2000);
    }

    function toggleStickerPanel() {
        document.getElementById('sticker-panel').classList.toggle('hidden');
    }

    function insertSticker(sticker) {
        const input = document.getElementById('message-input');
        input.value += sticker;
        input.focus();
        document.getElementById('sticker-panel').classList.add('hidden');
    }

    document.getElementById('file-input')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 10 * 1024 * 1024) {
            showToast('Файл слишком большой (макс. 10MB)', 'error');
            this.value = '';
            return;
        }

        const allowedTypes = ['image/', 'video/', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
        const isAllowed = allowedTypes.some(t => file.type.startsWith(t));
        if (!isAllowed) {
            showToast('Недопустимый тип файла', 'error');
            this.value = '';
            return;
        }

        document.getElementById('file-name').textContent = file.name;
        document.getElementById('file-preview').classList.remove('hidden');
        document.getElementById('file-preview').classList.add('flex');
    });

    function removeFile() {
        document.getElementById('file-input').value = '';
        document.getElementById('file-preview').classList.add('hidden');
        document.getElementById('file-preview').classList.remove('flex');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const chat = document.getElementById('chat-container');
        const scrollDownBtn = document.getElementById('scroll-down-btn');

        if (chat) {
            chat.scrollTop = chat.scrollHeight;

            chat.addEventListener('scroll', function() {
                const distanceFromBottom = chat.scrollHeight - chat.scrollTop - chat.clientHeight;
                if (distanceFromBottom > 150) {
                    scrollDownBtn.classList.remove('hidden');
                    scrollDownBtn.classList.add('flex');
                } else {
                    scrollDownBtn.classList.add('hidden');
                    scrollDownBtn.classList.remove('flex');
                }
            });

            scrollDownBtn.addEventListener('click', function() {
                chat.scrollTo({ top: chat.scrollHeight, behavior: 'smooth' });
            });
        }
    });
</script>
@endsection

