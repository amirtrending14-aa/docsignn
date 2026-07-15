<form action="{{ route('workflow.update', $workflow->id) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- документ --}}
    <div class="mb-3">
        <label class="form-label">Документ</label>
        <input type="text" class="form-control"
               value="{{ $workflow->document->title }}" disabled>
    </div>

    {{-- пользователь --}}
    <div class="mb-3">
        <label class="form-label">Пользователь</label>
        <input type="text" class="form-control"
               value="{{ $workflow->user->name }}" disabled>
    </div>

    {{-- этап --}}
    <div class="mb-3">
        <label class="form-label">Этап</label>
        <input type="number"
               name="step_order"
               class="form-control"
               value="{{ old('step_order', $workflow->step_order) }}"
               min="1">
    </div>

    {{-- кнопки --}}
    <div class="d-flex justify-content-between">
        <a href="{{ route('workflow.index', $workflow->document_id) }}"
           class="btn btn-secondary">
            Назад
        </a>

        <button type="submit" class="btn btn-success">
            Сохранить
        </button>
    </div>
</form>
