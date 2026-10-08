@php($editing = $document !== null)

<div class="card max-w-4xl">
    <form action="{{ $editing ? route('superadmin.documents.update', $document) : route('superadmin.documents.store') }}"
          method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="admin-label">Название документа <span class="text-red-600">*</span></label>
                <input id="title" name="title" type="text" required maxlength="255" class="admin-input"
                       value="{{ old('title', $document?->title) }}">
                @error('title')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="number" class="admin-label">Номер</label>
                <input id="number" name="number" type="text" maxlength="255" class="admin-input"
                       value="{{ old('number', $document?->number) }}">
                @error('number')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="type" class="admin-label">Тип документа <span class="text-red-600">*</span></label>
                <input id="type" name="type" type="text" required maxlength="100" class="admin-input"
                       value="{{ old('type', $document?->type) }}" placeholder="Например: договор">
                @error('type')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="status" class="admin-label">Статус <span class="text-red-600">*</span></label>
                <select id="status" name="status" required class="admin-input">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', $document?->status ?? 'draft') === $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
                @error('status')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="created_by" class="admin-label">Автор <span class="text-red-600">*</span></label>
                <select id="created_by" name="created_by" required class="admin-input">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('created_by', $document?->created_by ?? auth()->id()) === (string) $user->id)>
                            {{ $user->name }} · {{ $user->email }}
                        </option>
                    @endforeach
                </select>
                @error('created_by')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="receiver_id" class="admin-label">Получатель</label>
                <select id="receiver_id" name="receiver_id" class="admin-input">
                    <option value="">Не назначен</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('receiver_id', $document?->receiver_id) === (string) $user->id)>
                            {{ $user->name }} · {{ $user->email }}
                        </option>
                    @endforeach
                </select>
                @error('receiver_id')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="deadline" class="admin-label">Срок исполнения</label>
                <input id="deadline" name="deadline" type="datetime-local" class="admin-input"
                       value="{{ old('deadline', $document?->deadline?->format('Y-m-d\TH:i')) }}">
                @error('deadline')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="content" class="admin-label">Содержание</label>
                <textarea id="content" name="content" rows="8" class="admin-input">{{ old('content', $document?->content) }}</textarea>
                @error('content')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="file" class="admin-label">Файл (PDF, DOC, DOCX, XLS, XLSX, RTF; до 50 МБ)</label>
                @if($document?->file_path)
                    <div class="mb-3 flex flex-wrap items-center gap-3 text-sm">
                        <a class="text-blue-700 underline" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($document->file_path) }}" target="_blank" rel="noopener">
                            Открыть текущий файл
                        </a>
                        <label class="flex items-center gap-2 text-slate-600">
                            <input type="checkbox" name="remove_file" value="1" @checked(old('remove_file'))>
                            Удалить текущий файл
                        </label>
                    </div>
                @endif
                <input id="file" name="file" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.rtf" class="admin-input">
                @error('file')<p class="admin-error">{{ $message }}</p>@enderror
                @error('remove_file')<p class="admin-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5">
            <a href="{{ route('superadmin.documents.index') }}" class="btn-ghost">Отмена</a>
            <button type="submit" class="btn-primary">{{ $submitLabel }}</button>
        </div>
    </form>
</div>
