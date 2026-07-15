
    <div class="container">
        <h2 class="mb-3">Согласование документа</h2>

        {{-- кнопка добавить этап --}}
        <a href="{{ route('workflow.create', $documentId) }}" class="btn btn-primary mb-3">
            + Добавить этап
        </a>

        {{-- сообщения --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- таблица --}}
        <table class="table table-bordered" border="2">
            <thead>
            <tr>
                <th>#</th>
                <th>Пользователь</th>
                <th>Порядок</th>
                <th>Статус</th>
                <th width="250">Действия</th>
            </tr>
            </thead>
            <tbody>

            @forelse($workflows as $index =>$workflow)
                <tr>
                    <td>{{ $index +1 }}</td>

                    <td>
                        {{ $workflow->user->name ?? '—' }}
                    </td>

                    <td>{{ $workflow->step_order }}</td>

                    <td>
                        @if($workflow->status == 'pending')
                            <span class="badge bg-warning">Ожидает</span>
                        @elseif($workflow->status == 'approved')
                            <span class="badge bg-success">Одобрено</span>
                        @elseif($workflow->status == 'rejected')
                            <span class="badge bg-danger">Отклонено</span>
                        @elseif($workflow->status == 'waiting')
                            <span class="badge bg-secondary">Ожидание</span>
                        @endif
                    </td>

                    <td>
                        {{-- редактировать --}}
                        <a href="{{ route('workflow.edit', $workflow->id) }}"
                           class="btn btn-sm btn-warning">
                            ✏️
                        </a>

                        {{-- удалить --}}
                        <form action="{{ route('workflow.destroy', $workflow->id) }}"
                              method="POST"
                              style="display:inline;">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Удалить этап?')">
                                🗑
                            </button>
                        </form>

                        {{-- approve / reject --}}
                        @if($workflow->status == 'pending' && $workflow->user_id == auth()->id())

                            <form action="{{ route('workflow.approve', $workflow->id) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                <button class="btn btn-sm btn-success">
                                    ✔
                                </button>
                            </form>

                            <form action="{{ route('workflow.reject', $workflow->id) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                <button class="btn btn-sm btn-danger">
                                    ✖
                                </button>
                            </form>

                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        Нет этапов
                    </td>
                </tr>
            @endforelse

            </tbody>
        </table>

        {{-- пагинация (если используешь paginate) --}}
        @if(method_exists($workflows, 'links'))
            <div class="mt-3">
                {{ $workflows->links() }}
            </div>
        @endif

    </div>

