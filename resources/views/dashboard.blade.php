@extends('layouts.admin')

@section('content')
<div id="pjax-container">
    <section class="page-section active" id="page-dashboard">

        <!-- Header Section -->
        <div class="dashboard-header">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <div class="breadcrumb-custom mb-3">
                        <span class="breadcrumb-item" data-i18n="breadcrumb_home">Главная</span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active" data-i18n="control_panel">Панель управления</span>
                    </div>
                    @auth
                    <h1 class="dashboard-title mb-2">
                        <span data-i18n="welcomeBack">Доброе утро</span>, {{ explode(' ', auth()->user()->name)[0] }} !
                    </h1>
                    @endauth
                    <p class="dashboard-subtitle mb-0">
                        <span data-i18n="dashSubtitle">
                            У вас {{ $stats['pending'] ?? 0 }} документов ожидают подписания и {{ $stats['incoming'] ?? 0 }} новых входящих.
                        </span>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="header-actions">

                        <button class="btn btn-primary-custom btn-glow"
                                onclick="window.location.href='{{ route('documents.create') }}'">
                            <i class="bi bi-plus-lg me-2"></i>
                            <span data-i18n="createDocument">Новый документ</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <!-- Total Documents -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label" data-i18n="totalDocs">Всего документов</span>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value">
                    {{ number_format($stats['total'] ?? 0, 0, '.', ' ') }}
                </div>
                <span class="stat-delta {{ $docsGrowth >= 0 ? 'delta-up' : 'delta-down' }}">
            {{ $docsGrowth >= 0 ? '▲' : '▼' }} {{ abs($docsGrowth) }}%
        </span>
                <div class="stat-spark">
                    <x-sparkline :data="$sparklineData['total']" id="total" />
                </div>
            </div>

            <!-- Pending Documents -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label" data-i18n="pending">На подписании</span>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 19l7-7 3 3-7 7-3-3z"/>
                            <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value">{{ number_format($stats['waiting'] ?? 0, 0, '.', ' ') }}</div>
                <span class="stat-delta delta-down">{{ $stats['pending_change'] ?? 3 }}</span>
                <div class="stat-spark">
                    <x-sparkline :data="$sparklineData['waiting']" id="waiting" />
                </div>
            </div>

            <!-- Signed Documents -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label" data-i18n="signedMonth">Подписано за месяц</span>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value">{{ number_format($stats['signed'] ?? 0, 0, '.', ' ') }}</div>
                <span class="stat-delta delta-up">▲ {{ $signedGrowth ?? 0 }}%</span>
                <div class="stat-spark">
                    <x-sparkline :data="$sparklineData['signed']" id="signed" />
                </div>
            </div>

            <!-- Counterparties -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label" data-i18n="counterparties">Контакты</span>
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['users'] ?? 0 }}</div>
                <span class="stat-delta delta-up">
                    ▲ <span data-i18n="new_users">{{ $stats['new_users'] ?? 0 }} новых</span>
                </span>
                <div class="stat-spark">
                    <x-sparkline :data="$sparklineData['users']" id="users" />
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->

        <!-- Documents Table -->
        <div class="panel table-panel">
            <div class="panel-head">
                <div>
                    <h3 data-i18n="recentDocuments">Последние документы</h3>
                    <div class="sub" data-i18n="allOperations">Все операции в системе электронного документооборота</div>
                </div>

            </div>
            <div class="table-responsive-custom">
                <table class="table-modern">
                    <thead>
                    <tr>
                        <th data-i18n="thDoc">Документ</th>
                        <th data-i18n="thCounterparty">Команда</th>
                        <th data-i18n="thType">Тип</th>
                        <th data-i18n="thStatus">Статус</th>
                        <th data-i18n="thDate">Дата</th>
                        <th data-i18n="thActions">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($documents as $doc)
                    <tr class="table-row-hover" data-doc-id="{{ $doc->id }}">
                        <td>
                            <div class="doc-cell">
                                <div class="doc-icon">
                                    {{ strtoupper(pathinfo($doc->file_path ?? 'pdf', PATHINFO_EXTENSION)) }}
                                </div>
                                <div class="doc-meta">
                                    <b>{{ $doc->title }}</b>
                                    <small>ID: {{ $doc->id }} · {{ $doc->file_size ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="counterparty-cell">{{ $doc->counterparty ?? 'Внутренний' }}</td>
                        <td class="type-cell">{{ $doc->type ?? 'Документ' }}</td>
                        <td>
                                <span class="pill pill-{{ $doc->status }}" data-status="{{ $doc->status }}">
                                    @switch($doc->status)
                                        @case('pending')
                                            <span data-i18n="status_pending">На подписании</span>
                                            @break
                                        @case('approved')
                                        @case('signed')
                                            <span data-i18n="status_signed">Подписан</span>
                                            @break
                                        @case('rejected')
                                            <span data-i18n="status_rejected">Отклонён</span>
                                            @break
                                        @case('draft')
                                            <span data-i18n="status_draft">Черновик</span>
                                            @break
                                        @default
                                            {{ ucfirst($doc->status) }}
                                    @endswitch
                                </span>
                        </td>
                        <td class="date-cell">{{ $doc->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('documents.show', $doc->id) }}"
                                   class="btn-action btn-view"
                                   data-tooltip="Просмотр"
                                   data-i18n-title="tooltip_view"
                                   title="Просмотр">
                                    <i class="bi bi-eye"></i>
                                    <span class="btn-glow-effect"></span>
                                </a>


                            </div>
                        </td><style>
                            /* === КНОПКИ ДЕЙСТВИЙ В ТАБЛИЦЕ === */
                            .action-buttons {
                                display: flex !important;
                                align-items: center !important;
                                justify-content: flex-end !important;
                                gap: 8px !important;
                                position: relative !important;
                            }

                            .btn-action {
                                position: relative !important;
                                width: 34px !important;
                                height: 34px !important;
                                border-radius: 9px !important;
                                display: inline-flex !important;
                                align-items: center !important;
                                justify-content: center !important;
                                border: 1px solid rgba(255, 255, 255, 0.08) !important;
                                background: rgba(255, 255, 255, 0.03) !important;
                                color: var(--muted, #888) !important;
                                cursor: pointer !important;
                                transition: all 0.25s ease !important;
                                text-decoration: none !important;
                                overflow: hidden !important;
                                z-index: 1 !important;
                                padding: 0 !important;
                                margin: 0 !important;
                            }

                            .btn-action i {
                                font-size: 14px !important;
                                position: relative !important;
                                z-index: 2 !important;
                                transition: transform 0.25s ease !important;
                                display: block !important;
                            }

                            .btn-glow-effect {
                                position: absolute !important;
                                inset: 0 !important;
                                border-radius: 9px !important;
                                opacity: 0 !important;
                                transition: opacity 0.3s ease !important;
                                z-index: 1 !important;
                                pointer-events: none !important;
                            }

                            /* === КНОПКА ПРОСМОТР (View) — Синяя === */
                            .btn-view {
                                color: rgba(var(--glow, 0, 242, 254), 0.8) !important;
                            }

                            .btn-view .btn-glow-effect {
                                background: radial-gradient(circle at center, rgba(var(--glow, 0, 242, 254), 0.25), transparent 70%) !important;
                            }

                            .btn-view:hover {
                                color: rgba(var(--glow, 0, 242, 254), 1) !important;
                                border-color: rgba(var(--glow, 0, 242, 254), 0.5) !important;
                                background: rgba(var(--glow, 0, 242, 254), 0.1) !important;
                                box-shadow: 0 0 16px rgba(var(--glow, 0, 242, 254), 0.4), inset 0 0 12px rgba(var(--glow, 0, 242, 254), 0.15) !important;
                                transform: translateY(-2px) !important;
                            }

                            .btn-view:hover .btn-glow-effect {
                                opacity: 1 !important;
                            }

                            .btn-view:hover i {
                                transform: scale(1.15) !important;
                            }

                            /* === КНОПКА РЕДАКТИРОВАТЬ (Edit) — Оранжевая === */
                            .btn-edit {
                                color: rgba(255, 181, 71, 0.8) !important;
                            }

                            .btn-edit .btn-glow-effect {
                                background: radial-gradient(circle at center, rgba(255, 181, 71, 0.25), transparent 70%) !important;
                            }

                            .btn-edit:hover {
                                color: #ffb547 !important;
                                border-color: rgba(255, 181, 71, 0.5) !important;
                                background: rgba(255, 181, 71, 0.1) !important;
                                box-shadow: 0 0 16px rgba(255, 181, 71, 0.4), inset 0 0 12px rgba(255, 181, 71, 0.15) !important;
                                transform: translateY(-2px) !important;
                            }

                            .btn-edit:hover .btn-glow-effect {
                                opacity: 1 !important;
                            }

                            .btn-edit:hover i {
                                transform: scale(1.15) rotate(-8deg) !important;
                            }

                            /* === КНОПКА УДАЛИТЬ (Delete) — Красная === */
                            .btn-delete {
                                color: rgba(255, 99, 99, 0.8) !important;
                            }

                            .btn-delete .btn-glow-effect {
                                background: radial-gradient(circle at center, rgba(255, 99, 99, 0.25), transparent 70%) !important;
                            }

                            .btn-delete:hover {
                                color: #ff6363 !important;
                                border-color: rgba(255, 99, 99, 0.5) !important;
                                background: rgba(255, 99, 99, 0.1) !important;
                                box-shadow: 0 0 16px rgba(255, 99, 99, 0.4), inset 0 0 12px rgba(255, 99, 99, 0.15) !important;
                                transform: translateY(-2px) !important;
                            }

                            .btn-delete:hover .btn-glow-effect {
                                opacity: 1 !important;
                            }

                            .btn-delete:hover i {
                                transform: scale(1.15) !important;
                            }

                            /* === АКТИВНОЕ СОСТОЯНИЕ (нажатие) === */
                            .btn-action:active {
                                transform: translateY(0) scale(0.95) !important;
                                transition: all 0.1s ease !important;
                            }

                            /* === ТУЛТИП (data-tooltip) === */
                            .btn-action::before {
                                content: attr(data-tooltip) !important;
                                position: absolute !important;
                                bottom: calc(100% + 8px) !important;
                                left: 50% !important;
                                transform: translateX(-50%) translateY(4px) !important;
                                padding: 5px 10px !important;
                                background: rgba(10, 13, 20, 0.95) !important;
                                border: 1px solid rgba(255, 255, 255, 0.12) !important;
                                border-radius: 6px !important;
                                color: var(--text, #fff) !important;
                                font-size: 11px !important;
                                font-weight: 600 !important;
                                white-space: nowrap !important;
                                opacity: 0 !important;
                                pointer-events: none !important;
                                transition: all 0.2s ease !important;
                                z-index: 100 !important;
                                backdrop-filter: blur(8px) !important;
                            }

                            .btn-action::after {
                                content: "" !important;
                                position: absolute !important;
                                bottom: calc(100% + 2px) !important;
                                left: 50% !important;
                                transform: translateX(-50%) !important;
                                border: 5px solid transparent !important;
                                border-top-color: rgba(10, 13, 20, 0.95) !important;
                                opacity: 0 !important;
                                pointer-events: none !important;
                                transition: all 0.2s ease !important;
                                z-index: 100 !important;
                            }

                            .btn-action:hover::before,
                            .btn-action:hover::after {
                                opacity: 1 !important;
                                transform: translateX(-50%) translateY(0) !important;
                            }

                            .btn-action:hover::after {
                                transform: translateX(-50%) !important;
                            }

                            /* Цветной тултип для каждой кнопки */
                            .btn-view::before {
                                border-color: rgba(var(--glow, 0, 242, 254), 0.4) !important;
                                color: rgba(var(--glow, 0, 242, 254), 1) !important;
                            }

                            .btn-view::after {
                                border-top-color: rgba(var(--glow, 0, 242, 254), 0.8) !important;
                            }

                            .btn-edit::before {
                                border-color: rgba(255, 181, 71, 0.4) !important;
                                color: #ffb547 !important;
                            }

                            .btn-edit::after {
                                border-top-color: rgba(255, 181, 71, 0.8) !important;
                            }

                            .btn-delete::before {
                                border-color: rgba(255, 99, 99, 0.4) !important;
                                color: #ff6363 !important;
                            }

                            .btn-delete::after {
                                border-top-color: rgba(255, 99, 99, 0.8) !important;
                            }

                            /* === АДАПТИВНОСТЬ === */
                            @media (max-width: 768px) {
                                .action-buttons {
                                    gap: 6px !important;
                                }

                                .btn-action {
                                    width: 30px !important;
                                    height: 30px !important;
                                }

                                .btn-action i {
                                    font-size: 12px !important;
                                }

                                .btn-action::before,
                                .btn-action::after {
                                    display: none !important;
                                }
                            }
                        </style>

                        {{-- Bootstrap Icons (если не подключены) --}}
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
                    </tr>
                    @endforeach

                    @if($documents->isEmpty())
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="bi bi-folder2-open"></i>
                                </div>
                                <p class="empty-text" data-i18n="no_documents">Документы не найдены</p>
                                <small style="color: var(--text-muted);" data-i18n="create_first_doc">Создайте первый документ, чтобы начать работу</small>
                            </div>
                        </td>
                    </tr>
                    @endif
                    </tbody>
                </table>
            </div>

            @if($documents instanceof \Illuminate\Pagination\LengthAwarePaginator && $documents->hasPages())
            <div class="pagination-wrapper">
                {{ $documents->links() }}
            </div>
            @endif
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-box">
            <div class="modal-icon-wrapper danger">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <h3 class="modal-title" data-i18n="delete_doc_title">Удалить документ?</h3>
            <p class="modal-text">
                <span data-i18n="delete_doc_text">Вы действительно хотите удалить документ</span>
                <b id="deleteDocName"></b>?
                <span data-i18n="delete_doc_warning">Это действие нельзя отменить.</span>
            </p>
            <form id="deleteForm" method="POST" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()" data-i18n="cancel">
                    Отмена
                </button>
                <button class="modal-btn modal-btn-danger" onclick="submitDelete()">
                    <i class="bi bi-trash3 me-1"></i> <span data-i18n="delete">Удалить</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container" id="toastContainer"></div>

    <style>
        :root {
            /* Глобальная переменная подсветки из layout админки */
            --bg-dark: #0a0d14;
            --bg-card: rgba(20, 23, 35, 0.8);
            --bg-panel: rgba(15, 18, 28, 0.9);
            --border-color: rgba(var(--glow), 0.15);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent: rgb(var(--glow));
            --accent-light: rgba(var(--glow), 0.8);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }

        body {
            background: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        #pjax-container {
            padding: clamp(0.75rem, 2vw, 2rem);
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .dashboard-header {
            margin-bottom: clamp(1rem, 2vw, 2rem);
        }

        .breadcrumb-custom {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: clamp(0.75rem, 1.5vw, 0.875rem);
            color: var(--text-muted);
        }

        .breadcrumb-separator {
            color: var(--text-muted);
        }

        .dashboard-title {
            font-size: clamp(1.25rem, 3vw, 2rem);
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .dashboard-subtitle {
            color: var(--text-secondary);
            font-size: clamp(0.8rem, 1.8vw, 1rem);
            line-height: 1.5;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .btn-outline-custom {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: clamp(0.5rem, 1.5vw, 0.75rem) clamp(1rem, 2vw, 1.5rem);
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: clamp(0.8rem, 1.5vw, 0.95rem);
        }

        .btn-outline-custom:hover {
            border-color: var(--accent);
            background: rgba(var(--glow), 0.1);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, rgb(var(--glow)) 0%, rgba(var(--glow), 0.8) 100%);
            border: none;
            color: white;
            padding: clamp(0.5rem, 1.5vw, 0.75rem) clamp(1rem, 2vw, 1.5rem);
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(var(--glow), 0.4);
            cursor: pointer;
            font-size: clamp(0.8rem, 1.5vw, 0.95rem);
        }

        .btn-primary-custom a {
            color: inherit;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(var(--glow), 0.6);
        }

        .btn-glow {
            position: relative;
            overflow: hidden;
        }

        .btn-glow::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .btn-glow:hover::before {
            left: 100%;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: clamp(0.75rem, 1.5vw, 1.5rem);
            margin-bottom: clamp(1rem, 2vw, 2rem);
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: clamp(12px, 1.5vw, 16px);
            padding: clamp(1rem, 2vw, 1.5rem);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgb(var(--glow)), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(var(--glow), 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3), 0 0 40px rgba(var(--glow), 0.15);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: clamp(0.5rem, 1vw, 1rem);
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: clamp(0.7rem, 1.3vw, 0.875rem);
            font-weight: 500;
        }

        .stat-icon {
            width: clamp(32px, 4vw, 40px);
            height: clamp(32px, 4vw, 40px);
            border-radius: 10px;
            background: rgba(var(--glow), 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgb(var(--glow));
        }

        .stat-icon svg {
            width: clamp(16px, 2vw, 20px);
            height: clamp(16px, 2vw, 20px);
        }

        .stat-value {
            font-size: clamp(1.25rem, 3vw, 2rem);
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .stat-delta {
            display: inline-block;
            padding: clamp(0.2rem, 0.5vw, 0.25rem) clamp(0.5rem, 1vw, 0.75rem);
            border-radius: 6px;
            font-size: clamp(0.65rem, 1.2vw, 0.75rem);
            font-weight: 600;
            margin-bottom: clamp(0.5rem, 1vw, 1rem);
        }

        .delta-up {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }

        .delta-down {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }

        .stat-spark {
            height: clamp(30px, 4vw, 40px);
        }

        .stat-spark svg {
            width: 100%;
            height: 100%;
        }

        /* Main Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: clamp(0.75rem, 1.5vw, 1.5rem);
            margin-bottom: clamp(1rem, 2vw, 2rem);
        }

        .panel {
            background: var(--bg-panel);
            border: 1px solid var(--border-color);
            border-radius: clamp(12px, 1.5vw, 16px);
            padding: clamp(1rem, 2vw, 1.5rem);
        }

        .panel-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: clamp(1rem, 1.5vw, 1.5rem);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .panel-head h3 {
            font-size: clamp(1rem, 2vw, 1.25rem);
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--text-primary);
        }

        .panel-head .sub {
            color: var(--text-secondary);
            font-size: clamp(0.7rem, 1.3vw, 0.875rem);
        }

        .tabs {
            display: flex;
            gap: 0.5rem;
            background: rgba(15, 18, 28, 0.5);
            padding: 0.25rem;
            border-radius: 8px;
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            background: rgb(var(--glow));
            color: white;
            box-shadow: 0 0 15px rgba(var(--glow), 0.4);
        }

        .tab-btn:hover:not(.active) {
            color: var(--text-primary);
        }

        /* Chart */
        .chart-container {
            height: clamp(200px, 25vw, 260px);
            background: var(--bg-panel);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .chart-container svg {
            width: 100%;
            height: 100%;
        }

        /* Signers */
        .signers-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .signer-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(15, 18, 28, 0.5);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .signer-item:hover {
            background: rgba(var(--glow), 0.05);
        }

        .signer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgb(var(--glow)) 0%, rgba(var(--glow), 0.8) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .signer-info {
            flex: 1;
            min-width: 0;
        }

        .signer-info b {
            display: block;
            color: var(--text-primary);
            font-size: 0.9375rem;
            margin-bottom: 0.25rem;
        }

        .signer-info small {
            color: var(--text-secondary);
            font-size: 0.8125rem;
        }

        .signer-status {
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-wait {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
        }

        .status-ok {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }

        /* Table */
        .table-responsive-custom {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-modern thead th {
            background: rgba(15, 18, 28, 0.5);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: clamp(0.65rem, 1.2vw, 0.75rem);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: clamp(0.75rem, 1.5vw, 1rem);
            border-bottom: 1px solid var(--border-color);
            text-align: left;
            position: sticky;
            top: 0;
            white-space: nowrap;
        }

        .table-modern tbody tr {
            transition: all 0.3s ease;
        }

        .table-row-hover:hover {
            background: rgba(var(--glow), 0.05);
            box-shadow: inset 3px 0 0 rgb(var(--glow));
        }

        .table-modern td {
            padding: clamp(0.75rem, 1.5vw, 1.25rem) clamp(0.5rem, 1vw, 1rem);
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            font-size: clamp(0.75rem, 1.3vw, 0.875rem);
        }

        .doc-cell {
            display: flex;
            align-items: center;
            gap: clamp(0.5rem, 1vw, 1rem);
        }

        .doc-icon {
            width: clamp(32px, 4vw, 40px);
            height: clamp(32px, 4vw, 40px);
            border-radius: 8px;
            background: rgba(var(--glow), 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgb(var(--glow));
            font-size: clamp(0.65rem, 1.2vw, 0.75rem);
            font-weight: 600;
            flex-shrink: 0;
        }

        .doc-meta {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            min-width: 0;
        }

        .doc-meta b {
            color: var(--text-primary);
            font-size: clamp(0.8rem, 1.4vw, 0.9375rem);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .doc-meta small {
            color: var(--text-muted);
            font-size: clamp(0.65rem, 1.1vw, 0.75rem);
        }

        .counterparty-cell, .type-cell {
            color: var(--text-secondary);
            font-size: clamp(0.75rem, 1.3vw, 0.875rem);
            white-space: nowrap;
        }

        .pill {
            display: inline-block;
            padding: clamp(0.25rem, 0.5vw, 0.375rem) clamp(0.5rem, 1vw, 0.875rem);
            border-radius: 20px;
            font-size: clamp(0.65rem, 1.2vw, 0.75rem);
            font-weight: 600;
            white-space: nowrap;
        }

        .pill-pending {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
        }

        .pill-approved, .pill-signed {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }

        .pill-rejected {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }

        .pill-draft {
            background: rgba(148, 163, 184, 0.15);
            color: var(--text-muted);
        }

        .date-cell {
            color: var(--text-muted);
            font-size: clamp(0.75rem, 1.3vw, 0.875rem);
            white-space: nowrap;
        }

        /* ===== ACTION BUTTONS (VIEW / EDIT / DELETE) ===== */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .btn-action {
            position: relative;
            width: clamp(28px, 3.5vw, 36px);
            height: clamp(28px, 3.5vw, 36px);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            font-size: clamp(0.8rem, 1.3vw, 1rem);
            overflow: hidden;
            background: transparent;
        }

        .btn-action i {
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease;
        }

        .btn-glow-effect {
            position: absolute;
            inset: 0;
            border-radius: inherit;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        /* VIEW */
        .btn-view {
            background: rgba(59, 130, 246, 0.08);
            color: #60a5fa;
            border-color: rgba(59, 130, 246, 0.15);
        }

        .btn-view .btn-glow-effect {
            background: radial-gradient(circle at center, rgba(59, 130, 246, 0.4), transparent 70%);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.6);
        }

        .btn-view:hover {
            background: rgba(59, 130, 246, 0.18);
            border-color: rgba(59, 130, 246, 0.5);
            color: #93c5fd;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);
        }

        .btn-view:hover .btn-glow-effect {
            opacity: 1;
        }

        .btn-view:hover i {
            transform: scale(1.15);
        }

        /* EDIT */
        .btn-edit {
            background: rgba(245, 158, 11, 0.08);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.15);
        }

        .btn-edit .btn-glow-effect {
            background: radial-gradient(circle at center, rgba(245, 158, 11, 0.4), transparent 70%);
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.6);
        }

        .btn-edit:hover {
            background: rgba(245, 158, 11, 0.18);
            border-color: rgba(245, 158, 11, 0.5);
            color: #fcd34d;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35);
        }

        .btn-edit:hover .btn-glow-effect {
            opacity: 1;
        }

        .btn-edit:hover i {
            transform: scale(1.15) rotate(-8deg);
        }

        /* DELETE */
        .btn-delete {
            background: rgba(239, 68, 68, 0.08);
            color: #f87171;
            border-color: rgba(239, 68, 68, 0.15);
        }

        .btn-delete .btn-glow-effect {
            background: radial-gradient(circle at center, rgba(239, 68, 68, 0.4), transparent 70%);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.6);
        }

        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.18);
            border-color: rgba(239, 68, 68, 0.5);
            color: #fca5a5;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.35);
        }

        .btn-delete:hover .btn-glow-effect {
            opacity: 1;
        }

        .btn-delete:hover i {
            transform: scale(1.15);
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: scale(1.15) rotate(0); }
            25% { transform: scale(1.15) rotate(-8deg); }
            75% { transform: scale(1.15) rotate(8deg); }
        }

        /* Tooltip */
        .btn-action::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%) translateY(4px);
            background: rgba(15, 18, 28, 0.95);
            color: var(--text-primary);
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.2s ease;
            border: 1px solid var(--border-color);
            z-index: 10;
        }

        .btn-action:hover::after {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        /* ===== DELETE MODAL ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            padding: 1rem;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-box {
            background: linear-gradient(145deg, #141723 0%, #0f121c 100%);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: 20px;
            padding: clamp(1.25rem, 3vw, 2rem);
            max-width: 420px;
            width: 100%;
            text-align: center;
            transform: scale(0.9) translateY(20px);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 40px rgba(239, 68, 68, 0.15);
        }

        .modal-overlay.active .modal-box {
            transform: scale(1) translateY(0);
        }

        .modal-icon-wrapper {
            width: clamp(48px, 8vw, 64px);
            height: clamp(48px, 8vw, 64px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: clamp(1.25rem, 3vw, 1.75rem);
        }

        .modal-icon-wrapper.danger {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
            animation: pulse-danger 2s ease-in-out infinite;
        }

        @keyframes pulse-danger {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            50% { box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); }
        }

        .modal-title {
            color: var(--text-primary);
            font-size: clamp(1.1rem, 2.5vw, 1.375rem);
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .modal-text {
            color: var(--text-secondary);
            font-size: clamp(0.8rem, 1.8vw, 0.9375rem);
            line-height: 1.6;
            margin-bottom: 1.75rem;
        }

        .modal-text b {
            color: var(--text-primary);
            font-weight: 600;
        }

        .modal-actions {
            display: flex;
            gap: 0.75rem;
        }

        .modal-btn {
            flex: 1;
            padding: clamp(0.6rem, 1.5vw, 0.75rem) clamp(1rem, 2vw, 1.25rem);
            border-radius: 10px;
            font-weight: 600;
            font-size: clamp(0.8rem, 1.8vw, 0.9375rem);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
        }

        .modal-btn-cancel {
            background: rgba(148, 163, 184, 0.1);
            color: var(--text-secondary);
            border-color: rgba(148, 163, 184, 0.2);
        }

        .modal-btn-cancel:hover {
            background: rgba(148, 163, 184, 0.18);
            color: var(--text-primary);
            border-color: rgba(148, 163, 184, 0.4);
        }

        .modal-btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.35);
        }

        .modal-btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
        }

        /* ===== TOAST ===== */
        .toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            pointer-events: none;
            max-width: calc(100vw - 2rem);
        }

        .toast {
            background: linear-gradient(145deg, #141723 0%, #0f121c 100%);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: clamp(0.75rem, 1.5vw, 1rem) clamp(1rem, 2vw, 1.25rem);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 280px;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            animation: toast-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: auto;
        }

        .toast.toast-out {
            animation: toast-out 0.3s ease forwards;
        }

        .toast.success {
            border-color: rgba(16, 185, 129, 0.4);
        }

        .toast.error {
            border-color: rgba(239, 68, 68, 0.4);
        }

        .toast-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .toast.success .toast-icon {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }

        .toast.error .toast-icon {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }

        .toast-content {
            flex: 1;
            min-width: 0;
        }

        .toast-title {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.9375rem;
            margin-bottom: 0.125rem;
        }

        .toast-message {
            color: var(--text-secondary);
            font-size: 0.8125rem;
        }

        @keyframes toast-in {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes toast-out {
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: clamp(2rem, 5vw, 3rem) 1rem;
        }

        .empty-icon {
            font-size: clamp(2rem, 5vw, 3rem);
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .empty-text {
            color: var(--text-secondary);
            margin: 0;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        /* Pagination */
        .pagination-wrapper {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
        }

        .pagination-wrapper .pagination {
            gap: 0.375rem;
        }

        .pagination-wrapper .page-link {
            background: rgba(15, 18, 28, 0.5);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: clamp(0.4rem, 1vw, 0.5rem) clamp(0.6rem, 1.2vw, 0.875rem);
            border-radius: 8px;
            font-size: clamp(0.75rem, 1.3vw, 0.875rem);
            transition: all 0.3s ease;
        }

        .pagination-wrapper .page-link:hover {
            background: rgba(var(--glow), 0.15);
            color: var(--text-primary);
            border-color: rgb(var(--glow));
        }

        .pagination-wrapper .page-item.active .page-link {
            background: rgb(var(--glow));
            border-color: rgb(var(--glow));
            color: white;
            box-shadow: 0 0 15px rgba(var(--glow), 0.4);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-dark);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--text-muted);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgb(var(--glow));
        }

        /* ===== RESPONSIVE BREAKPOINTS ===== */

        /* Extra Large Desktop (1400px+) */
        @media (min-width: 1400px) {
            #pjax-container {
                padding: 2.5rem;
            }

            .dashboard-title {
                font-size: 2.25rem;
            }

            .stat-value {
                font-size: 2.25rem;
            }
        }

        /* Large Desktop (1200px - 1399px) */
        @media (max-width: 1399px) and (min-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Tablet Landscape (992px - 1199px) */
        @media (max-width: 1199px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Tablet Portrait (768px - 991px) */
        @media (max-width: 991px) {
            #pjax-container {
                padding: 1.25rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .dashboard-title {
                font-size: 1.5rem;
            }

            .stat-card {
                padding: 1.25rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .table-modern td,
            .table-modern th {
                padding: 0.75rem 0.5rem;
            }

            .doc-meta b {
                font-size: 0.85rem;
            }

            .doc-meta small {
                font-size: 0.7rem;
            }
        }

        /* Mobile Landscape & Small Tablet (576px - 767px) */
        @media (max-width: 767px) {
            #pjax-container {
                padding: 1rem;
            }

            .dashboard-header .row > div {
                text-align: center !important;
            }

            .header-actions {
                justify-content: center;
                margin-top: 1rem;
            }

            .btn-primary-custom {
                width: 100%;
                max-width: 300px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-label {
                font-size: 0.75rem;
            }

            .stat-value {
                font-size: 1.35rem;
            }

            .stat-icon {
                width: 36px;
                height: 36px;
            }

            .stat-icon svg {
                width: 18px;
                height: 18px;
            }

            .panel {
                padding: 1rem;
            }

            .panel-head h3 {
                font-size: 1.1rem;
            }

            .table-modern thead {
                display: none;
            }

            .table-modern tbody tr {
                display: block;
                background: var(--bg-card);
                border: 1px solid var(--border-color);
                border-radius: 12px;
                margin-bottom: 1rem;
                padding: 1rem;
            }

            .table-modern tbody tr:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            }

            .table-modern td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border: none;
                border-bottom: 1px solid rgba(var(--glow), 0.08);
            }

            .table-modern td:last-child {
                border-bottom: none;
            }

            .table-modern td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--text-secondary);
                font-size: 0.75rem;
                text-transform: uppercase;
                margin-right: 1rem;
            }

            .doc-cell {
                flex-direction: row;
            }

            .action-buttons {
                justify-content: flex-end;
            }

            .action-buttons::before {
                display: none !important;
            }

            .modal-box {
                padding: 1.5rem;
                margin: 1rem;
            }

            .modal-actions {
                flex-direction: column-reverse;
            }

            .modal-btn {
                width: 100%;
            }

            .toast-container {
                top: 1rem;
                right: 1rem;
                left: 1rem;
            }

            .toast {
                min-width: auto;
                max-width: 100%;
            }
        }

        /* Mobile Portrait (480px - 575px) */
        @media (max-width: 575px) {
            #pjax-container {
                padding: 0.75rem;
            }

            .dashboard-title {
                font-size: 1.25rem;
            }

            .dashboard-subtitle {
                font-size: 0.8rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-header {
                margin-bottom: 0.75rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .stat-spark {
                height: 35px;
            }

            .panel-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .panel-head h3 {
                font-size: 1rem;
            }

            .panel-head .sub {
                font-size: 0.75rem;
            }

            .table-modern td {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .table-modern td::before {
                margin-bottom: 0.25rem;
            }

            .doc-cell {
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
            }

            .doc-meta {
                width: 100%;
            }

            .doc-meta b {
                white-space: normal;
                word-break: break-word;
            }

            .action-buttons {
                width: 100%;
                justify-content: flex-start;
                margin-top: 0.5rem;
            }

            .action-buttons::before {
                display: none !important;
            }

            .btn-action {
                width: 32px;
                height: 32px;
            }

            .btn-action i {
                font-size: 13px;
            }
        }

        /* Small Mobile (360px - 479px) */
        @media (max-width: 479px) {
            #pjax-container {
                padding: 0.5rem;
            }

            .dashboard-title {
                font-size: 1.1rem;
            }

            .breadcrumb-custom {
                font-size: 0.7rem;
            }

            .stat-card {
                padding: 0.875rem;
            }

            .stat-label {
                font-size: 0.7rem;
            }

            .stat-value {
                font-size: 1.35rem;
            }

            .stat-icon {
                width: 32px;
                height: 32px;
            }

            .stat-icon svg {
                width: 16px;
                height: 16px;
            }

            .stat-delta {
                font-size: 0.65rem;
                padding: 0.2rem 0.5rem;
            }

            .panel {
                padding: 0.75rem;
                border-radius: 12px;
            }

            .panel-head h3 {
                font-size: 0.95rem;
            }

            .modal-box {
                padding: 1.25rem;
                border-radius: 16px;
            }

            .modal-title {
                font-size: 1.1rem;
            }

            .modal-text {
                font-size: 0.8rem;
            }

            .modal-btn {
                padding: 0.6rem 1rem;
                font-size: 0.8rem;
            }

            .toast {
                padding: 0.75rem 1rem;
            }

            .toast-title {
                font-size: 0.85rem;
            }

            .toast-message {
                font-size: 0.75rem;
            }
        }

        /* Extra Small Mobile (< 360px) */
        @media (max-width: 359px) {
            #pjax-container {
                padding: 0.5rem;
            }

            .dashboard-title {
                font-size: 1rem;
            }

            .stat-card {
                padding: 0.75rem;
            }

            .stat-value {
                font-size: 1.25rem;
            }

            .btn-primary-custom {
                font-size: 0.75rem;
                padding: 0.5rem 1rem;
            }

            .modal-box {
                padding: 1rem;
            }

            .modal-title {
                font-size: 1rem;
            }
        }

        /* Landscape orientation on mobile */
        @media (max-height: 500px) and (orientation: landscape) {
            .modal-overlay {
                align-items: flex-start;
                padding-top: 2rem;
            }

            .modal-box {
                max-height: 90vh;
                overflow-y: auto;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Print styles */
        @media print {
            .btn-primary-custom,
            .action-buttons,
            .modal-overlay,
            .toast-container {
                display: none !important;
            }

            body {
                background: white;
                color: black;
            }

            .stat-card,
            .panel {
                background: white;
                border: 1px solid #ddd;
                color: black;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ===== ПЕРЕВОДЫ / TRANSLATIONS / ТАРҶУМАҲО =====
            const translations = {
                ru: {
                    breadcrumb_home: "Главная",
                    control_panel: "Панель управления",
                    welcomeBack: "Доброе утро",
                    dashSubtitle: "У вас {pending} документов ожидают подписания и {incoming} новых входящих.",
                    new_users: "{count} новых",
                    createDocument: "Новый документ",
                    totalDocs: "Всего документов",
                    pending: "На подписании",
                    signedMonth: "Подписано за месяц",
                    counterparties: "Команда",
                    documentFlow: "Документооборот",
                    chartSubtitle: "Динамика за последние 12 месяцев",
                    week: "Неделя",
                    month: "Месяц",
                    year: "Год",
                    awaitingSignature: "Ожидают подписи",
                    needAttention: "Требуют вашего внимания",
                    noPending: "Нет ожидающих документов",
                    recentDocuments: "Последние документы",
                    allOperations: "Все операции в системе электронного документооборота",
                    all: "Все",
                    incoming: "Входящие",
                    outgoing: "Исходящие",
                    drafts: "Черновики",
                    thDoc: "Документ",
                    thCounterparty: "Контрагент",
                    thType: "Тип",
                    thStatus: "Статус",
                    thDate: "Дата",
                    thActions: "Действия",
                    status_pending: "На подписании",
                    status_signed: "Подписан",
                    status_rejected: "Отклонён",
                    status_draft: "Черновик",
                    tooltip_view: "Просмотр",
                    tooltip_edit: "Редактировать",
                    tooltip_delete: "Удалить",
                    no_documents: "Документы не найдены",
                    create_first_doc: "Создайте первый документ, чтобы начать работу",
                    no_data_chart: "Нет данных для отображения",
                    create_docs_hint: "Создайте документы, чтобы увидеть статистику",
                    docs_word: "документов",
                    delete_doc_title: "Удалить документ?",
                    delete_doc_text: "Вы действительно хотите удалить документ",
                    delete_doc_warning: "Это действие нельзя отменить.",
                    cancel: "Отмена",
                    delete: "Удалить",
                    deleteSuccess: "Документ успешно удалён",
                    deleteError: "Ошибка при удалении",
                    toast_success: "Успешно",
                    toast_error: "Ошибка",
                    internal: "Внутренний",
                    document_type: "Документ"
                },
                tj: {
                    breadcrumb_home: "Асосӣ",
                    control_panel: "Панели идоракунӣ",
                    welcomeBack: "Субҳ ба хайр",
                    dashSubtitle: "Шумо {pending} ҳуҷҷат дар интизори имзо ва {incoming} воридоти нав доред.",
                    new_users: "{count} нав",
                    createDocument: "Ҳуҷҷати нав",
                    totalDocs: "Ҳамаи ҳуҷҷатҳо",
                    pending: "Дар интизори имзо",
                    signedMonth: "Имзошуда дар моҳ",
                    counterparties: "Даста",
                    documentFlow: "Гардиши ҳуҷҷатҳо",
                    chartSubtitle: "Динамика дар 12 моҳи охир",
                    week: "Ҳафта",
                    month: "Моҳ",
                    year: "Сол",
                    awaitingSignature: "Интизори имзо",
                    needAttention: "Талаб кардани диққат",
                    noPending: "Ҳуҷҷати интизор нест",
                    recentDocuments: "Ҳуҷҷатҳои охирин",
                    allOperations: "Ҳамаи амалиётҳо дар системаи ҳуҷҷатгардонӣ",
                    all: "Ҳама",
                    incoming: "Воридотӣ",
                    outgoing: "Содиротӣ",
                    drafts: "Пешнависҳо",
                    thDoc: "Ҳуҷҷат",
                    thCounterparty: "Ҳамкор",
                    thType: "Намуд",
                    thStatus: "Статус",
                    thDate: "Сана",
                    thActions: "Амалҳо",
                    status_pending: "Дар интизори имзо",
                    status_signed: "Имзо шуд",
                    status_rejected: "Рад шуд",
                    status_draft: "Пешнавис",
                    tooltip_view: "Дидан",
                    tooltip_edit: "Таҳрир кардан",
                    tooltip_delete: "Нест кардан",
                    no_documents: "Ҳуҷҷатҳо ёфт нашуданд",
                    create_first_doc: "Барои оғози кор аввалин ҳуҷҷатро эҷод кунед",
                    no_data_chart: "Маълумот барои намоиш нест",
                    create_docs_hint: "Барои дидани омор ҳуҷҷатҳоро эҷод кунед",
                    docs_word: "ҳуҷҷат",
                    delete_doc_title: "Ҳуҷҷатро нест мекунед?",
                    delete_doc_text: "Шумо мутмаин ҳастед, ки мехоҳед ҳуҷҷатро нест кунед",
                    delete_doc_warning: "Ин амалро бекор карда намешавад.",
                    cancel: "Бекор кардан",
                    delete: "Нест кардан",
                    deleteSuccess: "Ҳуҷҷат бомуваффақият нест карда шуд",
                    deleteError: "Хато ҳангоми нест кардан",
                    toast_success: "Бомуваффақият",
                    toast_error: "Хато",
                    internal: "Дохилӣ",
                    document_type: "Ҳуҷҷат"
                },
                en: {
                    breadcrumb_home: "Home",
                    control_panel: "Control Panel",
                    welcomeBack: "Good morning",
                    dashSubtitle: "You have {pending} documents awaiting signature and {incoming} new incoming.",
                    new_users: "{count} new",
                    createDocument: "New Document",
                    totalDocs: "Total Documents",
                    pending: "Pending Signature",
                    signedMonth: "Signed This Month",
                    counterparties: "Team",
                    documentFlow: "Document Flow",
                    chartSubtitle: "Dynamics for the last 12 months",
                    week: "Week",
                    month: "Month",
                    year: "Year",
                    awaitingSignature: "Awaiting Signature",
                    needAttention: "Require your attention",
                    noPending: "No pending documents",
                    recentDocuments: "Recent Documents",
                    allOperations: "All operations in the electronic document management system",
                    all: "All",
                    incoming: "Incoming",
                    outgoing: "Outgoing",
                    drafts: "Drafts",
                    thDoc: "Document",
                    thCounterparty: "Counterparty",
                    thType: "Type",
                    thStatus: "Status",
                    thDate: "Date",
                    thActions: "Actions",
                    status_pending: "Pending",
                    status_signed: "Signed",
                    status_rejected: "Rejected",
                    status_draft: "Draft",
                    tooltip_view: "View",
                    tooltip_edit: "Edit",
                    tooltip_delete: "Delete",
                    no_documents: "No documents found",
                    create_first_doc: "Create the first document to get started",
                    no_data_chart: "No data to display",
                    create_docs_hint: "Create documents to see statistics",
                    docs_word: "documents",
                    delete_doc_title: "Delete document?",
                    delete_doc_text: "Are you sure you want to delete the document",
                    delete_doc_warning: "This action cannot be undone.",
                    cancel: "Cancel",
                    delete: "Delete",
                    deleteSuccess: "Document deleted successfully",
                    deleteError: "Error while deleting",
                    toast_success: "Success",
                    toast_error: "Error",
                    internal: "Internal",
                    document_type: "Document"
                }
            };

            // ===== Получение текущего языка =====
            function getCurrentLang() {
                return localStorage.getItem('docsign_lang')
                    || localStorage.getItem('app-lang')
                    || 'ru';
            }

            // ===== Применение переводов =====
            function applyTranslations() {
                const lang = getCurrentLang();
                const t = translations[lang] || translations['ru'];

                // Подзаголовок с переменными (одинарные скобки {pending} {incoming})
                const subtitleEl = document.querySelector('[data-i18n="dashSubtitle"]');
                if (subtitleEl && t.dashSubtitle) {
                    const pending = {{ $stats['pending'] ?? 0 }};
                    const incoming = {{ $stats['incoming'] ?? 0 }};
                    subtitleEl.textContent = t.dashSubtitle
                        .replace('{pending}', pending)
                        .replace('{incoming}', incoming);
                }

                // Новые пользователи (одинарные скобки {count})
                const newUsersEl = document.querySelector('[data-i18n="new_users"]');
                if (newUsersEl && t.new_users) {
                    const count = {{ $stats['new_users'] ?? 0 }};
                    newUsersEl.textContent = t.new_users.replace('{count}', count);
                }

                // Все элементы с data-i18n
                document.querySelectorAll('[data-i18n]').forEach(el => {
                    const key = el.getAttribute('data-i18n');
                    if (t[key] && key !== 'dashSubtitle' && key !== 'new_users') {
                        if (el.children.length > 0) {
                            el.childNodes.forEach(node => {
                                if (node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== "") {
                                    node.textContent = t[key];
                                }
                            });
                        } else {
                            el.textContent = t[key];
                        }
                    }
                });

                // Tooltip-ы
                document.querySelectorAll('[data-i18n-title]').forEach(el => {
                    const key = el.getAttribute('data-i18n-title');
                    if (t[key]) {
                        el.setAttribute('title', t[key]);
                        el.setAttribute('data-tooltip', t[key]);
                    }
                });

                // Placeholder-ы
                document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                    const key = el.getAttribute('data-i18n-placeholder');
                    if (t[key]) {
                        el.setAttribute('placeholder', t[key]);
                    }
                });
            }

            // Применяем сразу
            applyTranslations();

            // Слушаем событие смены языка из layout
            window.addEventListener('docsign:lang-changed', function(e) {
                if (e.detail && e.detail.lang) {
                    localStorage.setItem('docsign_lang', e.detail.lang);
                    localStorage.setItem('app-lang', e.detail.lang);
                }
                applyTranslations();
            });

            // Tab switching
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const parent = this.closest('.tabs');
                    parent.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // ===== TOAST FUNCTION =====
            window.showToast = function(type, title, message) {
                const container = document.getElementById('toastContainer');
                if (!container) return;

                const toast = document.createElement('div');
                toast.className = `toast ${type}`;

                const iconClass = type === 'success' ? 'bi-check-circle-fill' : 'bi-x-circle-fill';

                toast.innerHTML = `
                    <div class="toast-icon">
                        <i class="bi ${iconClass}"></i>
                    </div>
                    <div class="toast-content">
                        <div class="toast-title">${title}</div>
                        <div class="toast-message">${message}</div>
                    </div>
                `;

                container.appendChild(toast);

                setTimeout(() => {
                    toast.classList.add('toast-out');
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            };

            // ===== FLASH MESSAGES =====
            @if(session('success'))
                const _lang = getCurrentLang();
                const _t = translations[_lang] || translations['ru'];
                showToast('success', _t.toast_success, @json(session('success')));
            @endif

            @if(session('error'))
                const _lang2 = getCurrentLang();
                const _t2 = translations[_lang2] || translations['ru'];
                showToast('error', _t2.toast_error, @json(session('error')));
            @endif
        });

        // ===== DELETE MODAL LOGIC =====
        let currentDeleteId = null;

        function confirmDelete(id, name) {
            currentDeleteId = id;
            const modal = document.getElementById('deleteModal');
            const nameEl = document.getElementById('deleteDocName');
            const form = document.getElementById('deleteForm');

            nameEl.textContent = name;
            form.action = `/documents/${id}`;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            currentDeleteId = null;
        }

        function submitDelete() {
            const form = document.getElementById('deleteForm');
            if (currentDeleteId && form) {
                form.style.display = 'block';
                form.submit();
            }
        }

        // Close modal on overlay click
        document.addEventListener('click', function(e) {
            if (e.target.id === 'deleteModal') {
                closeDeleteModal();
            }
        });

        // Close modal on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });



    </script>
</div>
@endsection