{{--@extends('layouts.app')--}}

{{--@section('content')--}}
{{--    <div class="container">--}}

{{--        <h2 class="mb-3">--}}
{{--            Комментарии к документу: {{ $document->title }}--}}
{{--        </h2>--}}

{{--        --}}{{-- сообщения --}}
{{--        @if(session('success'))--}}
{{--            <div class="alert alert-success">--}}
{{--                {{ session('success') }}--}}
{{--            </div>--}}
{{--        @endif--}}

{{--        --}}{{-- ➕ форма добавления --}}
{{--        <div class="card mb-4">--}}
{{--            <div class="card-body">--}}

{{--                <form action="{{ route('comments.store') }}" method="POST">--}}
{{--                    @csrf--}}

{{--                    <input type="hidden" name="document_id" value="{{ $document->id }}">--}}

{{--                    --}}{{-- комментарий --}}
{{--                    <div class="mb-2">--}}
{{--                        <label>Комментарий</label>--}}
{{--                        <textarea name="comment" class="form-control" required></textarea>--}}
{{--                    </div>--}}

{{--                    <button class="btn btn-primary">--}}
{{--                        Отправить--}}
{{--                    </button>--}}

{{--                </form>--}}

{{--            </div>--}}
{{--        </div>--}}

{{--        --}}{{-- 💬 список комментариев --}}
{{--        @forelse($comments as $comment)--}}
{{--            <div class="card mb-2">--}}

{{--                <div class="card-body">--}}

{{--                    <div class="d-flex justify-content-between">--}}

{{--                        <strong>--}}
{{--                            👤 {{ $comment->user->name ?? 'User' }}--}}
{{--                        </strong>--}}

{{--                        <small class="text-muted">--}}
{{--                            {{ optional($comment->created_at)->format('Y-m-d H:i') }}--}}
{{--                        </small>--}}

{{--                    </div>--}}

{{--                    <p class="mb-2 mt-2">--}}
{{--                        {{ $comment->comment }}--}}
{{--                    </p>--}}

{{--                    --}}{{-- 🗑 удалить --}}
{{--                    <form action="{{ route('comments.destroy', $comment->id) }}"--}}
{{--                          method="POST"--}}
{{--                          onsubmit="return confirm('Удалить комментарий?')">--}}
{{--                        @csrf--}}
{{--                        @method('DELETE')--}}

{{--                        <button class="btn btn-sm btn-danger">--}}
{{--                            🗑 Удалить--}}
{{--                        </button>--}}
{{--                    </form>--}}

{{--                </div>--}}

{{--            </div>--}}
{{--        @empty--}}
{{--            <div class="alert alert-info">--}}
{{--                Нет комментариев--}}
{{--            </div>--}}
{{--        @endforelse--}}

{{--    </div>--}}
{{--@endsection--}}
